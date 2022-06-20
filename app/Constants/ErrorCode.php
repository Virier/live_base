<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("未知错误，请重试")
     */
    public const ERROR = 500;
}
