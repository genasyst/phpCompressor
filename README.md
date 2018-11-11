phpCompressor
==========


phpCompressor - Основная задача сжатие кода PHP, также возможно использовать в качесте простого обфускатора PHP. 
Компрессор сжимает код за счет удаления пробелов, переносов строк коментарием кода,
сокращения названий локальных переменных в функциях, сокращения названий свойст классов (переменных), 
сокращения названий методов классов. 

Настройки сокращения
=====

* Сокращение названий локальных переменных в функциях
* Сокращение названий свойст классов (переменных)
* Сокращение названий методов классов
* Удаление пробелов коментариев и переносов строк

Принцип сокращения названий
=====
Для сокращения названий используются алиасы, которые формируются в зависимости от частоты использования названия.

Например: название 'data' используется 98 раз в коде, 'options' - 70раз, 'values' - 68 раз и т.д. по угасающей...
Результат будет таким

* 'data'      => 'a'
* 'options'   => 'b'
* 'values'    => 'c'
* '...'       => 'd...aa'
* 'rare_name' => 'ab'
* .....



Установка
=====

Для установки воспользуйтесь композером  [composer](https://getcomposer.org):

    php composer.phar require genasyst/php-compressor
    
    
Примеры использования
=====

```php
<?php

$compressor = new \Genasyst\phpCompressor\Compressor();

$code = 'echo $data;..... ';
/* Установка кода с открывающим тегом <?php вначале */
$compressor->setContentByCode('<?php '.$code);

/* Установка кода из php файла */
$file_path = __DIR__ .'/ExampleTestEcho.php';
$compressor->setContentByFile($file_path);


/**
 * НАСТРОЙКИ
 *
 * Устанавливаем сокращение локальных переменных
 */
$compressor->compressLocalVariablesName();

/**
 * Устанавливаем сокращение свойств класса
 */
$compressor->compressObjectsVariablesName();

/**
 * Устанавливаем сокращение названий методов
 */
$compressor->compressObjectsMethodsName();


/**
 * ИСКЛЮЧЕНИЕ СЖАТИЯ НАЗВАНИЙ
 *
 *
 * Устанавливаем исключение названий  свойств объекта
 */
$compressor->setExcludeNames(
    \Genasyst\phpCompressor\Compressor::COMPRESS_TYPE_LOCAL_VARIABLES,
    ['not_compress_local' => 'not_compress_local']
);

/**
 * Устанавливаем исключение названий  свойств объекта
 */
$compressor->setExcludeNames(
    \Genasyst\phpCompressor\Compressor::COMPRESS_TYPE_OBJECT_VARIABLES,
    ['not_compressed_name' => 'not_compressed_name']
);

/**
 * Устанавливаем исключение названий методов объекта
 */
$compressor->setExcludeNames(
    \Genasyst\phpCompressor\Compressor::COMPRESS_TYPE_OBJECT_METHODS,
    ['thisMethodNameNotCompressed' => 'thisMethodNameNotCompressed']
);


/* Установка кода из куска */
$code = <<<CODE
function test1(\$long_name1,  \$not_compress_local = ' ++') {
    \$long_name1 = strtolower(\$long_name1);
    if(strlen(\$long_name1) > 10) {
        return strtoupper(\$long_name1);
    }
    return ucfirst(\$long_name1).\$not_compress_local;
}
class My {

    protected \$long_variable = '';

    protected \$super_long_variable = '';

    protected \$not_compressed_name = '';

    public function __construct(\$long_variable, \$super_long_variable, \$not_compressed_name)
    {
        \$this->long_variable = \$long_variable;
        \$this->super_long_variable = \$super_long_variable;
        \$this->not_compressed_name = \$not_compressed_name;
    }

    public function getSuperLongVariable()
    {
        return \$this->super_long_variable;
    }

    public function getLongVariable()
    {
        return   \$this->long_variable;
    }

    public function thisMethodNameNotCompressed()
    {
        return  \$this->not_compressed_name;
    }
}

\$my = new My('lONg','SuperLongUpper','NOT_compressed');

echo test1(\$my->getLongVariable());//Long ++
echo test1(\$my->getSuperLongVariable());//SUPERLONGUPPER ++
echo test1(\$my->thisMethodNameNotCompressed());//NOT_COMPRESSED  ++
CODE;
$compressor->parseBlock($code);


/**
 * Запускаем сжатие
 */
$compressor->compress();


/**
 * Получаем обратно сжатый код
 */
$code = $compressor->getContent();
echo $code;
/**
 * RESULT
 * function test1($a, $not_compress_local = ' ++')
 * {
 *     $a = strtolower($a);
 *     if (strlen($a) > 10) {
 *         return strtoupper($a);
 *     }
 *     return ucfirst($a) . $not_compress_local;
 * }
 * class My
 * {
 *     protected $b = '';
 *     protected $a = '';
 *     protected $not_compressed_name = '';
 *     public function __construct($c, $b, $a)
 *     {
 *         $this->b = $c;
 *         $this->a = $b;
 *         $this->not_compressed_name = $a;
 *     }
 *     function b()
 *     {
 *         return $this->a;
 *     }
 *     function a()
 *     {
 *         return $this->b;
 *     }
 *     public function thisMethodNameNotCompressed()
 *     {
 *         return $this->not_compressed_name;
 *     }
 * }
 * $a = new My('lONg', 'SuperLongUpper', 'NOT_compressed');
 * echo test1($a->a()); //Long ++
 * echo test1($a->b());//SUPERLONGUPPER ++
 * echo test1($a->thisMethodNameNotCompressed());//NOT_COMPRESSED  ++
*/

```



