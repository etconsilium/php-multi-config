<?php

namespace MultiConfig\Exception;

class FileNotFoundException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
