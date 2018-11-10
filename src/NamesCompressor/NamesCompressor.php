<?php

namespace Genasyst\phpCompressor\NamesCompressor;

use Genasyst\phpCompressor\NamesRegistry\NamesRegistry;
use Genasyst\phpCompressor\Utils\StringGenerator;

class NamesCompressor
{

    protected $registry = null;
    
    protected $data = [];

    public function __construct(NamesRegistry $registry)
    {
        $this->init($registry);
    }

    protected function init(NamesRegistry $registry)
    {
        $names = $registry->getAll();
        arsort($names);
        $generator = new StringGenerator();
        $short_names = $generator->range('a', 'zzzzz', count($names), $registry->getExcludeNames());
        $this->data = array_combine(array_keys($names), $short_names);
    }

    public function getShortName($name)
    {
        return $this->data[$name];
    }

}