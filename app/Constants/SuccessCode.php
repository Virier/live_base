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
     * @Message("θΏεζε")
     */
    public const SUCCESS = 200;
}
