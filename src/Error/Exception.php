<?php

namespace Ergo\Http\Error;

class Exception extends \Exception
{
    public function getStatusCode()
    {
        return $this->getCode();
    }
}
