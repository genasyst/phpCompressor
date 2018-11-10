<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Traverser;

use PhpParser\Node;
use phpCompressor;
use Genasyst\phpCompressor\PhpParser\Traverses\Entity;

class ClassVisitor extends EntityVisitor
{

    public function __construct()
    {
    }

    protected function getTraverser($node)
    {
        return new Entity\Object_\Class_($node);
    }

    public function checkType(Node $node)
    {
        return ($node instanceof Node\Stmt\Class_);
    }
}