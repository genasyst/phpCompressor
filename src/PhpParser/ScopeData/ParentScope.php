<?php

namespace Genasyst\phpCompressor\PhpParser\ScopeData;

use PhpParser\Node;

class ParentScope
{
    protected $scope = null;

    public function __construct($node = null)
    {

    }

    public function getParent()
    {
        return $this->scope;
    }

    public function setParent($parent = null)
    {
        $this->scope = $parent;
    }

    public function __call($name, $args)
    {
        if (is_callable([$this->scope, $name])) {
            return call_user_func_array(array($this->scope, $name), $args);
        }

        return null;

    }
}