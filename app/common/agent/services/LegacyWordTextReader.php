<?php

namespace app\common\agent\services;

use DomainException;

/** 按 MS-CFB/MS-DOC 读取 Word 97–2003 正文，不重建版式或执行文档对象。 */
class LegacyWordTextReader
{
    private const END = 0xfffffffe;
    private const FREE = 0xffffffff;
    private const MAX_BYTES = 20 * 1024 * 1024;
    private const MAX_TEXT_BYTES = 8 * 1024 * 1024;
    private string $data = '';
    private int $sectorSize = 512;
    private array $fat = [];
    private array $miniFat = [];
    private string $miniData = '';

    public function read(string $file): string
    {
        $data = file_get_contents($file, false, null, 0, self::MAX_BYTES + 1);
        if ($data === false || strlen($data) > self::MAX_BYTES) {
            throw new DomainException('旧版 Word 文件无法读取或过大，请拆分后上传');
        }
        $this->data = $data;
        $streams = $this->readCompoundFile();
        $word = $streams['worddocument'] ?? '';
        if ($this->u16($word, 0) !== 0xa5ec) $this->invalid();
        if ($this->u16($word, 2) < 0x00c1) {
            throw new DomainException('该 .doc 早于 Word 97，请另存为 Word 97–2003 或 .docx 格式后上传');
        }
        $flags = $this->u16($word, 10);
        if (($flags & 0x8100) !== 0) {
            throw new DomainException('该 Word 文档已加密，请解除文档密码后重新上传');
        }
        $table = $streams[($flags & 0x0200) ? '1table' : '0table'] ?? '';
        // FIB 的变长区域不能按固定位置跳读；ccpText 限定正文，避免混入页眉和批注。
        $position = 34 + $this->u16($word, 32) * 2;
        $longCount = $this->u16($word, $position);
        if ($longCount < 4) $this->invalid();
        $bodyChars = $this->u32($word, $position + 14);
        if ($bodyChars > self::MAX_TEXT_BYTES) {
            throw new DomainException('旧版 Word 正文过大，请拆分文档后读取');
        }
        $position += 2 + $longCount * 4;
        if ($this->u16($word, $position) < 34) $this->invalid();
        $clxOffset = $this->u32($word, $position + 2 + 33 * 8);
        $clxSize = $this->u32($word, $position + 6 + 33 * 8);
        $clx = $this->bytes($table, $clxOffset, $clxSize);
        $position = 0;
        while ($this->bytes($clx, $position, 1) === "\x01") {
            $position += 3 + $this->u16($clx, $position + 1);
        }
        if ($this->bytes($clx, $position, 1) !== "\x02") $this->invalid();
        $pieces = $this->bytes($clx, $position + 5, $this->u32($clx, $position + 1));
        $size = strlen($pieces);
        if ($size < 4 || ($size - 4) % 12 !== 0) $this->invalid();
        $count = intdiv($size - 4, 12);
        if ($count > 100000 || $this->u32($pieces, 0) !== 0 || $this->u32($pieces, $count * 4) < $bodyChars) {
            $this->invalid();
        }
        // MS-DOC 的压缩文本是单字节 Unicode，只有规范列出的字符采用特殊映射。
        $special = [0x82 => 0x201a, 0x83 => 0x0192, 0x84 => 0x201e, 0x85 => 0x2026,
            0x86 => 0x2020, 0x87 => 0x2021, 0x88 => 0x02c6, 0x89 => 0x2030,
            0x8a => 0x0160, 0x8b => 0x2039, 0x8c => 0x0152, 0x91 => 0x2018,
            0x92 => 0x2019, 0x93 => 0x201c, 0x94 => 0x201d, 0x95 => 0x2022,
            0x96 => 0x2013, 0x97 => 0x2014, 0x98 => 0x02dc, 0x99 => 0x2122,
            0x9a => 0x0161, 0x9b => 0x203a, 0x9c => 0x0153, 0x9f => 0x0178];
        $map = [];
        for ($byte = 0; $byte < 256; $byte++) $map[chr($byte)] = pack('v', $special[$byte] ?? $byte);
        $unicode = '';
        for ($index = 0; $index < $count; $index++) {
            $start = $this->u32($pieces, $index * 4);
            $end = $this->u32($pieces, ($index + 1) * 4);
            if ($end <= $start) $this->invalid();
            if ($start >= $bodyChars) break;
            $characters = min($end, $bodyChars) - $start;
            $fc = $this->u32($pieces, ($count + 1) * 4 + $index * 8 + 2);
            $compressed = ($fc & 0x40000000) !== 0;
            $offset = $fc & 0x3fffffff;
            if ($compressed && $offset % 2 !== 0) $this->invalid();
            $chunk = $this->bytes($word, $compressed ? intdiv($offset, 2) : $offset, $characters * ($compressed ? 1 : 2));
            $unicode .= $compressed ? strtr($chunk, $map) : $chunk;
        }
        // 先拼接 UTF-16，再转换，避免文本块边界切开 emoji 等代理对。
        if (strlen($unicode) !== $bodyChars * 2 || !mb_check_encoding($unicode, 'UTF-16LE')) $this->invalid();
        $text = mb_convert_encoding($unicode, 'UTF-8', 'UTF-16LE');
        if (strlen($text) > self::MAX_TEXT_BYTES) {
            throw new DomainException('旧版 Word 正文过大，请拆分文档后读取');
        }
        return $this->cleanText($text);
    }

