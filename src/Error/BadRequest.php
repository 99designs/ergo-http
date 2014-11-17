<?php

namespace Ergo\Http\Error;

use Ergo\Http\Error;

class BadRequest extends Error
{
    const STATUS_CODE = 400;

    public function __construct($string)
    {
        parent::__construct($string, self::STATUS_CODE);
    }
}
