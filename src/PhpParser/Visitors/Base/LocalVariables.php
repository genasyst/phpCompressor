<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Base;

use PhpParser\Node;
use Genasyst\phpCompressor\NamesRegistry;
use Genasyst\phpCompressor\PhpParser\Visitors\VisitorBase;

class LocalVariables extends NamesVisitor
{

    protected $names_storage = null;

    protected function init()
    {
        //$this->names_storage = new \Genasyst\phpCompressor\NamesRegistry\LocalVariables();
    }

    protected function getNodeName($node)
    {
        if ($node instanceof Node\Expr\ClosureUse) {
            return $node->var;
        } else {
            return $node->name;
        }
    }

    protected function setNodeName($node, $name)
    {
        if ($node instanceof Node\Expr\ClosureUse) {
            $node->var = $name;
        } else {
            $node->name = $name;
        }
    }

    protected function findNodes(Node $node)
    {

        /* переменные объявленные в коде */
        $real_nodes = [];
        /* $var = ....  || $var['key']...*/
        if ($node instanceof Node\Expr\Assign /* $var = ...*/
            || $node instanceof Node\Expr\ArrayDimFetch /* $var['key']...*/
        ) {
            if (isset($node->var) && ($node->var instanceof Node\Expr\Variable)) {
                $real_nodes[] = $node->var;
            }
            if ($node instanceof Node\Expr\ArrayDimFetch) {
                /* $arr_nodes = function ($node) {
                     function array_filter_recursive($input, $callback = null, &$return = [])  {
                         foreach ($input as $value) {
                             if (is_array($value)) {
                                  array_filter_recursive($value, $callback, $return);
                             }
                         }
                         if(!is_array($input) && ($input instanceof Node\Expr\Variable)) {
 
                         }
                         $return += array_filter($input, $callback);
                     };
                     $return = [];
                     array_filter_recursive($node, function($v){
                         if(isset($node->var)  && ($v instanceof Node\Expr\Variable)){
                           return 1;
                         }
                         return 0;
                     },$return);
                    return $return;
                 };*/
                if (isset($node->dim) && ($node->dim instanceof Node\Expr\Variable)) {
                    $real_nodes[] = $node->dim;
                }
                //var_dump($arr_nodes($node->var)); echo "4333333333333333333333333333333333333333333333333333333333333333333333333333333333333";
            }

        } elseif ($node instanceof Node\Expr\MethodCall) {
            /* вызов метода из переменной $var->method_name(...) || $var->$name (...) */
            if (isset($node->var) && ($node->var instanceof Node\Expr\Variable)) {
                $real_nodes[] = $node->var;
            }
            if (isset($node->name) && ($node->name instanceof Node\Expr\Variable)) {
                $real_nodes[] = $node->name;
            }
        } elseif ($node instanceof Node\Stmt\Static_) {
            /* static $var; */
            if (isset($node->vars) && is_array($node->vars)) {
                foreach ($node->vars as $param) {
                    if ($param instanceof Node\Stmt\StaticVar) {
                        $real_nodes[] = $param;
                    }
                }
            }
        } elseif ($node instanceof Node\Stmt\Function_) {
            /* Переменные-параметры функций funct(param1,param2...){...body...} */
            if (isset($node->params) && is_array($node->params)) {
                foreach ($node->params as $param) {
                    if ($param instanceof Node\Param) {
                        $real_nodes[] = $param;
                    }
                }
            }
        } elseif ($node instanceof Node\Expr\ClosureUse) {
            /* Переменные-параметры use     функций funct(...)   use($var,) */
            /*!!!!!!!!!!!!!! Исключение !!!!!!!!!!!!!!!!!!! Нет имени!
            object(PhpParser\Node\Expr\ClosureUse)#46 (3) {
                  ["var"]=>
                  string(1) "s"
                  ["byRef"]=>
                  bool(false)
                  ["attributes":protected]=>
                  array(2) {
                    ["startLine"]=>
                    int(20)
                    ["endLine"]=>
                    int(20)
                  }
                }
            */
            $real_nodes[] = $node;

        } elseif ($node instanceof Node\Stmt\ClassMethod) {

            if (isset($node->params) && is_array($node->params)) {
                foreach ($node->params as $param) {
                    if ($param instanceof Node\Param) {
                        $real_nodes[] = $param;
                    }
                }
            }

        } elseif ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            /* Переменные в условии IF  - if($name){  || ($name && $var) */
            if (isset($node->left) && ($node->left instanceof Node\Expr\Variable)) {
                $real_nodes[] = $node->left;
            }
            if (isset($node->right) && ($node->right instanceof Node\Expr\Variable)) {
                $real_nodes[] = $node->right;
            }
        } else if ($node instanceof Node\Arg) {
            /*Переменые - аргументы вызываемых функций  $v = funct(arg1,arg2...);*/
            if (isset($node->value) && ($node->value instanceof Node\Expr\Variable)) {
                $real_nodes[] = $node->value;
            }
            /* Переменные цикла foreach (expr as keyVar => valueVar) */
        } else if ($node instanceof Node\Stmt\Foreach_) {
            foreach (['expr', 'keyVar', 'valueVar'] as $v) {
                if (isset($node->$v) && ($node->$v instanceof Node\Expr\Variable)) {
                    $real_nodes[] = $node->$v;
                }
            }
        } else if (($node instanceof Node\Expr\Variable) && !$this->isExcludeName($this->getNodeName($node))) {
            $real_nodes[] = $node;
        } else if (($node instanceof Node\Param) && !$this->isExcludeName($this->getNodeName($node))) {
            $real_nodes[] = $node;
        }

        return $real_nodes;
    }

    protected function isExcludeName($name)
    {
        return array_key_exists($name, $this->exclude_names);
    }

    protected function before(Node $node)
    {
        if (!$this->isExcludeName($this->getNodeName($node))) {
            $node->setAttribute('type', 'local_variable');
            $this->setScopeObject($node->getAttribute('scope_data'));
            $this->addVariable($this->getNodeName($node));
        }

    }

    public function leaveNode(Node $node)
    {
        if ($node->getAttribute('type') == 'local_variable' && !$this->isExcludeName($this->getNodeName($node))) {
            $this->setScopeObject($node->getAttribute('scope_data'));
            // var_dump($this->getNodeName($node).' = '.$this->getShortName($this->getNodeName($node)));
            $this->setNodeName($node, $this->getShortName($this->getNodeName($node)));
        }
    }

    public function getShortName($name)
    {
        return $this->scope_object->getVariableShortName($name);
    }

    protected function addVariable($name)
    {
        $this->scope_object->addVariable($name);
    }

    public function getVariablesStorage()
    {
        return $this->names_storage;
    }
}