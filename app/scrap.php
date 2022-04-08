<?php
namespace App;
use Sunra\PhpSimple\HtmlDomParser;

$url = "https://play.google.com/store/books";
$html = file_get_contents($url);
$parser = HtmlDomParser::class;

$dom = $parser::str_get_html( $parser );
$test = $dom->find(".sv0AUd .bs3Xnd");
var_dump($test);