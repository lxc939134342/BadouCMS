layui.define(['lang', 'http', 'bdTable', 'bdForm'], function (exports) {
    "use strict";
    var lang = layui.lang,
        http = layui.http,
        bdTable = layui.bdTable,
        bdForm = layui.bdForm;

    var Badou = new function () {
        this.lang = lang;
        this.bdTable = bdTable;
        this.api = http.api;
        this.http = http;
        this.bdForm = bdForm;
    }
    exports('badou', Badou);
});