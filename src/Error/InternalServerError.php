<?php

namespace Ergo\Http\Error;

class InternalServerError extends Exception
{
    const STATUS_CODE = 500;

    public function __construct($string)
    {
        parent::__construct($string, self::STATUS_CODE);
    }
}