    private function readCompoundFile(): array
    {
        if ($this->bytes($this->data, 0, 8) !== hex2bin('d0cf11e0a1b11ae1')
            || $this->u16($this->data, 28) !== 0xfffe || $this->u16($this->data, 32) !== 6
            || $this->u32($this->data, 56) !== 4096) $this->invalid();
        $version = $this->u16($this->data, 26);
        $shift = $this->u16($this->data, 30);
        if (!(($version === 3 && $shift === 9) || ($version === 4 && $shift === 12))) $this->invalid();
        $this->sectorSize = 1 << $shift;
        if (strlen($this->data) % $this->sectorSize !== 0) $this->invalid();
        $sectors = intdiv(strlen($this->data), $this->sectorSize) - 1;
        $fatCount = $this->u32($this->data, 44);
        $difatCount = $this->u32($this->data, 72);
        $entriesPerSector = intdiv($this->sectorSize, 4);
        // FAT 只需覆盖实际扇区；拒绝虚增数量，避免构造附件放大数组和遍历开销。
        $maxFatCount = (int)ceil($sectors / $entriesPerSector);
        $maxDifatCount = (int)ceil(max(0, $fatCount - 109) / ($entriesPerSector - 1));
        if ($fatCount < 1 || $fatCount > $maxFatCount || $difatCount > $maxDifatCount) $this->invalid();
        $fatIds = array_values(unpack('V*', $this->bytes($this->data, 76, 436)));
        $next = $this->u32($this->data, 68);
        $seen = [];
        for ($index = 0; $index < $difatCount; $index++) {
            if ($next >= $sectors || isset($seen[$next])) $this->invalid();
            $seen[$next] = true;
            $ids = array_values(unpack('V*', $this->bytes($this->data, ($next + 1) * $this->sectorSize, $this->sectorSize)));
            $next = array_pop($ids);
            $fatIds = array_merge($fatIds, $ids);
        }
        if ($difatCount > 0 && $next !== self::END) $this->invalid();
        $fatIds = array_slice(array_values(array_filter($fatIds, fn ($id) => $id !== self::FREE)), 0, $fatCount);
        if (count($fatIds) !== $fatCount || count(array_unique($fatIds)) !== $fatCount) $this->invalid();
        $this->fat = [];
        foreach ($fatIds as $id) {
            if ($id >= $sectors) $this->invalid();
            $this->fat = array_merge($this->fat, array_values(unpack('V*', $this->bytes($this->data,
                ($id + 1) * $this->sectorSize, $this->sectorSize))));
        }
        $directory = $this->chain($this->data, $this->sectorSize, $this->sectorSize, $this->fat,
            $this->u32($this->data, 48), 1024 * 1024);
        if (strlen($directory) < 128 || $this->bytes($directory, 66, 1) !== "\x05") $this->invalid();
        $streams = [];
        $seen = [];
        $pending = [$this->u32($directory, 76)];
        // 只遍历根目录的兄弟树，不进入嵌入对象，防止读取到另一份文档的同名流。
        while ($pending !== []) {
            $id = array_pop($pending);
            if ($id === self::FREE) continue;
            if ($id === 0 || isset($seen[$id])) $this->invalid();
            $seen[$id] = true;
            $entry = $this->bytes($directory, $id * 128, 128);
            $pending[] = $this->u32($entry, 68);
            $pending[] = $this->u32($entry, 72);
            if ($this->bytes($entry, 66, 1) !== "\x02") continue;
            $length = $this->u16($entry, 64);
            if ($length < 2 || $length > 64 || $length % 2 !== 0) $this->invalid();
            $name = strtolower(mb_convert_encoding($this->bytes($entry, 0, $length - 2), 'UTF-8', 'UTF-16LE'));
            if (!in_array($name, ['worddocument', '0table', '1table'], true)) continue;
            if (isset($streams[$name])) $this->invalid();
            $streams[$name] = $entry;
        }
        $needsMini = false;
        $streamBytes = 0;
        foreach ($streams as $entry) {
            $size = $this->streamSize($entry);
            $streamBytes += $size;
            if ($streamBytes > self::MAX_BYTES) $this->invalid();
            if ($size > 0 && $size < 4096) $needsMini = true;
        }
        $this->miniFat = [];
        $this->miniData = '';
        if ($needsMini) {
            $miniCount = $this->u32($this->data, 64);
            if ($miniCount < 1 || $miniCount > $sectors) $this->invalid();
            $miniFat = $this->chain($this->data, $this->sectorSize, $this->sectorSize, $this->fat,
                $this->u32($this->data, 60), $miniCount * $this->sectorSize);
            $this->miniFat = array_values(unpack('V*', $miniFat));
            $root = $this->bytes($directory, 0, 128);
            $this->miniData = $this->readStream($root, false);
        }
        foreach ($streams as &$entry) $entry = $this->readStream($entry, true);
        unset($entry);
        return $streams;
    }

