<?php
namespace Genasyst\phpCompressor\PhpParser\ScopeData;

class Function_ extends ScopeData
{
    public function init()
    {
        $_storage = \Genasyst\phpCompressor\NamesRegistry\Functions::getInstance();
        if (is_string($this->node->name)) {
            /*$this->variables_storage->set($this->node->name);*/
        } elseif (is_object($this->node->name) && $this->node->name instanceof \PhpParser\Node\Name && count($this->node->name->parts) == 1) {
            $_storage->set($this->node->name->parts[0]);
        }
    }
}
