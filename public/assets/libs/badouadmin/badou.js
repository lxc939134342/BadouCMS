layui.define(['lang', 'bdHttp', 'bdTable', 'bdForm'], function (exports) {
    "use strict";
    var lang = layui.lang,
        http = layui.bdHttp,
        bdTable = layui.bdTable,
        bdForm = layui.bdForm;
    // var jstree = layui.jstree;

    console.log();

    var Badou = new function () {
        this.lang = lang;
        this.bdTable = bdTable;
        this.http = http;
        this.api = http.api;
        this.bdForm = bdForm;
        // this.jstree = jstree;
    }
    exports('badou', Badou);
});