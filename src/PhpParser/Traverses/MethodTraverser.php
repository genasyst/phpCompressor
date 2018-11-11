<?php

namespace Genasyst\phpCompressor\PhpParser\Traverses;

use PhpParser\Node;
use Genasyst\phpCompressor\PhpParser\Traverses\Entity\Object_;
use Genasyst\phpCompressor\PhpParser\Traverses\Entity\Global_;
use Genasyst\phpCompressor;
use PhpParser\NodeVisitorAbstract;
use \Genasyst\phpCompressor\PhpParser\Visitors\Traverser;

class MethodTraverser extends \PhpParser\NodeTraverser
{


    protected $method = 'leaveNode';
    protected $scope = null;
    protected $global_visitor = null;
    public function __construct(  Traverser\GlobalVisitor $global_visitor, $method = 'leaveNode', $scope = null)
    {
        $this->scope = $scope;
        $this->method = $method;
        $this->global_visitor = $global_visitor;
        parent::__construct();
    }

    /**
     * Traverses an array of nodes using the registered visitors.
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return Node[] Traversed array of nodes
     */
    public function traverse(array $nodes)
    {
        $nodes = $this->traverseArray($nodes);

        return $nodes;
    }

    /*public function getTraverser($node) {
        $traverser = null;
        if($node instanceof Node\Stmt\TraitUse) {
            $traverser =  new Object_\TraitUse($node);
        } elseif($node instanceof Node\Stmt\Property) {
            $traverser =  new Object_\Property($node);
        } elseif($node instanceof Node\Stmt\ClassMethod){
            $traverser = new Object_\ClassMethod($node);
        }elseif($node instanceof Node\Stmt\Function_) {
            $traverser = new Global_\Function_($node);
        }
        if($traverser) {
            $traverser->setParent($this->scope);
        }

        return $traverser;
    }*/
    public function setScope(Node $node)
    {
        if (is_object($node->getAttribute('scope_data'))) {
            return;
        }
        $scope = null;
        if ($node instanceof Node\Stmt\TraitUse) {
            $scope = new  \Genasyst\phpCompressor\PhpParser\ScopeData\ScopeData($node);
        }
        if ($node instanceof Node\Stmt\Class_) {
            $scope = new  \Genasyst\phpCompressor\PhpParser\ScopeData\Class_($node);
        } elseif ($node instanceof Node\Stmt\Property) {
            $scope = new  \Genasyst\phpCompressor\PhpParser\ScopeData\ScopeData($node);
        } elseif ($node instanceof Node\Stmt\ClassMethod) {
            $scope = new  \Genasyst\phpCompressor\PhpParser\ScopeData\ClassMethod($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $scope = new  \Genasyst\phpCompressor\PhpParser\ScopeData\Function_($node);
        }/* elseif($node instanceof Node\Expr\FuncCall) {
            $scope =  new  Genasyst\phpCompressor\PhpParser\ScopeData\Function_($node);
        } */ elseif ($node instanceof Node\Expr\ConstFetch) {
            $scope = new  \Genasyst\phpCompressor\PhpParser\ScopeData\Constants($node);
        } else {
            $scope = new  \Genasyst\phpCompressor\PhpParser\ScopeData\ParentScope($node);
        }
        if ($scope) {
            $scope->setParent($this->scope);
            $node->setAttribute('scope_data', $scope);
        }

        return $scope;
    }

    protected function getVisitors()
    {
        $visitors = [];
        /*TODO: ГОВНОКОД с передачей настроек какой - то получился)))*/
        if($this->global_visitor->isEnableCompressType(phpCompressor\Compressor::COMPRESS_TYPE_LOCAL_VARIABLES)){
            $visitors[] =  new  \Genasyst\phpCompressor\PhpParser\Visitors\Base\LocalVariables(
                                    $this->global_visitor->getCompressSettings(phpCompressor\Compressor::COMPRESS_TYPE_LOCAL_VARIABLES)
                                );
        }
        if($this->global_visitor->isEnableCompressType(phpCompressor\Compressor::COMPRESS_TYPE_OBJECT_VARIABLES)){
            $visitors[] =  new  \Genasyst\phpCompressor\PhpParser\Visitors\Base\ObjectVariables(
                $this->global_visitor->getCompressSettings(phpCompressor\Compressor::COMPRESS_TYPE_OBJECT_VARIABLES)
            );
        }
        if($this->global_visitor->isEnableCompressType(phpCompressor\Compressor::COMPRESS_TYPE_OBJECT_METHODS)){
            $visitors[] =  new  \Genasyst\phpCompressor\PhpParser\Visitors\Base\ObjectMethods(
                $this->global_visitor->getCompressSettings(phpCompressor\Compressor::COMPRESS_TYPE_OBJECT_METHODS)
            );
        }
        return $visitors;
    }

    protected function initVisitors($traverser)
    {
        foreach ($this->getVisitors() as $visitor) {
            $traverser->addVisitor($visitor);
        }
    }

    protected function traverseNode(Node $node)
    {

        foreach ($node->getSubNodeNames() as $name) {
            $subNode =& $node->$name;

            if (is_array($subNode)) {
                $traverser = new MethodTraverser($this->global_visitor, $this->method, $node->getAttribute('scope_data'));
                $this->initVisitors($traverser);
                $traverser->traverse($subNode);
                // $subNode = $this->traverseArray($subNode, $node);
                if ($this->stopTraversal) {
                    break;
                }
            } elseif ($subNode instanceof Node) {
                $this->setScope($subNode);

                $traverseChildren = true;
                foreach ($this->getVisitors() as $visitor) {
                    $return = $visitor->enterNode($subNode);
                    if (self::DONT_TRAVERSE_CHILDREN === $return) {
                        $traverseChildren = false;
                    } else if (self::STOP_TRAVERSAL === $return) {
                        $this->stopTraversal = true;
                        break 2;
                    } else if (null !== $return) {
                        $subNode = $return;
                    }
                }

                if ($traverseChildren) {
                    $subNode = $this->traverseNode($subNode);
                    if ($this->stopTraversal) {
                        break;
                    }
                }

                foreach ($this->getVisitors() as $visitor) {
                    $return = null;
                    if (is_callable([$visitor, $this->method])) {
                        $return = $visitor->{$this->method}($subNode);
                    }
                    if (self::STOP_TRAVERSAL === $return) {
                        $this->stopTraversal = true;
                        break 2;
                    } else if (null !== $return) {
                        if (is_array($return)) {
                            throw new \LogicException(
                                'leaveNode() may only return an array ' .
                                'if the parent structure is an array'
                            );
                        }
                        $subNode = $return;
                    }
                }
            }
        }

        return $node;
    }

    protected function traverseArray(array $nodes, $parent_node = null)
    {
        $doNodes = array();

        foreach ($nodes as $i => &$node) {
            if (is_array($node)) {
                $node = $this->traverseArray($node);
                if ($this->stopTraversal) {
                    break;
                }
            } elseif ($node instanceof Node) {
                if ($parent_node == null) {
                    $this->setScope($node);
                }

                $traverseChildren = true;

                foreach ($this->visitors as $visitor) {
                    $return = $visitor->enterNode($node);
                    if (self::DONT_TRAVERSE_CHILDREN === $return) {
                        $traverseChildren = false;
                    } else if (self::STOP_TRAVERSAL === $return) {
                        $this->stopTraversal = true;
                        break 2;
                    } else if (null !== $return) {
                        $node = $return;
                    }
                }

                if ($traverseChildren) {
                    $node = $this->traverseNode($node);
                    if ($this->stopTraversal) {
                        break;
                    }
                }

                foreach ($this->visitors as $visitor) {
                    $return = null;
                    if (is_callable([$visitor, $this->method])) {
                        $return = $visitor->{$this->method}($node);
                    }

                    if (self::REMOVE_NODE === $return) {
                        $doNodes[] = array($i, array());
                        break;
                    } else if (self::STOP_TRAVERSAL === $return) {
                        $this->stopTraversal = true;
                        break 2;
                    } elseif (is_array($return)) {
                        $doNodes[] = array($i, $return);
                        break;
                    } elseif (null !== $return) {
                        $node = $return;
                    }
                }
            }
        }

        if (!empty($doNodes)) {
            while (list($i, $replace) = array_pop($doNodes)) {
                array_splice($nodes, $i, 1, $replace);
            }
        }

        return $nodes;
    }

}