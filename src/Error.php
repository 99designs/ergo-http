<?php

namespace Ergo\Http;

class Error extends \Exception
{
    public function getStatusCode()
    {
        return $this->getCode();
    }
}
