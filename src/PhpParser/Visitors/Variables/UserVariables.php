<?php

namespace Genasyst\phpCompressor\PhpParser\Visitors\Variables;

use PhpParser\Node;
use Genasyst\phpCompressor\NamesRegistry;
use Genasyst\phpCompressor\PhpParser\Visitors\VisitorBase;

class UserVariables extends VisitorBase
{

    protected function initRegistry()
    {
        $this->registry = NamesRegistry\ClassVariables::getInstance();
    }

    protected function prepare(Node $node)
    {
        /* переменные объявленные в коде */
        $name = null;
        /* $var = ....  || $var['key']...*/
        if ($node instanceof Node\Expr\Assign /* $var = ...*/
            || $node instanceof Node\Expr\ArrayDimFetch /* $var['key']...*/
        ) {
            if (isset($node->var) && ($node->var instanceof Node\Expr\Variable)) {
                $this->prepareNode($node->var);
            }
        } elseif ($node instanceof Node\Expr\MethodCall) {
            /* вызов метода из переменной $var->method_name(...) || $var->$name (...) */
            if (isset($node->var) && ($node->var instanceof Node\Expr\Variable)) {
                $this->prepareNode($node->var);
            }
            var_dump($node);
            if (isset($node->name) && ($node->name instanceof Node\Expr\Variable)) {
                $this->prepareNode($node->name);
            }
        } elseif ($node instanceof Node\Stmt\Function_) {
            /* Переменные-параметры функций funct(param1,param2...){...body...} */
            if (isset($node->params) && is_array($node->params)) {
                foreach ($node->params as $param) {
                    if ($param instanceof Node\Param) {
                        $this->prepareNode($param);
                    }
                }
            }
        } elseif ($node instanceof Node\Stmt\ClassMethod) {
            /* Переменные-параметры функций public funct(param1,param2...){...body...} */
            if (isset($node->params) && is_array($node->params)) {
                foreach ($node->params as $param) {
                    if ($param instanceof Node\Param) {
                        $this->prepareNode($param);
                    }
                }
            }

        } elseif ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            /* Переменные в условии IF  - if($name){  || ($name && $var) */
            if (isset($node->left) && ($node->left instanceof Node\Expr\Variable)) {
                $this->prepareNode($node->left);
            }
            if (isset($node->right) && ($node->right instanceof Node\Expr\Variable)) {
                $this->prepareNode($node->right);
            }
        } else if ($node instanceof Node\Arg) {
            /*Переменые - аргументы вызываемых функций  $v = funct(arg1,arg2...);*/
            if (isset($node->value) && ($node->value instanceof Node\Expr\Variable)) {
                $this->prepareNode($node->value);
            }
            /* Переменные цикла foreach (expr as keyVar => valueVar) */
        } else if ($node instanceof Node\Stmt\Foreach_) {
            foreach (['expr', 'keyVar', 'valueVar'] as $v) {
                if (isset($node->$v) && ($node->$v instanceof Node\Expr\Variable)) {
                    $this->prepareNode($node->$v);
                }
            }
        }
    }

    protected function prepareNode(Node $node)
    {
        $node->setAttribute('type', 'user_variable');
        $this->registry->set($node->name);
    }

    protected function replace(Node $node)
    {
        if ($node->getAttribute('type') == 'user_variable') {
            $node->name = $this->getCompressor()->getShortName($node->name) . '__NEW_NAME';
        }
    }
}