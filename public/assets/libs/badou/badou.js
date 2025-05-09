layui.define(['lang','http','bdTable'],function(exports) {
        "use strict";
        var lang = layui.lang,
            http = layui.http,
            bdTable = layui.bdTable;

    var Badou = new function () {
        this.lang=lang;
        this.bdTable=bdTable;
        this.api=http.api;
        this.http=http;
    }
    exports('badou', Badou);
});