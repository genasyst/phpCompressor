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

    protected $strip_whitespaces = false;

    protected $comments = false;

    protected $compress_types = [
        'local_variables'  => false,
        'object_variables' => false,
        'object_methods'   => false,
    ];

    protected $exclude_names = [
        'local_variables'  => [
            'this'                 => 'this', /** use in Object method scope **/
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
        'object_variables' => [
            'this' => 'this',
        ],

        'object_methods' => [
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
        ],
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
            if ($this->strip_whitespaces) {
                $this->content = $this->stripWhitespaces($this->content);
            }
        } catch (Error $e) {
            echo 'Parse Error: ', $e->getMessage();
        }
    }
 
    protected function stripWhitespaces($code)
    {
        static $IW = array(
            T_CONCAT_EQUAL,             // .=
            T_DOUBLE_ARROW,             // =>
            T_BOOLEAN_AND,              // &&
            T_BOOLEAN_OR,               // ||
            T_IS_EQUAL,                 // ==
            T_IS_NOT_EQUAL,             // != or <>
            T_IS_SMALLER_OR_EQUAL,      // <=
            T_IS_GREATER_OR_EQUAL,      // >=
            T_INC,                      // ++
            T_DEC,                      // --
            T_PLUS_EQUAL,               // +=
            T_MINUS_EQUAL,              // -=
            T_MUL_EQUAL,                // *=
            T_DIV_EQUAL,                // /=
            T_IS_IDENTICAL,             // ===
            T_IS_NOT_IDENTICAL,         // !==
            T_DOUBLE_COLON,             // ::
            T_PAAMAYIM_NEKUDOTAYIM,     // ::
            T_OBJECT_OPERATOR,          // ->
            T_DOLLAR_OPEN_CURLY_BRACES, // ${
            T_AND_EQUAL,                // &=
            T_MOD_EQUAL,                // %=
            T_XOR_EQUAL,                // ^=
            T_OR_EQUAL,                 // |=
            T_SL,                       // <<
            T_SR,                       // >>
            T_SL_EQUAL,                 // <<=
            T_SR_EQUAL,                 // >>=
        );
        $code = preg_replace('/\s+/is', " ", $code);
        $tokens = token_get_all($code);

        $new = "";
        $c = sizeof($tokens);
        $iw = false; // ignore whitespace
        $ih = false; // in HEREDOC
        $ls = "";    // last sign
        $ot = null;  // open tag
        for ($i = 0; $i < $c; $i++) {
            $token = $tokens[$i];
            if (is_array($token)) {
                list($tn, $ts) = $token; // tokens: number, string, line
                $tname = token_name($tn);
                if ($tn == T_INLINE_HTML) {
                    $new .= $ts;
                    $iw = false;
                } else {
                    if ($tn == T_OPEN_TAG) {
                        if (strpos($ts, " ") || strpos($ts, "\n") || strpos($ts, "\t") || strpos($ts, "\r")) {
                            $ts = rtrim($ts);
                        }
                        $ts .= " ";
                        $new .= $ts;
                        $ot = T_OPEN_TAG;
                        $iw = true;
                    } elseif ($tn == T_OPEN_TAG_WITH_ECHO) {
                        $new .= $ts;
                        $ot = T_OPEN_TAG_WITH_ECHO;
                        $iw = true;
                    } elseif ($tn == T_CLOSE_TAG) {
                        if ($ot == T_OPEN_TAG_WITH_ECHO) {
                            $new = rtrim($new, "; ");
                        } else {
                            $ts = " " . $ts;
                        }
                        $new .= $ts;
                        $ot = null;
                        $iw = false;
                    } elseif (in_array($tn, $IW)) {
                        $new .= $ts;
                        $iw = true;
                    } elseif ($tn == T_CONSTANT_ENCAPSED_STRING
                        || $tn == T_ENCAPSED_AND_WHITESPACE
                    ) {
                        if ($ts[0] == '"') {
                            $ts = addcslashes($ts, "\n\t\r");
                        }
                        $new .= $ts;
                        $iw = true;
                    } elseif ($tn == T_WHITESPACE) {
                        $nt = @$tokens[$i + 1];
                        if (!$iw && (!is_string($nt) || $nt == '$') && !in_array($nt[0], $IW)) {
                            $new .= " ";
                        }
                        $iw = false;
                    } elseif ($tn == T_START_HEREDOC) {
                        $new .= "<<<S\n";
                        $iw = false;
                        $ih = true; // in HEREDOC
                    } elseif ($tn == T_END_HEREDOC) {
                        $new .= "S;";
                        $iw = true;
                        $ih = false; // in HEREDOC
                        for ($j = $i + 1; $j < $c; $j++) {
                            if (is_string($tokens[$j]) && $tokens[$j] == ";") {
                                $i = $j;
                                break;
                            } else if ($tokens[$j][0] == T_CLOSE_TAG) {
                                break;
                            }
                        }
                    } elseif ($tn == T_COMMENT || $tn == T_DOC_COMMENT) {
                        $iw = true;
                    } else {
                        if (!$ih) {
                            //$ts = strtolower($ts);
                        }
                        $new .= $ts;
                        $iw = false;
                    }
                }
                $ls = "";
            } else {
                if (($token != ";" && $token != ":") || $ls != $token) {
                    $new .= $token;
                    $ls = $token;
                }
                $iw = true;
            }
        }

        return $new;
    }

    public function setExcludeNames($type = '', array $exclude_names)
    {
        if (isset($this->compress_types[$type])) {
            $this->exclude_names[$type] += $exclude_names;
        }
    }

    protected function getCompressSettings()
    {
        $settings = [];
        foreach ($this->compress_types as $type => $enable) {
            $settings[$type] = [
                'enable'        => $enable,
                'exclude_names' => $this->exclude_names[$type],
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

    public function compresWhitespaces()
    {
        $this->strip_whitespaces = true;
    }


    public function setContentByFile($file)
    {
        if (false === file_exists($file)) {
            throw new \Exception('Not found file ' . $file);
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