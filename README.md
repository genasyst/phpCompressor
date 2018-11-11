phpCompressor
==========


phpCompressor - The main task of compressing PHP code, it is also possible to use as a simple PHP obfuscator. 
The compressor compresses the code by removing spaces, line breaks code comment,
abbreviations of names of local variables in functions, abbreviations of names of property classes (variables), 
abbreviations of names of class methods. 

Reduction settings
=====
* Reduction of local variable names in functions
* Reduction of names of properties of classes (variables)
* Reduction of the names of class methods
* To remove spaces, comments and line breaks

The principle of abbreviating names
===== 
Aliases are used to abbreviate names, which are formed depending on the frequency of use of the name.

For example: the name 'data' is used 98 times in the code, 'options' - 70 times, 'values' - 68 times, etc.on fading...
The result will be

* 'data'      => 'a'
* 'options'   => 'b'
* 'values'    => 'c'
* '...'       => 'd...aa'
* 'rare_name' => 'ab'
* .....



Installation
===== 

To install, use the composer  [composer](https://getcomposer.org):

    php composer.phar require genasyst/php-compressor
    
    
Example of use
=====

```php
<?php

$compressor = new \Genasyst\phpCompressor\Compressor();

$code = 'echo $data;..... ';
/* Setting the code with the opening tag <?php first */
$compressor->setContentByCode('<?php '.$code);

/ * Install code from php file */
$file_path = __DIR__ .'/ExampleTestEcho.php';
$compressor->setContentByFile($file_path);


/**
 * SETTINGS
 *
 * Set reduction of local variables
 */
$compressor->compressLocalVariablesName();

/**
 * Set the reduction of class properties
 */
$compressor->compressObjectsVariablesName();

/**
 * Setting abbreviations for method names
 */
$compressor->compressObjectsMethodsName();


/**
 * WITH THE EXCEPTION OF COMPRESSION NAMES
 *
 *
 * Set the exception of the names of local variables
 */
$compressor->setExcludeNames(
    \Genasyst\phpCompressor\Compressor::COMPRESS_TYPE_LOCAL_VARIABLES,
    ['not_compress_local' => 'not_compress_local']
);

/**
 * Set the exception of the names of object properties
 */
$compressor->setExcludeNames(
    \Genasyst\phpCompressor\Compressor::COMPRESS_TYPE_OBJECT_VARIABLES,
    ['not_compressed_name' => 'not_compressed_name']
);

/**
 * Set the exception of the names of the methods on the object
 */
$compressor->setExcludeNames(
    \Genasyst\phpCompressor\Compressor::COMPRESS_TYPE_OBJECT_METHODS,
    ['thisMethodNameNotCompressed' => 'thisMethodNameNotCompressed']
);


/* Set the code of a piece */
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
 * Start compression
 */
$compressor->compress();


/**
 * Get back the compressed code
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
 * echo test1($a->b());//SUPERLONGUPPER 
 * echo test1($a->thisMethodNameNotCompressed());//NOT_COMPRESSED  
*/

```



