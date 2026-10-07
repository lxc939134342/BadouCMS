<?php

namespace app\common\agent\services;

use app\common\agent\AgentRunControl;
use app\common\agent\AgentStoppedException;

use app\admin\model\AgentPatch;
use app\common\agent\AgentContext;
use app\common\agent\PatchReviewResult;
use app\common\ai\AiException;
use app\common\ai\AiGateway;
use NeuronAI\Agent\Agent;
use NeuronAI\Chat\Messages\UserMessage;

class AgentPatchService
{
    private const DEFAULT_ALLOWED_EXT = ['php', 'html', 'htm', 'js', 'css', 'json', 'ini', 'sql', 'md'];
    private const DEFAULT_DENY_PREFIX = ['vendor/', 'config/', 'runtime/', 'node_modules/', 'public/uploads/', 'public/dist/', 'public/assets/', '.git/'];
    private const DEFAULT_DENY_FILES = ['.env', 'composer.json', 'composer.lock', 'package-lock.json', 'pnpm-lock.yaml'];
    private const SCAN_ROOTS = ['app', 'modules', 'extend', 'config', 'route', 'template', 'public', 'data', 'docs'];

    public function listForChat(int $chatId, int $tenantId, int $adminId): array
    {
        return AgentPatch::listForChat($chatId, $tenantId, $adminId);
    }

    /** 删除某个会话的全部代码改动记录（含备份文件）。 */
    public function deleteForChat(int $chatId, int $tenantId, int $adminId): int
    {
        $ids = AgentPatch::where('chat_id', $chatId)
            ->where('tenant_id', $tenantId)
            ->where('admin_id', $adminId)
            ->column('id');
        if ($ids === []) {
            return 0;
        }
        $count = AgentPatch::where('id', 'in', $ids)->delete();
        foreach ($ids as $id) {
            @unlink(root_path() . 'runtime/agent_patch_backup/' . $id . '.bak');
            @unlink(root_path() . 'runtime/agent_patch_backup/' . $id . '.txt');
        }
        return $count;
    }

    public function readFile(string $file, int $offset = 1, int $limit = 400): array
    {
        [$full, $relative] = $this->resolvePath($file);
        $lines = @file($full, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new \DomainException('读取文件失败');
        }
        $total = count($lines);
        $offset = max(1, $offset);
        $limit = max(1, min(800, $limit));
        $numbered = [];
        $bytes = 0;
        foreach (array_slice($lines, $offset - 1, $limit) as $index => $line) {
            $line = mb_substr($line, 0, 1000);
            $bytes += strlen($line) + 1;
            if ($bytes > 30000) {
                $numbered[] = '...（内容过长已截断，请用 offset/limit 分段读取）';
                break;
            }
            $numbered[] = ($offset + $index) . ': ' . $line;
        }
        return [
            'file' => $relative,
            'total_lines' => $total,
            'offset' => $offset,
            'content' => implode("\n", $numbered),
        ];
    }

