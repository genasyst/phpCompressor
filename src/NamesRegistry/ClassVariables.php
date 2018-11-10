<?php

namespace Genasyst\phpCompressor\NamesRegistry;

class ClassVariables
{
    protected static $instance;

    protected static $data = [];

    protected $class = '';

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function set($name)
    {
        if ($this->has($name)) {
            static::$data[$this->class][$name]++;
        } else {
            static::$data[$this->class][$name] = 1;
        }
    }

    protected function hasClass($class)
    {
        return array_key_exists($class, static::$data);
    }

    public function has($name)
    {
        return ($this->hasClass($this->class) && array_key_exists($name, static::$data[$this->class][$name]));
    }

    public function get($name)
    {
        if ($this->has($name)) {
            return static::$data[$this->class][$name];
        }

        return 0;
    }

    public function getAll($class = null)
    {
        if ($class === false) {
            return static::$data;
        }
        if ($class === null) {
            return $this->hasClass($this->class) ?
                static::$data[$this->class] : [];
        }

        return $this->hasClass($class) ?
            static::$data[$class] : [];
    }
}