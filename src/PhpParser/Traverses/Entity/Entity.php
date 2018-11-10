<?php

namespace Genasyst\phpCompressor\PhpParser\Traverses\Entity;

use Genasyst\phpCompressor\PhpParser\ScopeData\ScopeData;
use Genasyst\phpCompressor\PhpParser\Traverses\CodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Node;

abstract class Entity
{
    protected $data = null;
    /**
     * @var ScopeData
     */
    protected $scope_object = null;

    public function __construct(Node\Stmt $node)
    {
        $this->data = $node;
        if (is_object($node->getAttribute('scope_object'))) {
            $this->scope_object = $node->getAttribute('scope_object');
        } else {
            $this->scope_object = new ScopeData($node);
        }
    }

    abstract public function beforeTraverse();

    abstract public function traverse();

    abstract public function afterTraverse();

    public function setParent(ScopeData $scope)
    {
        $this->scope_object->setParent($scope);
    }

    public function getTraverser($node)
    {
        $traverser = null;
        if ($node instanceof Node\Stmt\TraitUse) {
            $traverser = new Object_\TraitUse($node);
        } elseif ($node instanceof Node\Stmt\Property) {
            $traverser = new Object_\Property($node);
        } elseif ($node instanceof Node\Stmt\ClassMethod) {
            $traverser = new Object_\ClassMethod($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $traverser = new Global_\Function_($node);
        }
        if ($traverser) {
            $traverser->setParent($this->scope_object);
        }

        return $traverser;
    }

    public function getParent()
    {
        return $this->scope_object->getParent();
    }

}