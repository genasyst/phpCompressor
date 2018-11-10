<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Base;

use PhpParser\Node;
use Genasyst\phpCompressor\NamesRegistry;
use Genasyst\phpCompressor\PhpParser\Visitors\VisitorBase;

class ObjectVariables extends LocalVariables
{

    protected static $variables_storage = null;
    
    public function __construct($context = null)
    {
        parent::__construct($context);
    }

    protected function isExcludeName($name)
    {
        return array_key_exists($name, $this->exclude_names);
    }

    protected function findNodes(Node $node)
    {
        $real_nodes = [];

        if ($node instanceof Node\Stmt\Property && !$node->isStatic()) {
            if (isset($node->props)) {
                foreach ($node->props as $v) {

                    if ($v instanceof Node\Stmt\PropertyProperty) {
                        $real_nodes[] = $v;
                    }
                }
            }
        } else if ($node instanceof Node\Expr\PropertyFetch) {
            $real_nodes[] = $node;
        }

        return $real_nodes;
    }

    protected function before(Node $node)
    {
        if (!$this->isExcludeName($this->getNodeName($node))) {
            $node->setAttribute('type', 'object_variable');
            $this->addVariable($this->getNodeName($node));
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node->getAttribute('type') == 'object_variable' && !$this->isExcludeName($this->getNodeName($node))) {
            $this->setNodeName($node, $this->getShortName($this->getNodeName($node)));
        }
    }

    public function getVariablesStorage()
    {
        if (static::$variables_storage === null) {
            static::$variables_storage = new \Genasyst\phpCompressor\NamesRegistry\ObjectsVariables($this->exclude_names);
        }

        return static::$variables_storage;
    }

    public function getShortName($name)
    {

        return $this->getVariablesStorage()->getShortName($name);
    }

    protected function addVariable($name)
    {
        $this->getVariablesStorage()->set($name);
    }
}