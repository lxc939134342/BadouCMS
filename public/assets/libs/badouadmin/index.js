window.rootPath = (function (src) {
    src = document.currentScript
        ? document.currentScript.src
        : document.scripts[document.scripts.length - 1].src;
    return src.substring(0, src.lastIndexOf("/") + 1);
})();

layui.config({
    base: rootPath,
    version: "1.0.0"
}).extend({
    lang: "lang",
    badou: "badou",
    bdHttp: "bdHttp",
    tableSearch: "tableSearch",   // 通用搜索
    bdTable: "bdTable",       // 表格组件
    bdForm: "bdForm",        // 表单组件
    cardList: "cardList",      // 卡片列表
    bdUpload: "bdUpload",      // 上传组件
    // 拖拽组件
    Sortable: {
        src: '/assets/libs/sortablejs/Sortable.min.js',
        api: 'Sortable'
    },
    // 下拉组件
    xmSelect: {
        src: '/assets/libs/xm-select/xm-select.js',
        api: 'xmSelect'
    },
    bdTool: "bdTool",        // 工具组件
});

layui.use(['layer', 'badou', 'toast'], function () {
    var Layer = layui.layer;
    var badou = layui.badou;
    var toast = layui.toast;

    //点击包含.btn-dialog的元素时弹出dialog
    $(document).on('click', '.btn-dialog,.dialogit', function (e) {
        var that = this;
        if ($(that).attr('disabled') || $(that).hasClass('disabled')) {
            return false;
        }
        var options = $.extend({}, $(that).data() || {});
        var url = $(that).attr('href');
        var title = $(that).attr("title") || $(that).data("title") || $(that).data('original-title') || $(that).text();
        var button = [];
        if (button && typeof button.callback === 'function') {
            options.callback = button.callback;
        }
        if (typeof options.confirm !== 'undefined') {
            Layer.confirm(options.confirm, function (index) {
                badou.api.open(url, title, options);
                Layer.close(index);
            });
        } else {
            window[$(that).data("window") || 'self'].layui.badou.api.open(url, title, options);
        }
        return false;
    });

    //新窗口
    $(document).on("click", ".btn-addtab", function (e) {
        var that = this;
        if ($(that).attr("disabled") || $(that).hasClass("disabled")) {
            return false;
        }
        var url = $(that).attr("href");
        var id = $(that).data("id") || $(that).attr("id") || "addtab" + Math.random();
        var title =
            $(that).attr("title") ||
            $(that).data("title") ||
            $(that).data("original-title") ||
            $(that).text();
        top.layui.admin.jump(id, title, url)
        return false;
    });

    // 发送验证码
    var si = {};
    $(document).on("click", ".btn-captcha", function (e) {
        var type = $(this).data("type") ? $(this).data("type") : 'mobile';
        var btn = this;
        if ($(btn).hasClass("disabled")) {
            return false;
        }
        badou.api.sendcaptcha = function (btn, type, data, callback) {
            $(btn).addClass("disabled", true).text("发送中...");

            badou.api.ajax({ url: $(btn).data("url"), data: data }, function (data, ret) {
                clearInterval(si[type]);
                var seconds = 60;
                si[type] = setInterval(function () {
                    seconds--;
                    if (seconds <= 0) {
                        clearInterval(si);
                        $(btn).removeClass("disabled").text("发送验证码");
                    } else {
                        $(btn).addClass("disabled").text(seconds + "秒后可再次发送");
                    }
                }, 1000);
                if (typeof callback == 'function') {
                    callback.call(this, data, ret);
                }
            }, function () {
                $(btn).removeClass("disabled").text('发送验证码');
            });
        };
        if (['mobile', 'email'].indexOf(type) > -1) {
            var element = $(this).data("input-id") ? $("#" + $(this).data("input-id")) : $("input[name='" + type + "']", $(this).closest("form"));

            var text = type === 'email' ? '邮箱' : '手机号码';
            if (element.val() === "") {
                toast.error({ message: text + "不能为空！" })
                element.focus();
                return false;
            } else if (type === 'mobile' && !element.val().match(/^1[3-9]\d{9}$/)) {
                toast.error({ message: "请输入正确的" + text + "！" })
                element.focus();
                return false;
            } else if (type === 'email' && !element.val().match(/^[\w\+\-]+(\.[\w\+\-]+)*@[a-z\d\-]+(\.[a-z\d\-]+)*\.([a-z]{2,4})$/)) {
                toast.error({ message: "请输入正确的" + text + "！" })
                element.focus();
                return false;
            }
            var data = { event: $(btn).data("event") };
            data[type] = element.val();
            badou.api.sendcaptcha(btn, type, data);
        } else {
            var data = { event: $(btn).data("event") };
            badou.api.sendcaptcha(btn, type, data, function (data, ret) {
                Layer.open({ title: false, area: ["400px", "430px"], content: "<img src='" + data.image + "' width='400' height='400' /><div class='text-center panel-title'>扫一扫关注公众号获取验证码</div>", type: 1 });
            });
        }
        return false;

    });
});

