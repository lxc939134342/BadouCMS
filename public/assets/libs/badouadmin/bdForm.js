
layui.define(['jquery', 'bdHttp', 'form', 'iconPicker','toast'], function (exports) {
    var $ = layui.jquery;
    var http = layui.bdHttp;
    var form = layui.form;
    var iconPicker = layui.iconPicker;
    var toast = layui.toast;
    // var jstree = layui.jstree;
    var bdForm = {
        events: {
            //绑定事件
            bindevent: function (layform) {
                //选择图标水电费
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
                        var index = parent.Layer.getFrameIndex(window.name);
                        parent.Layer.close(index);
                    }
                    return false;
                });
            },
            authGroupBindevent: function () {
                
                // var events = bdForm.events;
                // events.authGroupBindevent($("form.layui-form"), null, null, function() {
                //     console.log(123)
                //     if ($("#treeview").length > 0) {
                //         var r = $("#treeview").jstree("get_all_checked");
                //         $("input[name='row[rules]']").val(r.join(','));
                //     }
                //     return true;
                // });

                //渲染权限节点树
                //变更级别后需要重建节点树
                layui.form.on('select(parentid)', function(data){
                    var parentid = $(data.elem).data("parentid");
                    var id = $(data.elem).data("id");
                    var val = data.value;

                    if (val == id) {
                        $("option[value='" + parentid + "']", this).prop("selected", true).change();
                        // Backend.api.toastr.error('父级不能是它自己');
                        // parent.layui.toast.error('父级不能是它自己');
                        // Toastr.success('父级不能是它自己');
                        toast.success('父级不能是它自己');
                        // parent.layui.toast.success({ message: msg })
                        // parent.layui.toast.success({ message: msg })
                        return false;
                    }
                    $.ajax({
                        url: "auth.group/roletrees",
                        type: 'post',
                        dataType: 'json',
                        data: { id: id, parentid: val },
                        success: function(ret) {
                            console.log(ret)
                            if (ret.hasOwnProperty("code")) {
                                var data = ret.hasOwnProperty("data") && ret.data != "" ? ret.data : "";
                                if (ret.code === 1) {
                                    //销毁已有的节点树
                                    $("#treeview").jstree("destroy");
                                    var events = bdForm.events;
                                    events.rendertree(data);
                                } else {

                                    toast.success({ message: msg });
                                    
                                }
                            }
                        },
                        error: function(e) {
                            toast.success({ message: e.message });
                        }
                    });
                });

                //全选和展开
                layui.form.on('checkbox(checkall)', function(data){
                    $("#treeview").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                });
                layui.form.on('checkbox(expandall)', function(data){
                    $("#treeview").jstree($(this).prop("checked") ? "open_all" : "close_all");
                });
                $("select[name='row[parentid]']").siblings("div.layui-form-select").find("dd.layui-this").click();
            },
            rendertree: function(content) {
                $("#treeview")
                    .on('redraw.jstree', function(e) {
                        $(".layer-footer").attr("domrefresh", Math.random());
                    })
                    .jstree({
                        "themes": { "stripes": true },
                        "checkbox": {
                            "keep_selected_style": false,
                        },
                        "types": {
                            "root": {
                                "icon": "fa fa-folder-open",
                            },
                            "menu": {
                                "icon": "fa fa-folder-open",
                            },
                            "file": {
                                "icon": "fa fa-file-o",
                            }
                        },
                        "plugins": ["checkbox", "types"],
                        "core": {
                            'check_callback': true,
                            "data": content
                        }
                    });
            }
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
            //权限角色组
            authGroupBindevent:function (form, success, error, submit) {
                //如果传入的 form 不是对象，则将其转换为 jQuery 对象。这确保了后续操作可以直接使用 jQuery 方法。
                form = typeof form === 'object' ? form : $(form);
                //从 bdForm 对象中获取 events 对象，它包含了一些与表单相关的事件处理方法。
                var events = bdForm.events;
                //绑定表单事件,调用 events 对象中的 bindevent 方法，用于绑定表单的事件（例如图标选择器等）。
                events.authGroupBindevent(form);
                //绑定表单验证事件 调用 events 对象中的 validator 方法，用于绑定表单的验证逻辑。
                //success, error, 和 submit 参数会被传递给验证逻辑，分别表示成功、错误和提交时的回调函数。
                // events.validator(form, success, error, submit);
            },
        }
    };
    exports('bdForm', bdForm);
});