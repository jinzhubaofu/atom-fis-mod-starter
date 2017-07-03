# atom-fis3-starter

使用 atom 和 fis3 来创建网站应用的种子项目

## 上手指南

### 前置条件

1. NodeJS(>=6.0)
2. php(>=5.4)

### Setup

1. 下载 .zip 包
2. 解压 zip 包
3. cd atom-fis3-starter
4. npm install

### 启动开发

npm run watch
open http://localhost:9000/src/Todo/index.php

### 构建

npm run build

## 开发指南

### 目录结构

```text
├── README.md
├── docs                       // 所有的文档应该放到这里
│   └── ide.md
├── fis-conf.js                // fis3 构建的配置文件
├── package.json               
├── scripts                    // 所有的本地脚本在这个目录
│   ├── build.sh               // 构建用的 shell
│   └── webserver              // 本地开发用的服务器在这里
│       ├── atom.php           // 渲染 atom php 文件用的脚本
│       ├── get-mock-data.js   // 用于生成 mock 数据
│       └── index.php          // 本地开发服务器的入口 php 文件
├── src                        // 所有的源码在这里
│   ├── Todo                   // 建议每个页面都用一个目录来承载
│   │   ├── index.php          // 入口模板[关键]
│   │   ├── index.atom         // 入口 atom 组件[关键]
│   │   ├── index.mock.js      // mock 数据文件
│   │   └── List.atom          
│   └── common                 // 在多个页面中公共使用的模块
│       └── component          // 公共组件
│           └── Layout.atom    // 布局组件
└── static                     // 不需要进行模块化处理的 js
    └── mod.js                 // 一般我们都使用 npm 包的方式来引入开源库，不需要在这里添加；
                               // 由于 mod.js 是我们的加载器，所以就在这里特殊处理。
```


### 如何做预渲染

1. atom 提供了 `Server Side Renderer` 功能，我们在后端需要使用我们提供的入口模板
1. 我们移除了对 `smarty` 模板的依赖，现在只使用纯 php 来做渲染模板。
1. 我们给后端提供的是`即食`模板，即在准备好必需的数据后，直接 `include` 指定的入口 php 即可。

#### 步骤

1. 安装 vip-server-renderer 的 php 渲染库

    在下载好 atom 的 php 库之后，在合适的位置引入：

    ```php
    require_once(__DIR__ . "/path/to/vip-server-renderer/php/server/Atom.class.php");
    ```

1. 准备业务数据

    建议所有业务数据都封装进 `tplData`，所有的统计相关数据封装进 `extData`；

1. 根据请求，路由到正确的 atom 组件和入口模板

    我们提供的 atom 组件一般是**页面目录**下`index.atom.php`，模板是**页面目录**下的 `index.php`；

1. 渲染 atom 组件

    调用下边这个函数即可

    ```php
    function renderAtom($componentPath, $tplData) {

        // 新建实例
        $atom = new Atom();

        // 渲染 vnode
        $vnode = $atom->renderVNode($componentPath, $tplData);

        // 为支持前端渲染，增加data-server-rendered属性
        $vnode->setAttribute('data-server-rendered', 'true');

        // 添加根结点标识
        $vnode->setAttribute('atom-root');

        // 渲染结果
        return $atom->renderHtml($componentPath);

    }
    ```

1. 渲染模板

    除了 atom 组件提供的主要页面内容之外，我们还需要在外层包裹一些基础的 html，诸如 title / link / script 等等；

    在我们的构建产物中每个页面目录下的 `index.php` 就是这个页面的模板。在准备好数据之后，直接引入它即可。

    必需的数据:

        |名称|类型|必须|描述|
        |---|---|---|---|
        |tplData|object|必须|所有页面中的业务数据|
        |extData|object|非必须|需要用到的统计相关数据|
        |atom|object|必须|atom预渲染的结果数据|
        |atom.html|string|必须|atom预渲染输出的html|
        |atom.css|string|非必须|atom预渲染输出的css|

    使用示例：

    ```php
    $tplData = getMockData($componentPath);
    $atom = renderAtom($absoluteComponentPath, $tplData);
    include($absoluteTemplatePath);
    ```


### atom 的根结点

我们使用固定的 HTML 属性 `[atom-root]` 来标识 atom 的组件的根挂载元素。

这个是在 `scripts/webserver/atom.php` 和 `src/Todo/index.php` 中两处配合起来完成的。

**不建议在一个页面中使用多个 atom 根结点**

原因中在同一个页面中的多个 atom 根结点之间的数据和消息交互是互相隔离的；如果不能保证根结点之间没有关联，不要搞出多个根结点。

### atom 的布局组件

在我们有了 atom 组件之后，我们通过一个 `layout` 组件来完成布局，举个例子：

`layout.atom`:

```vue
<template>
    <main>
        <section>
            <slot name="main" />
        </section>
        <aside>
            <slot name="aside" />
        </aside>
</template>
```

在我们的入口 atom 组件中使用它：

```vue
<template>
    <div>
        <app-layout>
            <div slot="main">
                <h4 class="title">hello, {{name}}；我是 index.atom。</h4>
                <todo-list :list="myList" @addLike="addLike" />
            </div>
            <div slot="aside">
                这里是侧边栏
            </div>
        </app-layout>
    </div>
</template>
```

### mock 数据

你可以在页面目录下放置一个 `index.mock.js` 文件来生成 mock 数据。

在这个 js 文件中，你可以直接返回数据：

```js
module.exports = {
    tplData: {
        // 业务数据
    },
    extData: {
        // 统计数据
    }
};
```

我们会上边这个数据来渲染页面。或者是返回一个函数，我们在渲染前会调用它。它可以返回一个 promise，来进行异步操作：

```js
const fetch = require('node-fetch');
const fs = require('fs');
module.exports = function (request) {
    return fs.existsSync('my-local-mock-data.json')
        ? require('my-local-mock-data.json')
        : fetch('http://remote-mock-server.com', {method: 'GET'})
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error(response.status);
            });
};
```
其中，如果返回的是个函数，那么它还可以拿到当前请求的 url；可以用来做一些更灵活的 mock 处理。
