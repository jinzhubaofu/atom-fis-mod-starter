<?php

/**
 * @file php built-in webserver router
 * @author ludafa <leonlu@outlook.com>
 */

date_default_timezone_set("UTC");

require_once(__DIR__.'/atom.php');

$root = getcwd();

// 如果不是 *.php 那么直接返回；
if (!preg_match('/\.php($|\?)/', $_SERVER['REQUEST_URI'])) {
    return false;
}

$templatePath = substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 1);
$absoluteTemplatePath = "$root/output/$templatePath";

if (!file_exists($absoluteTemplatePath)) {
    echo "无法找到模板$templatePath，你可能是没进行构建";
    exit(0);
}

$componentPath = substr($templatePath, 0, -4) . '.atom.php';
$absoluteComponentPath = $root . '/output/' . $componentPath;

if (!file_exists($absoluteComponentPath)) {
    echo "无法找到组件" . $componentPath . " ，你可能是没进行构建";
    exit(1);
}

$mockData = getMockData($componentPath, $_SERVER['REQUEST_URI']);
$tplData = $mockData['tplData'];
$extData = $mockData['extData'];
$atom = renderAtom($absoluteComponentPath, $tplData);

include($absoluteTemplatePath);
