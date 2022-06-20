<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
#[Constants]
class SuccessCode extends AbstractConstants
{
    /**
     * @Message("返回成功")
     */
    public const SUCCESS = 200;
}
