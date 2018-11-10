<?php

namespace Genasyst\phpCompressor;

use Genasyst\phpCompressor\PhpParser;
use Genasyst\phpCompressor\NamesRegistry;
use Genasyst\phpCompressor\PhpParser\Visitors\Traverser\GlobalVisitor;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Error;


class Compressor
{

    const COMPRESS_TYPE_LOCAL_VARIABLES = 'local_variables';
    const COMPRESS_TYPE_OBJECT_VARIABLES = 'object_variables';
    const COMPRESS_TYPE_OBJECT_METHODS = 'object_methods';
    
    protected $content = '';

    protected $whitespaces = false;

    protected $comments = false;

    protected $compress_types = [
        'local_variables'         => false,
        'object_variables'        => false,
        'object_methods'          => false,
    ];

    protected $exclude_names = [
        'local_variables'         => [
            'this'                 => 'this',/** use in Object method scope **/
            'GLOBALS'              => 'GLOBALS',
            '_GET'                 => '_GET',
            '_POST'                => '_POST',
            '_FILES'               => '_FILES',
            '_COOKIE'              => '_COOKIE',
            '_SESSION'             => '_SESSION',
            '_SERVER'              => '_SERVER',
            'http_response_header' => 'http_response_header',
            'php_errormsg'         => 'php_errormsg',
        ],
        'object_variables'        => [
            'this'                 => 'this',
        ],
        
        'object_methods'          =>  [
            //Magic
            'this'         => 'this',
            '__construct'  => '__construct',
            '__destruct'   => '__destruct',
            '__call'       => '__call',
            '__callStatic' => '__callStatic',
            '__get'        => '__get',
            '__set'        => '__set',
            '__isset'      => '__isset',
            '__unset'      => '__unset',
            '__sleep'      => '__sleep',
            '__wakeup'     => '__wakeup',
            '__toString'   => '__toString',
            '__invoke'     => '__invoke',
            '__set_state'  => '__set_state',
            '__clone'      => '__clone',
            '__debugInfo'  => '__debugInfo',
            // only php 7
            'do'           => 'do',
            'as'           => 'as',
        ]
    ];
    /**
     * @var NodeTraverser
     */
    protected $traverser = null;

    public function __construct($data = [])
    {

    }

    public function compress()
    { 
        try {
            $statements = $this->getStatements();
            $traverser = $this->getTraverser();
            $traverser->addVisitor(new GlobalVisitor($this->getCompressSettings()));
            $new_statements = $traverser->traverse($statements);
            $this->content = $this->getCompiller()->prettyPrint($new_statements);
        } catch (Error $e) {
            echo 'Parse Error: ', $e->getMessage();
        }
    }

    public function setExcludeNames($type = '', array $exclude_names) {
        if(isset($this->compress_types[$type])) {
            $this->exclude_names[$type] += $exclude_names;
        }
    }
    protected function getCompressSettings()
    {
        $settings = [];
        foreach ($this->compress_types as $type => $enable) {
            $settings[$type] = [
                'enable' => $enable,
                'exclude_names' => $this->exclude_names[$type]
            ];
        }
        return $settings;
    }
    public function getContent()
    {
        return $this->content;
    }

    public function getStatements($perfer = ParserFactory::PREFER_PHP5)
    {
        $parser = (new ParserFactory)->create($perfer);

        return $parser->parse($this->content);
    }


    /**
     * @return PrettyPrinter\Standard
     */
    protected function getCompiller()
    {
        return new PrettyPrinter\Standard;
    }

    protected function getTraverser()
    {
        return new \PhpParser\NodeTraverser();
    }

    public function removeWhitespaces()
    {
        $this->whitespaces = true;
    }

    public function removeComments()
    {
        $this->comments = true;
    }

    /**********************
     *   settings
     **********************/
    public function compressLocalVariablesName()
    {
        $this->compress_types['local_variables'] = true;
    }

    public function compressObjectsVariablesName()
    {
        $this->compress_types['object_variables'] = true;
    }

    public function compressObjectsMethodsName()
    {
        $this->compress_types['object_methods'] = true;
    }


    public function setContentByFile($file)
    {
        if (false === file_exists($file)) {
            throw new \Exception('Not found file '.$file);
        }
        return $this->content = file_get_contents($file);
    }

    public function setContentByCode($code)
    {
        $this->content = $code;
    }

    public function parseBlock($blockCode)
    {
        $this->content = '<?php ' . $blockCode;
    }


}