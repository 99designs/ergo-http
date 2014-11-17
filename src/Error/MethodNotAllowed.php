<?php

namespace Ergo\Http\Error;

class MethodNotAllowed extends Exception
{
    const STATUS_CODE = 405;

    public function __construct($string)
    {
        parent::__construct($string, self::STATUS_CODE);
    }
}
