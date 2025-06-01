
layui.define(['jquery', 'bdHttp', 'form', 'iconPicker', 'toast', 'bdUpload'], function (exports) {
    var $ = layui.jquery;
    var http = layui.bdHttp;
    var form = layui.form;
    var iconPicker = layui.iconPicker;
    var toast = layui.toast;
    var bdUpload = layui.bdUpload;
    var bdForm = {
        events: {
            //绑定事件
            bindevent: function (layform) {
                //选择图标
                if ($('.icon-select').length > 0) {
                    $(".icon-select").each(function (i, j) {
                        var that = this;
                        iconPicker.render({
                            elem: this,
                            type: 'fontClass',
                            search: true,
                            page: true,
                            limit: 16,
                            click: function (data) {
                                $(that).val(data.class);
                            },
                        });
                    });
                }
                //绑定上传组件
                bdUpload.render()
                //绑定选择附件事件
                bdForm.events.bdchoosefile(layform);
            },
            //表单校验事件
            validator: function (layform, success, error, submit) {
                if (!layform.is("form"))
                    return;
                var submitBtn = $("[lay-submit]", layform),
                    filter = submitBtn.attr('lay-filter');

                $(".layer-footer [lay-submit],.fixed-footer [lay-submit],.normal-footer [lay-submit]", layform).removeClass("disabled");
                //验证通过提交表单
                form.on('submit(' + filter + ')', function (data) {
                    submitBtn.addClass("disabled");

                    var submitResult = bdForm.api.submit(layform, function (data, ret) {
                        submitBtn.removeClass("disabled");
                        if (false === $(this).triggerHandler("success.form", [data, ret])) {
                            return false;
                        }
                        if (typeof success === 'function') {
                            if (false === success.call($(this), data, ret)) {
                                return false;
                            }
                        }
                        //提示及关闭当前窗口
                        var msg = ret.hasOwnProperty("msg") && ret.msg !== "" ? ret.msg : __('Operation completed');

                        parent.layui.toast.success({ message: msg })
                        parent.$(".btn-refresh").trigger("click");

                        if (window.name) {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                        }
                        return false;
                    }, function (data, ret) {
                        if (false === $(this).triggerHandler("error.form", [data, ret])) {
                            return false;
                        }
                        submitBtn.removeClass("disabled");
                        if (typeof error === 'function') {
                            if (false === error.call($(this), data, ret)) {
                                return false;
                            }
                        }
                    }, submit);
                    //如果提交失败则释放锁定
                    if (!submitResult) {
                        submitBtn.removeClass("disabled");
                    }
                    return false;
                })

                //自定义关闭按钮事件
                layform.on("click", ".layer-close", function () {
                    if (window.name) {
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    }
                    return false;
                });
            },
            //绑定选择附件事件
            bdchoosefile: function (form) {
                if ($(".btn-bdchoose", form).length > 0) {
                    $(".btn-bdchoose", form).off('click').on('click', function () {
                        var that = this;
                        var multiple = $(this).data("multiple") ? $(this).data("multiple") : false;
                        var mimetype = $(this).data("mimetype") ? $(this).data("mimetype") : '';
                        var admin_id = $(this).data("admin-id") ? $(this).data("admin-id") : '';
                        var user_id = $(this).data("user-id") ? $(this).data("user-id") : '';
                        mimetype = mimetype.replace(/\/\*/ig, '/');
                        var url = $(this).data("url") ? $(this).data("url") : "general.attachment/select";

                        parent.layui.badou.api.open(url + "?element_id=" + $(this).attr("id") + "&multiple=" + multiple + "&mimetype=" + mimetype + "&admin_id=" + admin_id + "&user_id=" + user_id, __('Choose'), {
                            callback: function (data) {
                                var button = $(that);
                                var maxcount = $(button).data("maxcount");
                                var input_id = $(button).data("input-id") ? $(button).data("input-id") : "";
                                maxcount = typeof maxcount !== "undefined" ? maxcount : 0;
                                if (input_id && data.multiple) {
                                    var urlArr = [];
                                    var inputObj = $("#" + input_id);
                                    var value = $.trim(inputObj.val());
                                    if (value !== "") {
                                        urlArr.push(inputObj.val());
                                    }
                                    var nums = value === '' ? 0 : value.split(/\,/).length;
                                    var files = data.url !== "" ? data.url.split(/\,/) : [];
                                    $.each(files, function (i, j) {
                                        var url = Config.upload.fullmode ? http.api.cdnurl(j) : j;
                                        urlArr.push(url);
                                    });
                                    if (maxcount > 0) {
                                        var remains = maxcount - nums;
                                        if (files.length > remains) {
                                            toast.error(__('You can choose up to %d file%s', remains));
                                            return false;
                                        }
                                    }
                                    var result = urlArr.join(",");
                                    inputObj.val(result).trigger("change").trigger("validate");
                                } else if (input_id) {
                                    var url = Config.upload.fullmode ? http.api.cdnurl(data.url) : data.url;
                                    $("#" + input_id).val(url).trigger("change").trigger("validate");
                                }

                            }
                        });
                        return false;
                    });
                }
            },
        },
        api: {
            submit: function (form, success, error, submit) {
                if (form.length === 0) {
                    toast.error("表单未初始化完成,无法提交");
                    return false;
                }
                if (typeof submit === 'function') {
                    if (false === submit.call(form, success, error)) {
                        return false;
                    }
                }
                var type = form.attr("method") ? form.attr("method").toUpperCase() : 'POST';
                type = type && (type === 'GET' || type === 'POST') ? type : 'POST';
                url = form.attr("action");
                url = url ? url : location.href;
                //修复当存在多选项元素时提交的BUG
                var params = {};
                var multipleList = $("[name$='[]']", form);
                if (multipleList.length > 0) {
                    var postFields = form.serializeArray().map(function (obj) {
                        return $(obj).prop("name");
                    });
                    $.each(multipleList, function (i, j) {
                        if (postFields.indexOf($(this).prop("name")) < 0) {
                            params[$(this).prop("name")] = '';
                        }
                    });
                }
                //调用Ajax请求方法
                http.api.ajax({
                    type: type,
                    url: url,
                    data: form.serialize() + (Object.keys(params).length > 0 ? '&' + $.param(params) : ''),
                    dataType: 'json',
                    complete: function (xhr) {
                        var token = xhr.getResponseHeader('__token__');
                        if (token) {
                            $("input[name='__token__']").val(token);
                        }
                    }
                }, function (data, ret) {
                    //$('.form-group', form).removeClass('has-feedback has-success has-error');
                    if (data && typeof data === 'object') {
                        //刷新客户端token
                        if (typeof data.token !== 'undefined') {
                            $("input[name='__token__']").val(data.token);
                        }
                        //调用客户端事件
                        if (typeof data.callback !== 'undefined' && typeof data.callback === 'function') {
                            data.callback.call(form, data);
                        }
                    }
                    if (typeof success === 'function') {
                        if (false === success.call(form, data, ret)) {
                            return false;
                        }
                    }
                }, function (data, ret) {
                    if (data && typeof data === 'object' && typeof data.token !== 'undefined') {
                        $("input[name='__token__']").val(data.token);
                    }
                    if (typeof error === 'function') {
                        if (false === error.call(form, data, ret)) {
                            return false;
                        }
                    }
                });
                return true;
            },
            //这是一个函数，接收四个参数：form: 表单元素或表单的选择器。success: 成功回调函数。error: 错误回调函数。submit: 提交回调函数。
            bindevent: function (form, success, error, submit) {
                //如果传入的 form 不是对象，则将其转换为 jQuery 对象。这确保了后续操作可以直接使用 jQuery 方法。
                form = typeof form === 'object' ? form : $(form);
                //从 bdForm 对象中获取 events 对象，它包含了一些与表单相关的事件处理方法。
                var events = bdForm.events;
                //绑定表单事件,调用 events 对象中的 bindevent 方法，用于绑定表单的事件（例如图标选择器等）。
                events.bindevent(form);
                //绑定表单验证事件 调用 events 对象中的 validator 方法，用于绑定表单的验证逻辑。
                //success, error, 和 submit 参数会被传递给验证逻辑，分别表示成功、错误和提交时的回调函数。
                events.validator(form, success, error, submit);
            },
        }
    };
    exports('bdForm', bdForm);
});