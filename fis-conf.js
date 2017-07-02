/**
 * @file fis config
 * @author leon <ludafa@outlook.com>
 */


fis.set('project.fileType.text', 'atom');
fis.set('project.files', ['**.tpl', 'mod.js', '!debug.tpl', '!templates_c', '!output']);

fis.hook('commonjs', {
    extList: ['.js', '.atom']
});

// src 为项目目录
fis.match('/{node_modules, src}/**.js', {
    isMod: true,
    useSameNameRequire: true
});

fis.match('(**)/(*).atom', {
    isMod: true,
    // fis3 不支持多重后缀，即不支持 .atom.js；
    rExt: 'js',
    useSameNameRequire: true,
    // 这里极为关键，不加 isJsLike 就不把我们当 js 处理了。
    isJsLike: true,
    // 输出为 commonjs 模块
    parser: fis.plugin('atom', {mode: 'commonjs'}),
    // 由于上边不支持多重后缀，所以我们这里 release 的时候加上后缀
    release: '$1/$2.atom.js'
});

// 用 loader 来自动引入资源。
fis.match('::package', {
    postpackager: fis.plugin('loader', {
        useInlineMap: true,
        resourceType: 'mod',
        processor: {
            '.tpl': 'tpl'
        }
    })
});

// 不处理 src/common/static 下的静态库
fis.match('/static/**.js', {
    isMod: false
});

// 禁用components
fis.unhook('components');
fis.hook('node_modules', {
    shimProcess: false,
    shimGlobal: false,
    shimBuffer: false
});
