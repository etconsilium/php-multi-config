<?php namespace MultiConfig\Exception;

/**
 * KeyNotExistException
 *
 * @package etconsilium\php-multi-config
 */
class KeyNotExistException extends \Exception
{
    public function __construct($message='', $code=0, \Exception $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }
}