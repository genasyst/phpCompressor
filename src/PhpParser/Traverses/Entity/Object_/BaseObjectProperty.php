<?php

namespace Genasyst\phpCompressor\PhpParser\Traverses\Entity\Object_;

use Genasyst\phpCompressor\PhpParser\Traverses\Entity\Entity;

abstract class BaseObjectProperty extends Entity
{
    protected function getClass()
    {
        return $this->getParent()->getName();
    }

    public function getPropertiesStorage()
    {
        return $this->getParent()->getPropertiesStorage();
    }

    public function getLocalVariablesStorage()
    {
        return $this->variables_names_storage;
    }
}