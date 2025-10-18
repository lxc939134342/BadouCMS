KindEditor.ready(function (K) {
    layui.use(["badou"], function () {
        var badou = layui.badou;
        var layer = layui.layer;

        // 辅助函数：从剪贴板获取图片文件
        var getImageFromClipboard = function (data) {
            var i, item;
            i = 0;
            while (i < data.clipboardData.items.length) {
                item = data.clipboardData.items[i];
                if (item.type.indexOf("image") !== -1) {
                    return item.getAsFile() || false;
                }
                i++;
            }
            return false;
        };

        // 辅助函数：从拖拽事件中获取图片文件
        var getImagesFromDrop = function (data) {
            var i, item, images;
            i = 0;
            images = [];
            while (i < data.dataTransfer.files.length) {
                item = data.dataTransfer.files[i];
                if (item.type.indexOf("image") !== -1) {
                    images.push(item);
                }
                i++;
            }
            return images;
        };
        var upload = function (editorInstance, file, uploadCallback) {
            var uploadUrl = badou.api.fixurl("ajax/upload"); // 获取上传URL
            var formData = new FormData();
            formData.append("file", file); // 'file' 是 KindEditor 传递过来的 File 对象
            $.ajax({
                url: uploadUrl,
                type: "POST",
                data: formData,
                processData: false, // 告诉 jQuery 不要去处理发送的数据
                contentType: false, // 告诉 jQuery 不要去设置 Content-Type 请求头
                dataType: "json", // 期望服务器返回 JSON
                success: function (res) {
                    // 假设 Layui/Badou 的上传接口成功时返回
                    if (res.code === 1) {
                        var data = {
                            code: "000",
                            data: { url: badou.api.cdnurl(res.data.url) },
                            title: "",
                            width: "",
                            height: "",
                            border: "",
                            align: "",
                        };
                        uploadCallback(data);
                    } else {
                        if (uploadCallback) {
                            // 如果是 KindEditor 内部上传按钮触发，传递错误信息
                            uploadCallback({ code: 1, message: res.msg || "上传失败" });
                        } else {
                            // 否则，使用 KindEditor 的错误处理函数
                            editorInstance.errorMsgHandler(res.msg || "上传失败", "error");
                        }
                    }
                },
                error: function (xhr, status, error) {
                    var msg = (xhr.responseJSON ? xhr.responseJSON.msg : error) || "上传失败";
                    if (uploadCallback) {
                        // 如果是 KindEditor 内部上传按钮触发，传递错误信息
                        uploadCallback({ error: 1, message: msg });
                    } else {
                        // 否则，使用 KindEditor 的错误处理函数
                        editorInstance.errorMsgHandler(msg, "error");
                    }
                },
            });
        };

        KindEditor.plugin("multiimage", function (K) {
            var self = this,
                name = "multiimage",
                lang = self.lang(name + "."),
                allowImages = K.undef(self.allowImages, false);

            var click = function () {
                var html = [
                    '<div class="ke-dialog-content-inner">',
                    '<div class="ke-dialog-row ke-clearfix">',
                    '<div class=""><div class="ke-inline-block ke-upload-button">' +
                    '<form class="ke-upload-area ke-form nice-validator n-default" method="post" enctype="multipart/form-data" style="width: 266px;margin:50px auto;">' +
                    '<span class="ke-button-common"><input type="button" class="ke-button-common ke-button" value="批量上传图片" style="width:128px;"></span><input type="file" class="ke-upload-file" name="imgFiles" multiple style="width:128px;left:0;right:inherit" tabindex="-1">' +
                    '<span class="ke-button-common" style="margin-left:10px;"><input type="button" class="ke-button-common ke-button ke-select-image" style="width:128px;" value="从图片空间选择"></span>' +
                    "</form>" +
                    "</div></span></div>",
                    "</div>",
                    "</div>",
                ].join("");
                var dialog = self.createDialog({
                    name: name,
                    width: Math.min(document.body.clientWidth, 450),
                    height: 260,
                    title: self.lang(name),
                    body: html,
                    noBtn: {
                        name: self.lang("no"),
                        click: function (e) {
                            self.hideDialog().focus();
                        },
                    },
                }),
                    div = dialog.div;

                $("input[name=imgFiles]", div).change(function () {
                    dialog.showLoading();
                    var files = $(this).prop("files");
                    $.each(files, function (i, file) {
                        self.beforeUpload.call(
                            self,
                            function (data) {
                                self.exec("insertimage", badou.api.cdnurl(data.data.url));
                            },
                            file
                        );
                    });
                    setTimeout(function () {
                        self.hideDialog().focus();
                    }, 0);
                });

                $(".ke-select-image", div).click(function () {
                    self.hideDialog().focus();
                    parent.layui.badou.api.open(self.fileManagerJson, __("Choose"), {
                        callback: function (data) {
                            var urlArr = data.url.split(/\,/);
                            $.each(urlArr, function () {
                                var url = badou.api.cdnurl(this);
                                self.exec("insertimage", url);
                            });
                        },
                    });
                });
            };
            self.clickToolbar(name, click);
        });

        KindEditor.plugin("filemanager", function (K) {
            var self = this;
            self.plugin.filemanagerDialog = function (options) {
                parent.layui.badou.api.open(self.fileManagerJson, __("Choose"), {
                    callback: function (data) {
                        var urlArr = data.url.split(/\,/);
                        $.each(urlArr, function () {
                            var url = badou.api.cdnurl(this);
                            // self.exec('insertimage', url);
                            K('[name="url"]').val(url);
                        });
                    },
                });
            };
        });

        var _bindevent = layui.bdForm.events.bindevent;
        layui.bdForm.events.bindevent = function (form) {
            _bindevent.apply(this, [form]);
            K.create(".editor", {
                dialogOffset: 0, //对话框距离页面顶部的位置，默认为0居中，
                allowFileManager: true,
                allowImageUpload: true,
                allowMediaUpload: true,
                filePostName: "file", // 确保上传文件的表单名称为 'file'
                uploadJson: badou.api.fixurl("ajax/upload"), // 保持此配置，供其他插件使用
                fileManagerJson: badou.api.fixurl("general.attachment/select?element_id=&multiple=true&mimetype=*"), // 文件管理器功能保持不变
                themeType: "black", //主题
                width: "100%",
                items: [
                    "source",
                    "undo",
                    "redo",
                    "preview",
                    "print",
                    "template",
                    "code",
                    "quote",
                    "cut",
                    "copy",
                    "paste",
                    "plainpaste",
                    "wordpaste",
                    "justifyleft",
                    "justifycenter",
                    "justifyright",
                    "justifyfull",
                    "insertorderedlist",
                    "insertunorderedlist",
                    "indent",
                    "outdent",
                    "subscript",
                    "superscript",
                    "clearhtml",
                    "quickformat",
                    "selectall",
                    "formatblock",
                    "fontname",
                    "fontsize",
                    "forecolor",
                    "hilitecolor",
                    "bold",
                    "italic",
                    "underline",
                    "strikethrough",
                    "lineheight",
                    "removeformat",
                    "image",
                    "multiimage",
                    "graft",
                    "media",
                    "insertfile",
                    "table",
                    "hr",
                    "emoticons",
                    "baidumap",
                    "pagebreak",
                    "anchor",
                    "link",
                    "unlink",
                    "about",
                    "fullscreen",
                ],
                noDisableItems: ["source", "fullscreen"], // 确保这些项在禁用模式下仍然可用
                afterCreate: function () {
                    var self = this;
                    //Ctrl+回车提交
                    K.ctrl(document, 13, function () {
                        self.sync(); // 同步编辑器内容到 textarea
                        // 确保 self.srcElement[0] 存在且是有效的 DOM 元素
                        if (self.srcElement && self.srcElement[0]) {
                            $(self.srcElement[0]).closest("form").submit();
                        } else {
                            console.warn("KindEditor: srcElement[0] is undefined, cannot submit form.");
                        }
                    });
                    K.ctrl(self.edit.doc, 13, function () {
                        self.sync(); // 同步编辑器内容到 textarea
                        // 确保 self.srcElement[0] 存在且是有效的 DOM 元素
                        if (self.srcElement && self.srcElement[0]) {
                            $(self.srcElement[0]).closest("form").submit();
                        } else {
                            console.warn("KindEditor: srcElement[0] is undefined, cannot submit form.");
                        }
                    });

                    //粘贴上传
                    $("body", self.edit.doc).bind("paste", function (event) {
                        var image, pasteEvent;
                        pasteEvent = event.originalEvent;
                        if (pasteEvent.clipboardData && pasteEvent.clipboardData.items) {
                            image = getImageFromClipboard(pasteEvent);
                            if (image) {
                                event.preventDefault();
                                // 调用统一的上传函数处理粘贴的图片
                                upload(self, image, function (data) {
                                    self.exec("insertimage", data.data.url);
                                });
                            }
                        }
                    });
                    //拖拽上传
                    $("body", self.edit.doc).bind("drop", function (event) {
                        var images, pasteEvent;
                        pasteEvent = event.originalEvent;
                        if (pasteEvent.dataTransfer && pasteEvent.dataTransfer.files) {
                            images = getImagesFromDrop(pasteEvent);
                            if (images.length > 0) {
                                event.preventDefault();
                                $.each(images, function (i, image) {
                                    // 调用统一的上传函数处理每个拖拽的图片
                                    upload(self, image, function (data) {
                                        self.exec("insertimage", data.data.url);
                                    });
                                });
                            }
                        }
                    });
                },
                beforeUpload: function (ke_upload_button_callback, file) {
                    var self = this; // 'this' 指向 KindEditor 实例
                    var file = file ? file : $("input.ke-upload-file", this.form).prop("files")[0];
                    upload(self, file, ke_upload_button_callback);
                    return false; // 阻止 KindEditor 内部上传按钮的默认提交，由我们的 AJAX 处理
                },
                //错误处理 handler
                errorMsgHandler: function (message, type) {
                    try {
                        JDialog.msg({ type: type, content: message, timer: 2000 });
                    } catch (Error) {
                        layer.msg(message, { icon: 2, time: 2000 });
                    }
                },
            });
        }
    });
});