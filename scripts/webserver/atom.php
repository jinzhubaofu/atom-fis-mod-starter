<?php

require_once(__DIR__ . "/../../node_modules/vip-server-renderer/php/server/Atom.class.php");

function renderAtom($componentPath, $data) {

    // 新建实例
    $atom = new Atom();

    $vnode = $atom->renderVNode($componentPath, $data);

    // 为支持前端渲染，增加data-server-rendered属性
    $vnode->setAttribute('data-server-rendered', 'true');
    $vnode->setAttribute('atom-root');

    // 渲染结果
    $output = $atom->renderHtml($componentPath);

    return $output;

}

function getMockData($componentPath) {
    $root = getcwd();
    $data = exec("node $root/scripts/webserver/get-mock-data.js $root/$componentPath");
    $data = json_decode($data, true);
    return $data;
}