<?php

namespace Ergo\Http\Error;

use Ergo\Http\Error;

class CurlError extends Error
{
    public function __construct($string)
    {
        parent::__construct($string);
    }
}
