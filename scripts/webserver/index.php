<?php

/**
 * @file php built-in webserver router
 * @author ludafa <leonlu@outlook.com>
 */

date_default_timezone_set("UTC");

$root = getcwd();

require_once("$root/node_modules/vip-server-renderer/php/server/Atom.class.php");
require_once("$root/scripts/webserver/smarty/Smarty.class.php");

$selector = 'atom-root';

function route($root, $request) {

    // 如果不是 *.atom.php 那么直接返回；
    if (!preg_match('/\.atom\.php$/', $request)) {
        return false;
    }

    $componentPath = substr($request, 1);
    $realPath = "$root/output/$componentPath";

    if (!file_exists($realPath)) {
        echo "$componentPath 不存在，可能你忘了 npm run build, $realPath";
        exit(0);
    }

    return $componentPath;

}

function renderAtom($root, $componentPath, $data) {

    // 新建实例
    $atom = new Atom();

    $atom->addComponentDir("$root/output");
    $vnode = $atom->renderVNode($componentPath, $data);

    // 为支持前端渲染，增加data-server-rendered属性
    $vnode->setAttribute('data-server-rendered', 'true');
    $vnode->setAttribute('atom-root');

    // 渲染结果
    $output = $atom->renderHtml($componentPath);


    return $output;

}

function getMockData($root, $componentPath) {
    $data = exec("node $root/scripts/webserver/get-mock-data.js $root/$componentPath");
    $data = json_decode($data, true);
    return $data;
}

$componentPath = route($root, $_SERVER['REQUEST_URI']);

if (empty($componentPath)) {
    return false;
}

$tplData = getMockData($root, $componentPath);

$atom = renderAtom($root, $componentPath, $tplData);

$feRoot = 'http://' . $_SERVER['HTTP_HOST'];

$templatePath = substr($componentPath, 0, -9).'.php';

if (is_file($templatePath)) {
    include($templatePath);
    exit(0);
}

?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title><?php echo $tplData['title']?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
    <?php
    foreach ($atom['css'] as $style) {
        echo "$style\n";
    }
    ?>
    </style>
</head>
<body>
<?php echo $atom['html']?>
<script src="<?php echo $feRoot?>/static/mod.js"></script>
<!--RESOURCEMAP_PLACEHOLDER-->
<!--SCRIPT_PLACEHOLDER-->
<script>
require(['vip-server-renderer/js/atom', '<?php echo $componentPath ?>'], function (Atom, App) {
    new Atom({
        el: '[atom-root]',
        data: <?php echo json_encode($tplData)?>,
        render: function (createElement) {
            return createElement('App', {
                props: {
                    <?php
                    foreach ($atom['props'] as $index => $prop) {
                        $propName = json_encode($prop);
                        $comma = $index === count($atom['props']) - 1 ? '' : ',';
                        echo "$propName: this[$propName]$comma";
                    }
                    ?>
                }
            });
        },
        components: {
            App: App
        }
    });
});
</script>
</body>
</html>
