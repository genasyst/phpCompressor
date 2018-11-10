<?php

namespace Genasyst\phpCompressor\PhpParser\Traverses\Entity\Object_;

use Genasyst\phpCompressor\PhpParser\Traverses\Entity\Entity;
use PhpParser\Node;
use Genasyst\phpCompressor;
use Genasyst\phpCompressor\PhpParser\ScopeData;

abstract class BaseObject extends Entity
{
    /**
     * @var  Node\Stmt\ClassLike $data
     */
    protected $data = null;
    protected $scope_object = null;
    protected $properties_names_storage = null;

    public function __construct(Node\Stmt\ClassLike $node)
    {
        parent::__construct($node);
        if (is_object($node->getAttribute('scope_object'))) {
            $this->scope_object = $node->getAttribute('scope_object');
        } else {
            $this->scope_object = new ScopeData\Class_($node);
        }
    }

    public function beforeTraverse()
    {
        foreach ($this->data->stmts as $v) {
            $traverser = $this->getTraverser($v);
            if ($traverser) {
                $traverser->beforeTraverse();
            }
        }
        $this->data->setAttribute('scope_object', $this->scope_object);
        // var_dump($this->data);
    }

    public function traverse()
    {
        foreach ($this->data->stmts as $v) {
            $traverser = $this->getTraverser($v);
            if ($traverser) {
                $traverser->traverse();
            }
        }
    }

    public function afterTraverse()
    {
        foreach ($this->data->stmts as $v) {
            $traverser = $this->getTraverser($v);
            if ($traverser) {
                $traverser->afterTraverse();
            }
        }
    }

    public function getName()
    {
        return $this->data->name;
    }

    public function getScopeObject()
    {
        return $this->scope_object;
    }

}