<?php

namespace Symfony\BundleGenerator;

/**
 * Description of Parameter
 *
 * @author Pierre-Gildas MILLON <pierre-gildas.millon@ineat-conseil.fr>
 */
class Parameter
{
    protected $message;
    protected $defaultValue;
    protected $value;

    function __construct($message, $defaultValue)
    {
        $this->message = $message;
        $this->defaultValue = $defaultValue;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getValue()
    {
        return is_null($this->value) ? $this->getDefaultValue() : $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}

