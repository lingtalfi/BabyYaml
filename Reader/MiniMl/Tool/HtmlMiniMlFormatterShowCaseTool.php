<?php


namespace Ling\BabyYaml\Reader\MiniMl\Tool;

use Ling\BabyYaml\Reader\MiniMl\Formatter\HtmlMiniMlFormatter;


/**
 * HtmlMiniMlFormatterShowCaseTool
 * @author Lingtalfi
 * 2015-05-21
 *
 */
class HtmlMiniMlFormatterShowCaseTool
{

    public static function labelsShowCase($string = "Doo, really?")
    {

        $s = '';
        $s .= "<emergency>emergency: $string</emergency>\n";
        $s .= "<alert>alert: $string</alert>\n";
        $s .= "<critical>critical: $string</critical>\n";
        $s .= "<error>error: $string</error>\n";
        $s .= "<warning>warning: $string</warning>\n";
        $s .= "<notice>notice: $string</notice>\n";
        $s .= "<info>info: $string</info>\n";
        $s .= "<debug>debug: $string</debug>\n";
        $s .= "<success>success: $string</success>\n";

        return HtmlMiniMlFormatter::create()->format($s);
    }
}
