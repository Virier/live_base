<?php


namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class FqException extends ServerException
{
    public function __construct(string $message = null, int $code = 500, Throwable $previous = null) {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
        }
        parent::__construct($message, $code, $previous);
    }
}