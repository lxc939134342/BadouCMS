layui.define(['jquery', 'bdHttp', 'toast', 'upload', 'laytpl', 'layer'], function (exports) {
    "use strict";
    var $ = layui.jquery;
    var upload = layui.upload;
    var http = layui.bdHttp;
    var laytpl = layui.laytpl;
    var layer = layui.layer;

    var bdUpload = {
        config: {
            previewtpl: `
                <div class="layui-upload-item" >
                    <img src="{{=d.fullurl}}" class="layui-upload-img" style="width: 100%; height: 92px" />
                </div>
            `
        },
        render: function (options) {
            options = $.extend({}, this.config, options || {});
            $('.btn-bdupload').each(function () {
                var that = this;
                //填充ID
                var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
                //预览ID
                var preview_id = $(that).data("preview-id") ? $(that).data("preview-id") : "";
                var url = $(that).data('url') ? $(that).data('url') : Config.upload.uploadurl;
                upload.render({
                    elem: that, // 绑定多个元素
                    url: url, // 此处配置你自己的上传接口即可
                    accept: 'images', // 普通文件
                    done: function (res, index, upload) {
                        if (res.code == 1) {
                            bdUpload.events.onUploadSuccess.call(that, res.data, index, upload);
                        } else {

                        }
                    }
                });

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
                    console.log(top.layer);
                    layer.photos({
                        photos: '#' + preview_id,
                    })
                    // refresh(previewObj.data("name"));
                });
                $("#" + input_id).trigger("change");
            })



        },
        events: {
            onUploadSuccess: function (data, index, upload) {
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
            }
        }
    };

    exports('bdUpload', bdUpload);
});