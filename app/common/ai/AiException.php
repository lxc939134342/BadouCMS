<?php

namespace app\common\ai;

class AiException extends \RuntimeException
{
    public const NOT_LOGIN = 'not_login';
    public const NO_CREDIT = 'no_credit';
    public const UPSTREAM = 'upstream';

    public function __construct(
        string $message,
        public readonly string $reason = self::UPSTREAM,
        public readonly string $rechargeUrl = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