    /** 在项目源码目录内按路径关键词搜索文件，帮助定位真实路径。 */
    public function findFiles(string $keyword, int $limit = 50): array
    {
        $keyword = trim(str_replace('\\', '/', $keyword));
        if ($keyword === '') {
            throw new \DomainException('请提供文件名或路径关键词');
        }
        $limit = max(1, min(100, $limit));
        $root = rtrim(str_replace('\\', '/', root_path()), '/');
        $needle = mb_strtolower($keyword);
        $matches = [];
        foreach (self::SCAN_ROOTS as $base) {
            $dir = $root . '/' . $base;
            if (!is_dir($dir)) {
                continue;
            }
            $directory = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
            $filter = new \RecursiveCallbackFilterIterator($directory, function (\SplFileInfo $current) use ($root): bool {
                $relative = ltrim(str_replace($root, '', str_replace('\\', '/', $current->getPathname())), '/');
                return !$current->isDir() || !$this->isDenied($relative . '/');
            });
            $iterator = new \RecursiveIteratorIterator($filter);
            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $relative = ltrim(str_replace($root, '', str_replace('\\', '/', $file->getPathname())), '/');
                if (!in_array(strtolower((string)pathinfo($relative, PATHINFO_EXTENSION)), $this->allowedExt(), true)) {
                    continue;
                }
                if ($this->isDenied($relative) || !str_contains(mb_strtolower($relative), $needle)) {
                    continue;
                }
                $matches[] = $relative;
                if (count($matches) >= $limit) {
                    break 2;
                }
            }
        }
        sort($matches);
        return ['keyword' => $keyword, 'total' => count($matches), 'files' => $matches];
    }

    public function logTail(string $app = 'admin', int $lines = 200): array
    {
        $app = preg_replace('/[^a-z0-9_]/i', '', $app) ?: 'admin';
        $lines = max(1, min(500, $lines));
        $dir = root_path() . 'runtime/' . $app . '/log';
        if (!is_dir($dir)) {
            $dir = root_path() . 'runtime/log';
        }
        $latest = $this->latestLogFile($dir);
        if ($latest === null) {
            throw new \DomainException('没有找到日志文件');
        }
        $root = str_replace('\\', '/', root_path());
        return [
            'app' => $app,
            'file' => ltrim(str_replace($root, '', str_replace('\\', '/', $latest)), '/'),
            'lines' => $this->tailLines($latest, $lines, 524288),
        ];
    }

    public function propose(AgentContext $context, string $file, string $oldString, string $newString): array
    {
        if ($context->chatId <= 0) {
            throw new \DomainException('缺少会话信息，无法记录代码改动');
        }
        if ($oldString === '') {
            throw new \DomainException('old_string 不能为空');
        }
        [$full, $relative] = $this->resolvePath($file);
        $content = @file_get_contents($full);
        if ($content === false) {
            throw new \DomainException('读取文件失败');
        }
        // 兼容模型直接粘贴 source_read 的行号前缀，以及文件是 CRLF 换行的情况。
        $oldString = $this->normalizeSnippet($oldString);
        $located = $this->locate($content, $oldString);
        if ($located === null) {
            $head = implode("\n", array_slice(preg_split('/\r\n|\r|\n/', $content) ?: [], 0, 15));
            throw new \DomainException("文件中找不到要替换的原文。请确保 old_string 与文件逐字一致（不要带 source_read 的行号前缀，注意空格和换行）。文件开头实际内容：\n" . $head);
        }
        [$oldString, $count] = $located;
        if ($count > 1) {
            throw new \DomainException('要替换的内容在文件中出现 ' . $count . ' 次，请提供更长且唯一的片段');
        }

        // 完全相同的待批准改动直接复用，避免重复确认框。
        $existing = AgentPatch::where('chat_id', $context->chatId)
            ->where('file', $relative)
            ->where('status', 'pending')
            ->where('old_string', $oldString)
            ->where('new_string', $newString)
            ->find();
        if ($existing) {
            return $this->preview($existing) + ['duplicate' => true, 'message' => '已存在相同的待批准改动，未重复创建。'];
        }
        // 同一文件只保留最新一条待批准改动：旧的反正是基于旧文件内容，应用会互相冲突。
        AgentPatch::where('chat_id', $context->chatId)
            ->where('file', $relative)
            ->where('status', 'pending')
            ->delete();

        $patch = new AgentPatch();
        try {
            $patch->save([
                'chat_id' => $context->chatId,
                'admin_id' => $context->adminId,
                'tenant_id' => $context->tenantId,
                'file' => $relative,
                'old_string' => $oldString,
                'new_string' => $newString,
                // 只对比被替换的片段，避免整文件 diff；再限制长度防止回传给模型时过大。
                'diff' => $this->capText($this->buildDiff($oldString, $newString), 8000),
                'backup' => $content,
                'status' => 'pending',
                'mode' => 'manual',
                'review_reason' => '',
            ]);
        } catch (\Throwable $e) {
            throw new \DomainException('保存代码改动失败：' . $e->getMessage());
        }
        return $this->preview($patch);
    }

    public function apply(int $id, int $tenantId, int $adminId, string $mode = 'manual', string $reason = ''): array
    {
        $patch = AgentPatch::findForUser($id, $tenantId, $adminId);
        if (!$patch) {
            throw new \DomainException('改动不存在或无权访问');
        }
        if ($patch->status === 'applied') {
            return ['id' => $id, 'file' => (string)$patch->file, 'status' => 'applied'];
        }
        if ($patch->status !== 'pending') {
            throw new \DomainException('该改动当前状态无法批准');
        }
        [$full] = $this->resolvePath((string)$patch->file);
        $content = @file_get_contents($full);
        if ($content === false) {
            throw new \DomainException('读取文件失败');
        }
        if (substr_count($content, (string)$patch->old_string) !== 1) {
            throw new \DomainException('文件已被修改，原内容不再唯一匹配，请重新生成改动');
        }
        $newContent = str_replace((string)$patch->old_string, (string)$patch->new_string, $content);
        $this->writeBackup($id, (string)$patch->file, $content);
        if (@file_put_contents($full, $newContent) === false) {
            throw new \DomainException('写入文件失败');
        }
        $patch->save([
            'status' => 'applied',
            'mode' => $mode,
            'review_reason' => mb_substr($reason, 0, 500),
            'apply_time' => time(),
        ]);
        return ['id' => $id, 'file' => (string)$patch->file, 'status' => 'applied', 'mode' => $mode];
    }

    public function reject(int $id, int $tenantId, int $adminId): array
    {
        $patch = AgentPatch::findForUser($id, $tenantId, $adminId);
        if (!$patch) {
            throw new \DomainException('改动不存在或无权访问');
        }
        if ($patch->status !== 'pending') {
            throw new \DomainException('该改动当前状态无法拒绝');
        }
        $patch->save(['status' => 'rejected']);
        return ['id' => $id, 'status' => 'rejected'];
    }

    /** 用备份内容把文件还原到应用前的状态。 */
    public function rollback(int $id, int $tenantId, int $adminId): array
    {
        $patch = AgentPatch::findForUser($id, $tenantId, $adminId);
        if (!$patch) {
            throw new \DomainException('改动不存在或无权访问');
        }
        if ($patch->status !== 'applied') {
            throw new \DomainException('只有已应用的改动才能回退');
        }
        [$full] = $this->resolvePath((string)$patch->file);
        if (@file_put_contents($full, (string)$patch->backup) === false) {
            throw new \DomainException('回退写入失败');
        }
        $patch->save(['status' => 'reverted']);
        return ['id' => $id, 'file' => (string)$patch->file, 'status' => 'reverted'];
    }

    /** AI 自审：用一个独立的模型调用判断 diff 是否可自动批准。 */
    public function reviewById(int $id, int $tenantId, int $adminId, ?AgentRunControl $control = null): array
    {
        $patch = AgentPatch::findForUser($id, $tenantId, $adminId);
        if (!$patch) {
            throw new \DomainException('改动不存在或无权访问');
        }
        try {
            $agent = Agent::make();
            $agent->setWorkflowId('agent-patch-review:' . $id . ':' . bin2hex(random_bytes(16)));
            $agent->setAiProvider(AiGateway::provider('agent_review', 60, checkRunning: $control !== null ? fn () => $control->check() : null));
            $agent->setInstructions('你是代码变更审核员。只根据给定 diff 判断改动是否安全、局部、可回退，且没有删除关键逻辑或引入明显错误。');
            // 用 Neuron 自带的结构化输出，省去手写 JSON 解析。
            $result = $agent->structured(
                new UserMessage("文件：{$patch->file}\n\ndiff：\n{$patch->diff}"),
                PatchReviewResult::class
            );
            if (!$result instanceof PatchReviewResult) {
                return ['approve' => false, 'reason' => '自审未返回结构化结果'];
            }
            return ['approve' => (bool)$result->approve, 'reason' => (string)$result->reason];
        } catch (AgentStoppedException $e) {
            throw $e;
        } catch (AiException $e) {
            return ['approve' => false, 'reason' => $e->getMessage()];
        } catch (\Throwable $e) {
            $explained = AiGateway::explain($e);
            return ['approve' => false, 'reason' => '自审失败：' . $explained->getMessage()];
        }
    }

    private function preview(AgentPatch $patch): array
    {
        return [
            'id' => (int)$patch->id,
            'file' => (string)$patch->file,
            'status' => (string)$patch->status,
            'diff' => (string)$patch->diff,
            'requires_approval' => $patch->status === 'pending',
        ];
    }

    private function resolvePath(string $file): array
    {
        $file = str_replace('\\', '/', trim($file));
        if ($file === '' || str_contains($file, "\0")) {
            throw new \DomainException('文件路径无效');
        }
        $root = rtrim(str_replace('\\', '/', root_path()), '/');
        // 兼容用户直接粘贴的绝对路径。
        if (str_starts_with($file, $root . '/')) {
            $file = substr($file, strlen($root) + 1);
        } else {
            $file = ltrim($file, '/');
        }
        if ($file === '' || str_contains($file, '..')) {
            throw new \DomainException('文件路径无效');
        }
        $real = realpath($root . '/' . $file);
        if ($real === false || !is_file($real)) {
            throw new \DomainException('文件不存在：' . $file . '（路径相对项目根目录；可用 source_find 按文件名搜索）');
        }
        $real = str_replace('\\', '/', $real);
        if (!str_starts_with($real, $root . '/')) {
            throw new \DomainException('只能访问项目目录内的文件');
        }
        $relative = substr($real, strlen($root) + 1);
        if ($this->isDenied($relative)) {
            throw new \DomainException('该文件禁止 Agent 访问：' . $relative);
        }
        if (!in_array(strtolower((string)pathinfo($real, PATHINFO_EXTENSION)), $this->allowedExt(), true)) {
            throw new \DomainException('不支持的文件类型');
        }
        return [$real, $relative];
    }

    /** 去掉模型可能带上的 source_read 行号前缀（仅当每行都带时才处理）。 */
    private function normalizeSnippet(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        if (!is_array($lines) || $lines === []) {
            return $text;
        }
        foreach ($lines as $line) {
            if (!preg_match('/^\d+:\s?/', $line)) {
                return $text;
            }
        }
        return implode("\n", array_map(static fn ($line) => preg_replace('/^\d+:\s?/', '', $line), $lines));
    }

    /** 在文件内容里定位片段，必要时按文件换行风格再试一次。返回 [匹配片段, 出现次数] 或 null。 */
    private function locate(string $content, string $oldString): ?array
    {
        $candidates = [$oldString];
        if (str_contains($content, "\r\n") && !str_contains($oldString, "\r\n")) {
            $candidates[] = str_replace("\n", "\r\n", $oldString);
        }
        foreach ($candidates as $candidate) {
            $count = substr_count($content, $candidate);
            if ($count > 0) {
                return [$candidate, $count];
            }
        }
        return null;
    }

    private function isDenied(string $relative): bool
    {
        $lower = strtolower($relative);
        foreach ($this->denyPrefix() as $prefix) {
            if (str_starts_with($lower, $prefix)) {
                return true;
            }
        }
        $base = basename($lower);
        if (str_starts_with($base, '.env')) {
            return true;
        }
        foreach ($this->denyFiles() as $deny) {
            if ($base === $deny || $lower === $deny) {
                return true;
            }
        }
        return false;
    }

    /** 当前生效的可修改文件规则。 */
    public function policy(): array
    {
        return [
            'allowed_ext' => $this->allowedExt(),
            'deny_prefix' => array_map(static fn ($prefix) => rtrim($prefix, '/'), $this->denyPrefix()),
            'deny_files' => $this->denyFiles(),
            'scan_roots' => self::SCAN_ROOTS,
        ];
    }

    /** 检查某个文件是否允许 Agent 修改。 */
    public function checkFile(string $file): array
    {
        $root = rtrim(str_replace('\\', '/', root_path()), '/');
        $file = str_replace('\\', '/', trim($file));
        if (str_starts_with($file, $root . '/')) {
            $file = substr($file, strlen($root) + 1);
        } else {
            $file = ltrim($file, '/');
        }
        if ($file === '' || str_contains($file, '..')) {
            return ['file' => $file, 'editable' => false, 'exists' => false, 'reason' => '路径无效'];
        }
        $exists = is_file($root . '/' . $file);
        if ($this->isDenied($file)) {
            return ['file' => $file, 'editable' => false, 'exists' => $exists, 'reason' => '命中禁止规则（目录或文件被禁止）'];
        }
        $ext = strtolower((string)pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExt(), true)) {
            return ['file' => $file, 'editable' => false, 'exists' => $exists, 'reason' => '文件类型不在允许列表：' . ($ext ?: '无后缀')];
        }
        return ['file' => $file, 'editable' => true, 'exists' => $exists, 'reason' => $exists ? '允许修改' : '允许修改，但文件当前不存在'];
    }

    private function allowedExt(): array
    {
        $value = config('agent.patch.allowed_ext');
        return is_array($value) && $value !== [] ? array_map('strtolower', $value) : self::DEFAULT_ALLOWED_EXT;
    }

    private function denyPrefix(): array
    {
        $value = config('agent.patch.deny_prefix');
        if (!is_array($value) || $value === []) {
            return self::DEFAULT_DENY_PREFIX;
        }
        return array_map(static fn ($prefix) => strtolower(rtrim((string)$prefix, '/')) . '/', $value);
    }

    private function denyFiles(): array
    {
        $value = config('agent.patch.deny_files');
        return is_array($value) && $value !== [] ? array_map(static fn ($name) => strtolower((string)$name), $value) : self::DEFAULT_DENY_FILES;
    }

    private function writeBackup(int $id, string $file, string $content): void
    {
        $dir = root_path() . 'runtime/agent_patch_backup';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($dir . '/' . $id . '.bak', $content);
        @file_put_contents($dir . '/' . $id . '.txt', $file);
    }

    private function latestLogFile(string $dir): ?string
    {
        if (!is_dir($dir)) {
            return null;
        }
        $latest = null;
        $latestTime = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'log') {
                continue;
            }
            if ($file->getMTime() > $latestTime) {
                $latestTime = $file->getMTime();
                $latest = $file->getPathname();
            }
        }
        return $latest;
    }

    private function tailLines(string $path, int $lines, int $maxBytes): string
    {
        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return '';
        }
        fseek($handle, 0, SEEK_END);
        $size = ftell($handle);
        $buffer = '';
        $chunk = 4096;
        $pos = $size;
        while ($pos > 0 && substr_count($buffer, "\n") <= $lines && strlen($buffer) < $maxBytes) {
            $read = min($chunk, $pos);
            $pos -= $read;
            fseek($handle, $pos);
            $buffer = (string)fread($handle, $read) . $buffer;
        }
        fclose($handle);
        $all = explode("\n", $buffer);
        $tail = array_slice($all, -$lines);
        // 控制返回体积：日志行常常很长（含 SQL/HTML），不截断会把模型请求撑爆。
        $out = [];
        $bytes = 0;
        for ($i = count($tail) - 1; $i >= 0; $i--) {
            $line = mb_substr($tail[$i], 0, 300);
            $bytes += strlen($line) + 1;
            if ($bytes > 10000) {
                array_unshift($out, '...（更早日志已省略）');
                break;
            }
            array_unshift($out, $line);
        }
        return implode("\n", $out);
    }

    private function buildDiff(string $old, string $new): string
    {
        $oldLines = preg_split('/\r\n|\r|\n/', $old) ?: [];
        $newLines = preg_split('/\r\n|\r|\n/', $new) ?: [];
        $n = count($oldLines);
        $m = count($newLines);
        if ($n * $m > 2000000) {
            return implode("\n", array_merge(
                array_map(static fn ($line) => '- ' . $line, $oldLines),
                array_map(static fn ($line) => '+ ' . $line, $newLines)
            ));
        }
        $dp = [];
        for ($i = 0; $i <= $n; $i++) {
            $dp[$i] = array_fill(0, $m + 1, 0);
        }
        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = $m - 1; $j >= 0; $j--) {
                $dp[$i][$j] = $oldLines[$i] === $newLines[$j]
                    ? $dp[$i + 1][$j + 1] + 1
                    : max($dp[$i + 1][$j], $dp[$i][$j + 1]);
            }
        }
        $out = [];
        $i = 0;
        $j = 0;
        while ($i < $n && $j < $m) {
            if ($oldLines[$i] === $newLines[$j]) {
                $out[] = '  ' . $oldLines[$i];
                $i++;
                $j++;
            } elseif ($dp[$i + 1][$j] >= $dp[$i][$j + 1]) {
                $out[] = '- ' . $oldLines[$i];
                $i++;
            } else {
                $out[] = '+ ' . $newLines[$j];
                $j++;
            }
        }
        while ($i < $n) {
            $out[] = '- ' . $oldLines[$i++];
        }
        while ($j < $m) {
            $out[] = '+ ' . $newLines[$j++];
        }
        return implode("\n", $out);
    }

    private function capText(string $text, int $maxBytes): string
    {
        if (strlen($text) <= $maxBytes) {
            return $text;
        }
        return mb_substr($text, 0, $maxBytes) . "\n...（内容过长已截断）";
    }

}
