<?php

namespace Genasyst\phpCompressor\PhpParser\ScopeData;

use PhpParser\Node;

class ScopeData
{
    protected $scope = null;

    protected $variables_storage = null;

    protected $parent_scope = null;

    protected $functions = [];

    protected $node = null;

    public function __construct($node = null, $parent_scope = null)
    {
        $this->node = $node;
        $this->setParent($parent_scope);
        $this->variables_storage = new \Genasyst\phpCompressor\NamesRegistry\LocalVariables();
        $this->init();
    }

  /*  public function fte()
    {
        return $this->variables_storage;
    }*/

    public function setParent($scope)
    {
        $this->parent_scope = $scope;
    }

    public function init()
    {
    }

    public function getName()
    {
        $this->scope->name;
    }

    public function getParent()
    {
        return $this->parent_scope;
    }

    public function addVariable($name)
    {
        $this->variables_storage->set($name);
    }

    public function getVariableShortName($name)
    {
        return $this->variables_storage->getShortName($name);
    }

    public function addFunction($name)
    {

    }
}