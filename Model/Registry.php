<?php

namespace Buildateam\CustomProductBuilder\Model;

class Registry
{
    /**
     * Registry collection
     *
     * @var array
     */
    private $registry;

    /**
     * @return array
     */
    public function registry()
    {
        return $this->registry;
    }

    /**
     * @param $value
     */
    public function register($value)
    {
        $this->registry = $value;
    }
}
