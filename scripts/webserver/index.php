<?php

/**
 * @file php built-in webserver router
 * @author ludafa <leonlu@outlook.com>
 */

date_default_timezone_set("UTC");

require_once(__DIR__.'/atom.php');
require_once(__DIR__.'/Resource.class.php');

$root = getcwd();

// 如果不是 *.php 那么直接返回；
if (!preg_match('/\.php$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

$templatePath = substr($_SERVER['REQUEST_URI'], 1);
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

$tplData = getMockData($componentPath);
$atom = renderAtom($absoluteComponentPath, $tplData);
$feRoot = 'http://' . $_SERVER['HTTP_HOST'];

FISResource::setConfig(array(
    'config_dir'    => $root . '/output/config/',
    'template_dir'  => $root . '/output/'
));

display($templatePath, array(
    'atom' => $atom,
    'tplData' => $tplData,
    'feRoot' => $feRoot,
));
