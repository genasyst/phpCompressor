<?php

namespace Genasyst\phpCompressor\PhpParser\Traverses\Entity;


class ClassTraverser extends ObjectTraverser
{

    public function afterTraverse()
    {
        // TODO: Implement afterTraverse() method.
    }
    /*protected $stage = '';
    protected $registry = null;
    protected $compressor = null;
    public function __construct ($data) {

        if(method_exists($this, $stage)){
            $this->stage = $stage;
        }
        $this->initRegistry();
        $this->compressor = new \Genasyst\phpCompressor\NamesCompressor\NamesCompressor($this->registry);
    }
    public function leaveNode(\PhpParser\Node $node) {
        return $this->{$this->stage}($node);
    }
    protected function getCompressor(){
        return $this->compressor;
    }
    abstract protected function initRegistry();
    abstract protected function prepare(\PhpParser\Node $node);

    abstract protected function replace(\PhpParser\Node $node);

    public function __call ($name, $arguments) {
        echo 'Not found stage method '.$this->stage;
    }*/
}