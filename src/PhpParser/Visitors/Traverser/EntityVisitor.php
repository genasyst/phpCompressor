<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Traverser;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;


abstract class EntityVisitor extends NodeVisitorAbstract
{

    abstract public function checkType(Node $node);


    public function beforeTraverse(array $nodes)
    {

        foreach ($nodes as $node) {
            if ($this->checkType($node)) {
                $this->beforeNode($node);
            }
        }
    }

    abstract protected function getTraverser($node);

    public function beforeNode(Node $node)
    {
        $this->getTraverser($node)->beforeTraverse();
        //var_dump($node);
    }

    protected function run($node)
    {
        $this->getTraverser($node)->traverse();
    }

    public function afterNode(Node $node)
    {
        $this->getTraverser($node)->afterTraverse();
    }

    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($this->checkType($node)) {
                $this->afterNode($node);
            }
        }
    }

    public function leaveNode(Node $node)
    {
        if ($this->checkType($node)) {
            $this->run($node);
        }
    }

    public function enterNode(Node $node)
    {
        return $this->checkType($node) ? 1 : null;
    }

    public function __call($name, $arguments)
    {
        echo 'Not found stage method ' . get_class($this) . "::" . $name;
    }
}