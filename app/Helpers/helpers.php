<?php

use Carbon\Carbon;
use Hamcrest\Type\IsString;

/**
 * Return the formated date time
 */
if (!function_exists('formatDateTime')) {
    function format_date_time($datetime) {
        return Carbon::parse($datetime)->format('Y-m-d H:i:s');
    }
}

/**
 * Return the formated date
 */
if (!function_exists('formatDate')) {
    function format_date($datetime) {
        return Carbon::parse($datetime)->format('Y-m-d');
    }
}

function obfuscate($number)
{
    $offset = 973074643;
    $factor = 519737;
    return strtoupper(base_convert($factor * $number + $offset, 10, 36));
}

function deobfuscate($code)
{
    $offset = 973074643;
    $factor = 519737;
    return intdiv(base_convert($code, 36, 10) - $offset, $factor);
}

/**
 * obfuscate the json or array of numbers
 *
 * @param mix       $codes          Multiple numbers to obfuscate
 * @param boolean   $returnArray    Return type JSON string or array
 * @return mix                      JSON or array 
 */
function obfuscate_multiple($codes, $returnArray = false)
{
    $string         = false;

    // Check its json
    if(is_string($codes))
    {
        $codes      = json_decode($codes);
        $string     = true;
    }

    $numbers        = array();

    if(!empty($codes))
    {
        foreach($codes as $code)
        {
            $numbers[]  = obfuscate($code);
        }
    }
    
    return $returnArray ? $numbers : ($string ? json_encode($numbers) : $numbers);
}

/**
 * Deobfuscate the json or array of codes
 *
 * @param mix       $codes          Multiple codes to Deobfuscate
 * @param boolean   $returnArray    Return type JSON string or array
 * @return mix                      JSON or array 
 */
function deobfuscate_multiple($codes, $returnArray = false)
{
    $string         = false;

    // Check its json
    if(is_string($codes))
    {
        $codes      = json_decode($codes);
        $string     = true;
    }

    $numbers        = array();

    foreach($codes as $code)
    {
        $numbers[]  = deobfuscate($code);
    }
    
    return $returnArray ? $numbers : ($string ? json_encode($numbers) : $numbers);
}
?>