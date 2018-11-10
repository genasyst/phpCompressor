<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Base;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use phpCompressor;

abstract class BaseVisitor extends NodeVisitorAbstract
{

    protected $scope_object = null;


    abstract protected function findNodes(\PhpParser\Node $node);
    
    protected function init()
    {
    }

    public function __construct($context = null)
    {
        $this->setScopeObject($context);
        $this->init();
    }

    public function setScopeObject($scope_object = null)
    {
        if (is_object($scope_object)) {
            $this->scope_object = $scope_object;
        }
    }

    protected function before(\PhpParser\Node $node)
    {
    }

    protected function after(\PhpParser\Node $node)
    {
    }

   

    public function beforeNode(\PhpParser\Node $node)
    {
        $this->setScopeObject($node->getAttribute('scope_data'));
        $real_nodes = $this->findNodes($node);
        if ($real_nodes) {
            if (is_array($real_nodes)) {
                foreach ($real_nodes as $v) {
                    $this->before($v);
                }
            }
        }
    }

    protected function afterNode(\PhpParser\Node $node)
    {
        $real_nodes = $this->findNodes($node);
        if ($real_nodes) {
            if (is_array($real_nodes)) {
                foreach ($real_nodes as $v) {
                    $this->before($v);
                }
            }
        }
    }
}