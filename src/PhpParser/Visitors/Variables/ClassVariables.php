<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Variables;

use PhpParser\Node;
use Genasyst\phpCompressor\NamesRegistry;
use Genasyst\phpCompressor\PhpParser\Visitors\VisitorBase;

class ClassVariables extends VisitorBase
{

    protected function initRegistry()
    {
        $this->registry = NamesRegistry\ClassVariables::getInstance();
    }

    protected function prepare(Node $node)
    {

    }

    protected function prepareNode(Node $node)
    {
        //  $node->setAttribute('type', 'variable');
        // $this->registry->set($node->name);
    }

    protected function replace(Node $node)
    {
        // TODO: Implement replace() method.
    }
}