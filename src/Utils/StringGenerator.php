<?php

namespace Genasyst\phpCompressor\Utils;


class StringGenerator
{
    protected $chars =
        array(
            0  => 'a',
            1  => 'b',
            2  => 'c',
            3  => 'd',
            4  => 'e',
            5  => 'f',
            6  => 'g',
            7  => 'h',
            8  => 'i',
            9  => 'j',
            10 => 'k',
            11 => 'l',
            12 => 'm',
            13 => 'n',
            14 => 'o',
            15 => 'p',
            16 => 'q',
            17 => 'r',
            18 => 's',
            19 => 't',
            20 => 'u',
            21 => 'v',
            22 => 'w',
            23 => 'x',
            24 => 'y',
            25 => 'z',
            // 26 => '_',
        );
    protected $count = 0;
    protected $result = [];
    protected $quantity = 100;

    public function __construct($chars = null)
    {
        $this->chars = is_array($chars) ? array_values(array_unique($chars)) : $this->chars;
    }

    public function getStrings($quantity = 100)
    {
        $this->quantity = $quantity;
        $this->generate('');

        return $this->result;
    }

    function range($min, $max, $quantity = 100, $exclude = null, $result = null)
    {
        // echo 'qua ='. var_dump($quantity);
        // echo 'ex ='. var_dump($exclude);

        $pointer = strtolower($min);
        if (is_array($exclude)) {
            $exclude_names = $exclude;
        } else {
            $exclude_names = [];
        }
        $count = 0;

        if (!is_array($result)) {
            $result = [];
            $this->quantity = $quantity;
        } else {
            $count = count($result);
        }

        while ($this->positionalcomparison($pointer, strtolower($max)) <= 0 && $count < $quantity) {
            //if(!array_key_exists($pointer, $this->exclude_names)){
            $result[$pointer] = $pointer;
            $count++;
            $pointer++;
            //}

        }
        foreach ($exclude_names as $v) {
            if (array_key_exists($v, $result)) {
                unset($result[$v]);
            }
        }
        // if(in_array('do', $exclude)) {
        //var_dump($result);
        //  var_dump($quantity);
        // echo"\n\n";
        // var_dump($exclude);
        // }
        if (count($result) < $this->quantity) {
            return $this->range(end($result), $max, $quantity + 1, $exclude, $result);
        }

        return array_slice($result, 0, $this->quantity);
    }

    function positionalcomparison($a, $b)
    {
        $a1 = $this->stringtointvalue($a);
        $b1 = $this->stringtointvalue($b);
        if ($a1 > $b1) return 1;
        else if ($a1 < $b1) return -1;
        else return 0;
    }

    /*
    * e.g. A=1 - B=2 - Z=26 - AA=27 - CZ=104 - DA=105 - ZZ=702 - AAA=703
    */
    function stringtointvalue($str)
    {
        $amount = 0;
        $strarra = array_reverse(str_split($str));

        for ($i = 0; $i < strlen($str); $i++) {
            $amount += (ord($strarra[$i]) - 64) * pow(26, $i);
        }

        return $amount;
    }

    protected function generate($temp)
    {
        for ($j = 0; $j < count($this->chars); $j++) {
            if (!array_key_exists($j, $this->chars)) {
                continue;
            }
            if ($this->count == $this->quantity) {
                return;
            }
            $temp = $temp . $this->chars[$j];
            $this->result[] = $temp;
            $this->count++;
            $this->generate($temp);
        }
    }
}