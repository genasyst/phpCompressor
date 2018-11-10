<?php

namespace Genasyst\phpCompressor\NamesRegistry;

class LocalVariables extends NamesRegistry
{
    protected static $instance;
    
    protected $exclude_names = [
        'this'                 => 'this',
        'GLOBALS'              => 'GLOBALS',
        '_GET'                 => '_GET',
        '_POST'                => '_POST',
        '_FILES'               => '_FILES',
        '_COOKIE'              => '_COOKIE',
        '_SESSION'             => '_SESSION',
        '_SERVER'              => '_SERVER',
        'http_response_header' => 'http_response_header',
        'php_errormsg'         => 'php_errormsg',
    ];


}