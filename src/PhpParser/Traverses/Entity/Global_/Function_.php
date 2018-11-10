<?php

namespace Genasyst\phpCompressor\PhpParser\Traverses\Entity\Global_;

use Genasyst\phpCompressor\PhpParser\Traverses\Entity\Entity;
use Genasyst\phpCompressor\PhpParser\Visitors\Base\LocalVariables;
use Genasyst\phpCompressor;

use PhpParser\Node;


class Function_ extends Entity
{
    /**
     * @var  Node\Stmt\ClassLike $data
     */
    protected $data = null;
    
    protected $scope_object = null;
    
    protected $properties_names_storage = null;

    public function __construct(Node\Stmt\Function_ $node)
    {
        parent::__construct($node);
    }

    protected function getVisitors()
    {
        return [
            new LocalVariables($this->scope_object),
        ];
    }

    protected function initVisitors($traverser)
    {
        foreach ($this->getVisitors() as $visitor) {
            $traverser->addVisitor($visitor);
        }
    }

    public function beforeTraverse()
    {
      
        $traverser = new \Genasyst\phpCompressor\PhpParser\Traverses\MethodTraverser('beforeNode');
        $this->initVisitors($traverser);
        $data = [&$this->data];
        $traverser->traverse($data);
     
        $this->data->setAttribute('scope_object', $this->scope_object);
    }

    public function traverse()
    {
        $traverser = new \Genasyst\phpCompressor\PhpParser\Traverses\MethodTraverser('leaveNode');
        $this->initVisitors($traverser);
        $data = [&$this->data];
        $traverser->traverse($data);
    }

    public function afterTraverse()
    {
        $traverser = new \Genasyst\phpCompressor\PhpParser\Traverses\MethodTraverser('afterNode');
        $this->initVisitors($traverser);
        $data = [&$this->data];
        $traverser->traverse($data);
        $this->data->setAttribute('scope_object', $this->scope_object);
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