<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Traverser;

use Genasyst\phpCompressor;
use Genasyst\phpCompressor\PhpParser\Traverses\Entity;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class GlobalVisitor extends NodeVisitorAbstract
{
    protected $visitors = [];
    protected $compress_settings = [];

    public function __construct($compress_settings = [])
    {
        $this->visitors = [
            new ClassVisitor(),
            new FunctionVisitor(),
        ];
        $this->compress_settings = $compress_settings;
    }
    
    public function getCompressSettings($type = null)
    {
        if(array_key_exists($type, $this->compress_settings)) {
            return $this->compress_settings[$type];
        }
        return null;
    }
    public function isEnableCompressType($type) {
        $settings = $this->getCompressSettings($type);
        if($settings && array_key_exists('enable',$settings)){
            return $settings['enable'];
        }
        return false;
    }
    public function beforeTraverse(array $nodes)
    {
        $global_scope = new  \Genasyst\phpCompressor\PhpParser\ScopeData\ScopeData();
      
        $traverser = new  \Genasyst\phpCompressor\PhpParser\Traverses\MethodTraverser($this,'beforeNode', $global_scope);
        $traverser->traverse($nodes);
        
        $traverser = new  \Genasyst\phpCompressor\PhpParser\Traverses\MethodTraverser($this,'leaveNode');
        $traverser->traverse($nodes);

    }

    public function leaveNode(Node $node)
    {
       
    }

    public function afterTraverse(array $nodes)
    {
       
    }

    public function __call($name, $arguments)
    {
        echo 'Not found stage method ' . get_class($this) . "::" . $name;
    }
}