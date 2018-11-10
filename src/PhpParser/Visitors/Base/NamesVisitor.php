<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Base;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use phpCompressor;

abstract class NamesVisitor extends BaseVisitor
{
    protected $exclude_names = [];
    
    public function __construct(array $settings = [], $context = null)
    {
        $this->setExcludeNames($settings['exclude_names']);
        parent::__construct($context);
    }
    
    public function setExcludeNames(array $exclude_names)
    {
        $this->exclude_names = $exclude_names;
    }

  
}