/*******************************************************************************
 * KindEditor - WYSIWYG HTML Editor for Internet
 * Copyright (C) 2006-2011 kindsoft.net
 *
 * @author Roddy <luolonghao@gmail.com>
 * @site http://www.kindsoft.net/
 * @licence http://www.kindsoft.net/license.php
 *******************************************************************************/

KindEditor.plugin('wordpaste', function (K) {
    var self = this, name = 'wordpaste';
    self.clickToolbar(name, function () {
        var jsonp = function (url, callback) {
            var callbackName = 'jsonp_callback_' + Math.round(100000 * Math.random());
            window[callbackName] = function (data) {
                delete window[callbackName];
                document.body.removeChild(script);
                callback(data);
            };

            var script = document.createElement('script');
            script.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'callback=' + callbackName;
            document.body.appendChild(script);
        };
        var lang = self.lang(name + '.'),
            html = '<div style="padding:10px 20px;">' +
                '<div style="margin-bottom:10px;">' + lang.comment + '</div>' +
                '<iframe class="ke-textarea" frameborder="0" style="width:408px;height:260px;"></iframe>' +
                '</div>',
            dialog = self.createDialog({
                name: name,
                width: Math.min(document.body.clientWidth, 450),
                title: self.lang(name),
                body: html,
                yesBtn: {
                    name: self.lang('yes'),
                    click: function (e) {
                        var str = doc.body.innerHTML;
                        str = K.clearMsWord(str, self.filterMode ? self.htmlTags : K.options.htmlTags);
                        if (typeof self.wordImageServer !== 'undefined' && self.wordImageServer) {
                            var i = 0;
                            var arr = [];
                            var replacedStr = str.replace(/file:\/\/+(localhost)?(\S+\.(png|jpg|jpeg|gif|bmp))/ig, function (value) {
                                arr[i] = value;
                                var replaced = "##" + i + "##";
                                i++;
                                return replaced;
                            });
                            for (var j = 0; j < arr.length; j++) {
                                jsonp("http://" + self.wordImageServer + "/word?index=" + j + "&file=" + encodeURIComponent(arr[j]), function (data) {
                                    K.uploadwordimage.call(self, data.index, data.info);
                                });
                            }
                            str = replacedStr;
                        }
                        self.insertHtml(str).hideDialog().focus();
                    }
                }
            }),
            div = dialog.div,
            iframe = K('iframe', div),
            doc = K.iframeDoc(iframe);
        if (!K.IE) {
            doc.designMode = 'on';
        }
        doc.open();
        doc.write('<!doctype html><html><head><title>WordPaste</title></head>');
        doc.write('<body style="background-color:#FFF;font-size:12px;margin:2px;">');
        if (!K.IE) {
            doc.write('<br />');
        }
        doc.write('</body></html>');
        doc.close();
        if (K.IE) {
            doc.body.contentEditable = 'true';
        }
        iframe[0].contentWindow.focus();
    });
});
