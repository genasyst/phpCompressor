<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Base;

use PhpParser\Node;
use Genasyst\phpCompressor\NamesRegistry;
use Genasyst\phpCompressor\PhpParser\Visitors\VisitorBase;

class ObjectMethods extends NamesVisitor
{

    protected static $variables_storage = null;

    protected function getNodeName($node)
    {
        /* if($node instanceof Node\Expr\ClosureUse) {
             return $node->var;
         }else {*/
        return $node->name;
        // }
    }

    protected function setNodeName($node, $name)
    {
        /* if($node instanceof Node\Expr\ClosureUse) {
             $node->var = $name;
         } else {*/
        if (!property_exists($node, 'name') || !is_string($node->name)) {
            var_dump($node);
        }
        $node->name = $name;
        // }
    }

    protected function findNodes(Node $node)
    {
        $real_nodes = [];

        if ($node instanceof Node\Stmt\ClassMethod) {
            $real_nodes[] = $node;
        } elseif ($node instanceof Node\Expr\MethodCall && is_string($node->name)) {
            $real_nodes[] = $node;
        } elseif ($node instanceof Node\Expr\StaticCall) {
            $real_nodes[] = $node;
        }

        return $real_nodes;
    }

    protected function before(Node $node)
    {
        if (!$this->isExcludeName($this->getNodeName($node))) {
            $node->setAttribute('type', 'object_method');
            $this->addVariable($this->getNodeName($node));
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node->getAttribute('type') == 'object_method' && !$this->isExcludeName($this->getNodeName($node))) {
            if ($node instanceof Node\Stmt\ClassMethod) {
                if ($node->isStatic()) {
                    if ($node->isAbstract()) {
                        $node->flags = Node\Stmt\Class_::MODIFIER_STATIC & Node\Stmt\Class_::MODIFIER_ABSTRACT;
                    } else {
                        $node->flags = Node\Stmt\Class_::MODIFIER_STATIC;
                    }
                    $node->flags = Node\Stmt\Class_::MODIFIER_STATIC;

                    return;
                } elseif ($node->isAbstract()) {
                    $node->flags = Node\Stmt\Class_::MODIFIER_ABSTRACT;
                } else {
                    $node->flags = 0;
                }

            } elseif ($node instanceof Node\Expr\StaticCall && $node->class != 'parent' /*TODO: bug! Надо еще определить что вызывается не из статичекого метода*/) {
                return;
            }

            $this->setNodeName($node, $this->getShortName($this->getNodeName($node)));
        }

    }

    protected function isExcludeName($name)
    {
        if (preg_match('/[a-zA-Z0-9_]+Action$/', $name)) {
            return true;
        }

        return array_key_exists($name, $this->exclude_names);
    }

    /**
     * @return NamesRegistry\ObjectsMethods
     */
    public function getVariablesStorage()
    {
        if (static::$variables_storage === null) {
            static::$variables_storage = new \Genasyst\phpCompressor\NamesRegistry\ObjectsMethods($this->exclude_names);
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