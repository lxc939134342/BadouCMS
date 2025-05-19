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
    bdHttp: "bdHttp",
    bdTable: "bdTable",
    bdForm: "bdForm",
    tableSearch: "tableSearch", // 通用搜索
    badou: "badou",
    cardList: "cardList",
});

layui.use(['layer', 'badou'], function () {
    var Layer = layui.layer;
    var badou = layui.badou;

    //点击包含.btn-dialog的元素时弹出dialog
    $(document).on('click', '.btn-dialog,.dialogit', function (e) {
        var that = this;
        var options = $.extend({}, $(that).data() || {});
        var url = $(that).attr('href');
        var title = $(that).attr("title") || $(that).data("title") || $(that).data('original-title');
        // var button = Backend.api.gettablecolumnbutton(options);
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
});