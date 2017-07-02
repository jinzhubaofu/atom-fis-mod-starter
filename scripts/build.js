/**
 * @file 构建脚本
 * @author leon <ludafa@outlook.com>
 */

/* eslint-disable fecs-no-require, no-console */

const fs = require('fs');
const atom = require('vip-server-renderer');
const path = require('path');
const rimraf = require('rimraf');
const mkdirp = require('mkdirp');

const outputPath = path.join(__dirname, '../output');

rimraf.sync(outputPath);
mkdirp.sync(outputPath);

function buildComponent(component, content) {

    let result = atom.compile({
        content: content,
        compilePHPComponent(val, key) {
            return `"./output/php/${val}"`;
        },
        compileJSComponent(val, key) {
            return `require("/output/static/js/${val}")`;
        },
        strip: true
    });

    let {js, php} = result.compiled;

    createFile(path.join(outputPath, `static/js/${component}.js`), js);

    createFile(path.join(outputPath, `php/${component}.php`), php);

    console.log(`组件 ${component} 编译成功`);
}

function createFile(filePath, content) {
    mkdirp.sync(path.dirname(filePath));
    fs.writeFileSync(filePath, content, 'utf-8');
}

buildComponent(
    'index.atom',
    fs.readFileSync(path.join(__dirname, '../src/index.atom'), 'utf8')
);
