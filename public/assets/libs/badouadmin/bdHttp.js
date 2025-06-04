layui.define(['toast'], function (exports) {
    var Layer = layui.layer;
    var toast = layui.toast;
    var bdHttp = {
        config: {
            open: {
                area: null
            }
        },
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
                toast.success({ message: msg });
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

                toast.error({ message: ret.msg });
            },
            //服务器响应数据后
            onAjaxResponse: function (response) {
                try {
                    var ret = typeof response === 'object' ? response : JSON.parse(response);
                    if (!ret.hasOwnProperty('code')) {
                        $.extend(ret, { code: -2, msg: response, data: null });
                    }
                } catch (e) {
                    var ret = { code: -1, msg: e.message, data: null };
                }
                return ret;
            }
        },
        api: {
            //发送Ajax请求
            ajax: function (options, success, error) {
                options = typeof options === 'string' ? { url: options } : options;
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
                        ret = bdHttp.events.onAjaxResponse(ret);
                        if (ret.code === 1) {
                            bdHttp.events.onAjaxSuccess(ret, success);
                        } else {
                            bdHttp.events.onAjaxError(ret, error);
                        }
                    },
                    error: function (xhr) {
                        index && Layer.close(index);
                        var ret = { code: xhr.status, msg: xhr.statusText, data: null };
                        bdHttp.events.onAjaxError(ret, error);
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
            //打开一个弹出窗口
            open: function (url, title, options) {
                title = options && options.title ? options.title : (title ? title : "");
                url = bdHttp.api.fixurl(url);

                // url = url + (url.indexOf("?") > -1 ? "&" : "?") + "";
                var area = bdHttp.config.open.area ? bdHttp.config.open.area : [$(window).width() > 800 ? '800px' : '95%', $(window).height() > 600 ? '600px' : '95%'];
                var success = options && typeof options.success === 'function' ? options.success : $.noop;
                if (options && typeof options.success === 'function') {
                    delete options.success;
                }
                options = $.extend({
                    type: 2,
                    title: title,
                    shadeClose: true,
                    shade: false,
                    maxmin: true,
                    moveOut: true,
                    area: area,
                    content: url,
                    zIndex: Layer.zIndex,
                    success: function (layero, index) {
                        var that = this;
                        //存储callback事件
                        $(layero).data("callback", that.callback);
                        //$(layero).removeClass("layui-layer-border");
                        Layer.setTop(layero);
                        try {
                            var frame = Layer.getChildFrame('html', index);
                            var layerfooter = frame.find(".layer-footer");
                            bdHttp.api.layerfooter(layero, index, that);

                            //绑定事件
                            if (layerfooter.length > 0) {
                                // 监听窗口内的元素及属性变化
                                // Firefox和Chrome早期版本中带有前缀
                                var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
                                if (MutationObserver) {
                                    // 选择目标节点
                                    var target = layerfooter[0];
                                    // 创建观察者对象
                                    var observer = new MutationObserver(function (mutations) {
                                        bdHttp.api.layerfooter(layero, index, that);
                                        mutations.forEach(function (mutation) {
                                        });
                                    });
                                    // 配置观察选项:
                                    var config = { attributes: true, childList: true, characterData: true, subtree: true }
                                    // 传入目标节点和观察选项
                                    observer.observe(target, config);
                                    // 随后,你还可以停止观察
                                    // observer.disconnect();
                                }
                            }
                        } catch (e) {

                        }
                        if ($(layero).height() > $(window).height()) {
                            //当弹出窗口大于浏览器可视高度时,重定位
                            Layer.style(index, {
                                top: 0,
                                height: $(window).height()
                            });
                        }
                        success.call(this, layero, index);
                    }
                }, options ? options : {});
                if ($(window).width() < 480 || (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream)) {
                    options.area = [$(window).width() + "px", $(window).height() + "px"];
                    options.offset = ["0px", "0px"];
                }
                return Layer.open(options);
            },
            //关闭窗口并回传数据
            close: function (data) {
                var index = parent.layer.getFrameIndex(window.name);
                var callback = parent.$("#layui-layer" + index).data("callback");
                //再执行关闭
                parent.layer.close(index);
                //再调用回传函数
                if (typeof callback === 'function') {
                    callback.call(undefined, data);
                }
            },
            layerfooter: function (layero, index, that) {
                var frame = Layer.getChildFrame('html', index);
                var layerfooter = frame.find(".layer-footer");

                if (layerfooter.length > 0) {
                    $(".layui-layer-footer", layero).remove();
                    var footer = $("<div />").addClass('layui-layer-btn layui-layer-footer');
                    footer.html(layerfooter.html());
                    footer.insertAfter(layero.find('.layui-layer-content'));
                    //绑定事件
                    footer.on("click", ".layui-btn", function () {
                        if ($(this).hasClass("disabled") || $(this).parent().hasClass("disabled")) {
                            return;
                        }
                        var index = footer.find('.layui-btn').index(this);
                        $(".layui-btn:eq(" + index + ")", layerfooter).trigger("click");
                    });

                    var titHeight = layero.find('.layui-layer-title').outerHeight() || 0;
                    var btnHeight = layero.find('.layui-layer-btn').outerHeight() || 0;
                    //重设iframe高度
                    $("iframe", layero).height(layero.height() - titHeight - btnHeight);
                }
                //修复iOS下弹出窗口的高度和iOS下iframe无法滚动的BUG
                if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
                    var titHeight = layero.find('.layui-layer-title').outerHeight() || 0;
                    var btnHeight = layero.find('.layui-layer-btn').outerHeight() || 0;
                    $("iframe", layero).parent().css("height", layero.height() - titHeight - btnHeight);
                    $("iframe", layero).css("height", "100%");
                }
            },
            success: function (options, callback) {
                var type = typeof options === 'function';
                if (type) {
                    callback = options;
                }
                return toast.success({
                    message: __('Operation completed'),
                    offset: 0,
                    onClosing: callback
                });
            },
            error: function (options, callback) {
                var type = typeof options === 'function';
                if (type) {
                    callback = options;
                }
                return toast.error({
                    message: __('Operation failed'),
                    offset: 0,
                    onClosing: callback
                })
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
            },
            badouStorage: {
                // 统一的命名空间
                namespace: 'badouadmin',

                // 获取存储数据
                get: function (key) {
                    var data = layui.data(this.namespace);
                    return key ? data[key] : data;
                },

                // 设置存储数据
                set: function (namespace, data) {
                    if (!data) {
                        data = namespace;
                    } else {
                        this.namespace = namespace;
                    }
                    layui.data(this.namespace, data);
                },

                // 删除存储数据
                remove: function (key) {
                    layui.data(this.namespace, {
                        key: key,
                        remove: true
                    });
                },

                // 清空所有数据
                clear: function () {
                    layui.data(this.namespace, null);
                },

                // 获取或生成会话ID
                getSid: function () {
                    var sid = this.get('sid');
                    if (!sid) {
                        sid = Math.random().toString(20).substr(2, 12);
                        this.set({ key: 'sid', value: sid });
                    }
                    return sid;
                }
            }
        },
        init: function () {
            // jQuery兼容处理
            $.fn.extend({
                size: function () {
                    return $(this).length;
                }
            });
            // 对相对地址进行处理
            $.ajaxSetup({
                beforeSend: function (xhr, setting) {
                    setting.url = bdHttp.api.fixurl(setting.url);
                }
            });
            Layer.config({
                skin: 'layui-layer-badouadmin'
            });
            // 绑定ESC关闭窗口事件
            $(window).keyup(function (e) {
                if (e.keyCode == 27) {
                    if ($(".layui-layer").length > 0) {
                        var index = 0;
                        $(".layui-layer").each(function () {
                            index = Math.max(index, parseInt($(this).attr("times")));
                        });
                        if (index) {
                            Layer.close(index);
                        }
                    }
                }
            });
        }
    };
    bdHttp.init();
    exports('bdHttp', bdHttp);
});