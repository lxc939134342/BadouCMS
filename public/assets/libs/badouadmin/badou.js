layui.define(['lang', 'bdHttp', 'bdTable', 'bdForm', 'bdUpload', 'bdTool'], function (exports) {
    "use strict";
    var lang = layui.lang,
        http = layui.bdHttp,
        bdTable = layui.bdTable,
        bdForm = layui.bdForm,
        bdUpload = layui.bdUpload;

    var Badou = new function () {
        this.lang = lang;
        this.bdTable = bdTable;
        this.http = http;
        this.api = http.api;
        this.bdForm = bdForm;
        this.bdUpload = bdUpload;
        this.tool = layui.bdTool;

        // 钩子系统
        this.hooks = {
            handlers: {},
            // 注册钩子
            add: function (name, callback) {
                if (!this.handlers[name]) this.handlers[name] = [];
                this.handlers[name].push(callback);
            },
            // 触发钩子
            run: function (name, params) {
                if (this.handlers[name]) {
                    this.handlers[name].forEach(function (callback) {
                        if (typeof callback === 'function') {
                            callback(params);
                        }
                    });
                }
                // 同时向整个文档广播，增加灵活性
                $(document).trigger('badou.hook.' + name, [params]);
            }
        };
    }
    exports('badou', Badou);
});