layui.define(['jquery', 'http', 'form'], function (exports) {
    var http = layui.http;
    var form = layui.form;
    var bdForm = {
        events: {
            bindevent: function (layform) {

            },
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
                        var index = parent.Layer.getFrameIndex(window.name);
                        parent.Layer.close(index);
                    }
                    return false;
                });
            },
        },
        api: {
            submit: function (form, success, error, submit) {
                if (form.length === 0) {
                    Toastr.error("表单未初始化完成,无法提交");
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
            bindevent: function (form, success, error, submit) {
                form = typeof form === 'object' ? form : $(form);
                var events = bdForm.events;
                events.bindevent(form);
                events.validator(form, success, error, submit);
            }
        }
    };
    exports('bdForm', bdForm);
});