layui.define(['jquery', 'bdHttp', 'toast', 'upload', 'laytpl', 'layer', 'Sortable'], function (exports) {
    "use strict";
    var $ = layui.jquery;
    var upload = layui.upload;
    var http = layui.bdHttp;
    var laytpl = layui.laytpl;
    var layer = layui.layer;
    var Sortable = layui.Sortable;

    var bdUpload = {
        config: {
            container: document.body,
            elem: '.btn-bdupload',
            accept: 'file',
            previewtpl: `
                <div class="layui-col-xs3 layui-upload-item"  >
                    <img src="{{=d.fullurl}}" data-url="{{=d.url}}" class="layui-upload-img" />
                    <a href="javascript:;" class="layui-btn layui-btn-xs layui-bg-red btn-delete-img"><i class="fa fa-trash"></i></a>
                </div>
            `,
            success: null,
            error: null
        },
        render: function (options) {
            options = $.extend({}, this.config, options || {});
            $(options.elem, options.container).each(function () {

                var that = this;
                //填充ID
                var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
                //预览ID
                var preview_id = $(that).data("preview-id") ? $(that).data("preview-id") : "";
                var multiple = $(this).data("multiple");

                var url = $(that).data('url') ? $(that).data('url') : Config.upload.uploadurl;
                upload.render({
                    elem: that, // 绑定多个元素
                    url: url, // 此处配置你自己的上传接口即可
                    accept: options.accept, // 普通文件
                    done: function (res, index, upload) {
                        if (res.code == 1) {
                            bdUpload.events.onUploadSuccess.call(that, res.data, options);
                        } else {

                        }
                    }
                });

                if (preview_id && input_id) {
                    $(document.body).on("keyup change", "#" + input_id, function (e) {
                        var inputStr = $("#" + input_id).val();
                        var inputArr = inputStr.split(/\,/);
                        var previewObj = $("#" + preview_id);
                        previewObj.empty();
                        var tpl = previewObj.data("template") ? previewObj.data("template") : "";
                        var extend = previewObj.next().is("textarea") ? previewObj.next("textarea").val() : "{}";
                        var json = {};
                        try {
                            json = JSON.parse(extend);
                        } catch (e) {
                        }
                        $.each(inputArr, function (i, j) {
                            if (!j) {
                                return true;
                            }
                            var suffix = /[\.]?([a-zA-Z0-9]+)$/.exec(j);
                            suffix = suffix ? suffix[1] : 'file';
                            var btnData = $(that).data();
                            var fullurl = typeof btnData.cdnurl !== 'undefined' ? http.api.cdnurl(j, btnData.cdnurl) : http.api.cdnurl(j);
                            j = Config.upload.fullmode ? fullurl : j;
                            var value = (json && typeof json[i] !== 'undefined' ? json[i] : null);
                            var data = { url: j, fullurl: fullurl, data: btnData, key: i, index: i, value: value, row: value, suffix: suffix };
                            var html = tpl ? tpl : bdUpload.config.previewtpl;
                            html = laytpl(html).render(data);
                            previewObj.append(html);
                        });
                        layer.photos({
                            photos: '#' + preview_id,
                        });
                        bdUpload.api.refresh(previewObj.data("name"));
                    });
                    $("#" + input_id).trigger("change");
                }

                if (preview_id) {
                    //监听文本框改变事件
                    $("#" + preview_id).on('change keyup', "input,textarea,select", function () {
                        bdUpload.api.refresh($(this).closest(".layui-upload-list").data("name"));
                    });
                    // 监听事件
                    $(document.body).on("bd.preview.change", "#" + preview_id, function () {
                        var urlArr = [];
                        $("#" + preview_id + " [data-url]").each(function (i, j) {
                            urlArr.push($(this).data("url"));
                        });
                        if (input_id) {
                            $("#" + input_id).val(urlArr.join(","));
                        }
                        bdUpload.api.refresh($("#" + preview_id).data("name"));
                    });

                    // 移除按钮事件
                    $(document.body).on("click", "#" + preview_id + " .btn-delete-img", function () {
                        $(this).closest(".layui-upload-item").remove();
                        $("#" + preview_id).trigger("bd.preview.change");
                    });
                }
                //拖动排序
                if (preview_id && multiple) {
                    var previewEl = document.getElementById(preview_id);
                    new Sortable(previewEl, {
                        animation: 150,
                        ghostClass: 'sortable-ghost', // 拖动时的样式类名
                        onEnd: function (evt) {
                            // 排序完成后刷新隐藏的 textarea 值
                            $("#" + preview_id).trigger("bd.preview.change");
                        }
                    });
                }
            })

        },
        api: {
            //刷新隐藏textarea的值
            refresh: function (name) {
                var data = {};
                var textarea = $("textarea[name='" + name + "']");
                var container = textarea.prev("ul");
                $.each($("input,select,textarea", container).serializeArray(), function (i, j) {
                    var reg = /\[?(\w+)\]?\[(\w+)\]$/g;
                    var match = reg.exec(j.name);
                    if (!match)
                        return true;
                    if (!isNaN(match[2])) {
                        data[i] = j.value;
                    } else {
                        match[1] = "x" + parseInt(match[1]);
                        if (typeof data[match[1]] === 'undefined') {
                            data[match[1]] = {};
                        }
                        data[match[1]][match[2]] = j.value;
                    }
                });
                var result = [];
                $.each(data, function (i, j) {
                    result.push(j);
                });
                textarea.val(JSON.stringify(result));
            },
        },
        events: {
            onUploadSuccess: function (data, options) {
                var that = this;
                //如果有文本框则填充
                var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
                if (input_id) {
                    var urlArr = [];
                    var inputObj = $("#" + input_id);
                    if ($(that).data("multiple") && inputObj.val() !== "") {
                        urlArr.push(inputObj.val());
                    }
                    var url = Config.upload.fullmode ? (data.fullurl ? data.fullurl : http.api.cdnurl(data.url)) : data.url;
                    urlArr.push(url);
                    inputObj.val(urlArr.join(",")).trigger("change");
                }

                if (typeof options.success === 'function') {
                    var result = options.success.call(that, data, options);
                    if (result === false)
                        return;
                }
            }
        }
    };

    exports('bdUpload', bdUpload);
});