    private function streamSize(string $entry): int
    {
        $size = $this->u32($entry, 120);
        if ($this->u32($entry, 124) !== 0 || $size > self::MAX_BYTES) $this->invalid();
        return $size;
    }

    private function readStream(string $entry, bool $allowMini): string
    {
        $size = $this->streamSize($entry);
        if ($size === 0) return '';
        $mini = $allowMini && $size < 4096;
        $unit = $mini ? 64 : $this->sectorSize;
        $data = $this->chain($mini ? $this->miniData : $this->data, $mini ? 0 : $this->sectorSize,
            $unit, $mini ? $this->miniFat : $this->fat, $this->u32($entry, 116), (int)ceil($size / $unit) * $unit);
        return $this->bytes($data, 0, $size);
    }

    private function chain(string $data, int $base, int $unit, array $table, int $start, int $maxBytes): string
    {
        $text = '';
        $seen = [];
        while ($start !== self::END) {
            // 环形或越界扇区链必须立即拒绝，不能让损坏附件耗尽 FPM 内存。
            if (!isset($table[$start]) || isset($seen[$start]) || strlen($text) + $unit > $maxBytes) $this->invalid();
            $seen[$start] = true;
            $text .= $this->bytes($data, $base + $start * $unit, $unit);
            $start = $table[$start];
        }
        return $text;
    }

    private function cleanText(string $text): string
    {
        $output = '';
        $fields = [];
        $position = 0;
        $count = 0;
        // 域的指令部分不是正文，只保留已有显示结果，不计算域或访问超链接。
        while (preg_match('/[\x13\x14\x15]/', $text, $match, PREG_OFFSET_CAPTURE, $position)) {
            if (++$count > 100000) $this->invalid();
            $offset = $match[0][1];
            if (!in_array(false, $fields, true)) $output .= substr($text, $position, $offset - $position);
            $code = $match[0][0];
            if ($code === "\x13") {
                if (count($fields) >= 64) $this->invalid();
                $fields[] = false;
            } elseif ($fields === []) {
                $this->invalid();
            } elseif ($code === "\x14") {
                $fields[count($fields) - 1] = true;
            } else {
                array_pop($fields);
            }
            $position = $offset + 1;
        }
        if ($fields !== []) $this->invalid();
        $output .= substr($text, $position);
        $output = str_replace(["\r\x07", "\x07", "\r", "\x0b", "\x0c"], ["\t", "\t", "\n", "\n", "\n"], $output);
        $output = preg_replace('/[\x00-\x06\x08\x0e-\x1f]/', '', $output);
        return trim(preg_replace('/\n{3,}/', "\n\n", $output));
    }

    private function bytes(string $data, int $offset, int $size): string
    {
        if ($offset < 0 || $size < 0 || $offset > strlen($data) || $size > strlen($data) - $offset) $this->invalid();
        return substr($data, $offset, $size);
    }

    private function u16(string $data, int $offset): int { return unpack('v', $this->bytes($data, $offset, 2))[1]; }
    private function u32(string $data, int $offset): int { return unpack('V', $this->bytes($data, $offset, 4))[1]; }

    private function invalid(): never
    {
        throw new DomainException('无法解析旧版 Word，文件可能损坏或并非 Word 97–2003 .doc 格式，请重新保存后上传');
    }
}
