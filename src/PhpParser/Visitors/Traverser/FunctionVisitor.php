<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Traverser;

use PhpParser\Node;
use phpCompressor;
use Genasyst\phpCompressor\PhpParser\Traverses\Entity;

class FunctionVisitor extends EntityVisitor
{

    public function __construct()
    {
    }

    protected function getTraverser($node)
    {
        return new Entity\Global_\Function_($node);
    }

    public function checkType(Node $node)
    {
        return ($node instanceof Node\Stmt\Function_);
    }
}