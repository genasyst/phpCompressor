<?php

namespace Genasyst\phpCompressor\NamesRegistry;

use Genasyst\phpCompressor\NamesCompressor\NamesCompressor;

class NamesRegistry
{
    protected static $instance;

    protected $data = [];
    
    /**
     * @var NamesCompressor
     */
    protected $compressor = null;
    
    protected $exclude_names = [];

    final public static function getInstance($exclude_names = null)
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static($exclude_names);
    }
    
    public function __construct($exclude_names = null)
    {
        $this->setExcludeNames($exclude_names);
        $this->init();
    }

    public function getExcludeNames()
    {
        return $this->exclude_names;
    }

    public function setExcludeNames($exclude_names = null)
    {
        if (is_array($exclude_names)) {
            $this->exclude_names = $exclude_names;
        }
    }

    protected function init()
    {
    }

    final private function __wakeup()
    {
    }

    final private function __clone()
    {
    }

    public function set($name)
    {
        if ($this->has($name)) {
            $this->data[$name]++;
        } else {
            $this->data[$name] = 1;
        }
    }

    public function has($name)
    {
        return array_key_exists($name, $this->data);
    }

    public function get($name)
    {
        if ($this->has($name)) {
            return $this->data[$name];
        }

        return 0;
    }

    public function getAll()
    {
        return $this->data;
    }

    public function initCompressor()
    {
        $this->compressor = new NamesCompressor($this);
    }

    public function getShortName($name)
    {
        if ($this->compressor === null) {
            $this->initCompressor();
        }

        return $this->compressor->getShortName($name);
    }
}