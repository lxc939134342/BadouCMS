layui.define(function(exports) {
    var Layer = layui.layer;
    var Http = {
        events: {
            //请求成功的回调
            onAjaxSuccess: function (ret, onAjaxSuccess) {
                var data = typeof ret.data !== 'undefined' ? ret.data : null;
                var msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : __('Operation completed');

                if (typeof onAjaxSuccess === 'function') {
                    var result = onAjaxSuccess.call(this, data, ret);
                    if (result === false)
                        return;
                }
                layer.msg(msg);
            },
            //请求错误的回调
            onAjaxError: function (ret, onAjaxError) {
                var data = typeof ret.data !== 'undefined' ? ret.data : null;
                if (typeof onAjaxError === 'function') {
                    var result = onAjaxError.call(this, data, ret);
                    if (result === false) {
                        return;
                    }
                }
                layer.msg(ret.msg);
            },
            //服务器响应数据后
            onAjaxResponse: function (response) {
                try {
                    var ret = typeof response === 'object' ? response : JSON.parse(response);
                    if (!ret.hasOwnProperty('code')) {
                        $.extend(ret, {code: -2, msg: response, data: null});
                    }
                } catch (e) {
                    var ret = {code: -1, msg: e.message, data: null};
                }
                return ret;
            }
        },
        api: {
            //发送Ajax请求
            ajax: function (options, success, error) {
                options = typeof options === 'string' ? {url: options} : options;
                var index;
                if (typeof options.loading === 'undefined' || options.loading) {
                    index = Layer.load(options.loading || 0);
                }
                options = $.extend({
                    type: "POST",
                    dataType: "json",
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (ret) {
                        index && Layer.close(index);
                        ret = Http.events.onAjaxResponse(ret);
                        if (ret.code === 1) {
                            Http.events.onAjaxSuccess(ret, success);
                        } else {
                            Http.events.onAjaxError(ret, error);
                        }
                    },
                    error: function (xhr) {
                        index && Layer.close(index);
                        var ret = {code: xhr.status, msg: xhr.statusText, data: null};
                        Http.events.onAjaxError(ret, error);
                    }
                }, options);
                return $.ajax(options);
            },
            //修复URL
            fixurl: function (url) {
                if (url.substr(0, 1) !== "/") {
                    var r = new RegExp('^(?:[a-z]+:)?//', 'i');
                    if (!r.test(url)) {
                        url = Config.moduleurl + "/" + url;
                    }
                } else if (url.substr(0, 8) === "/addons/") {
                    url = Config.__PUBLIC__.replace(/(\/*$)/g, "") + url;
                }
                return url;
            },
            //获取修复后可访问的cdn链接
            cdnurl: function (url, domain) {
                var rule = new RegExp("^((?:[a-z]+:)?\\/\\/|data:image\\/)", "i");
                var cdnurl = Config.upload.cdnurl;
                if (typeof domain === 'undefined' || domain === true || cdnurl.indexOf("/") === 0) {
                    url = rule.test(url) || (cdnurl && url.indexOf(cdnurl) === 0) ? url : cdnurl + url;
                }
                if (domain && !rule.test(url)) {
                    domain = typeof domain === 'string' ? domain : location.origin;
                    url = domain + url;
                }
                return url;
            },
            //查询Url参数
            query: function (name, url) {
                if (!url) {
                    url = window.location.href;
                }
                if (!name)
                    return '';
                name = name.replace(/[\[\]]/g, "\\$&");
                var regex = new RegExp("[?&/]" + name + "([=/]([^&#/?]*)|&|#|$)"),
                    results = regex.exec(url);
                if (!results)
                    return null;
                if (!results[2])
                    return '';
                return decodeURIComponent(results[2].replace(/\+/g, " "));
            },
            success: function (options, callback) {
                var type = typeof options === 'function';
                if (type) {
                    callback = options;
                }
                return Layer.msg(__('Operation completed'), $.extend({
                    offset: 0, icon: 1
                }, type ? {} : options), callback);
            },
            error: function (options, callback) {
                var type = typeof options === 'function';
                if (type) {
                    callback = options;
                }
                return Layer.msg(__('Operation failed'), $.extend({
                    offset: 0, icon: 2
                }, type ? {} : options), callback);
            },
            msg: function (message, url) {
                var callback = typeof url === 'function' ? url : function () {
                    if (typeof url !== 'undefined' && url) {
                        location.href = url;
                    }
                };
                Layer.msg(message, {
                    time: 2000
                }, callback);
            },
            escape: function (text) {
                if (typeof text === 'string') {
                    return text
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;')
                        .replace(/`/g, '&#x60;');
                }
                return text;
            }
        }
    };
    exports('http', Http);
});