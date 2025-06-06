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
    // 组件扩展
    Sortable: {
        src: '/assets/libs/sortablejs/Sortable.min.js',
        api: 'Sortable'
    },
    bdTool: "bdTool",        // 工具组件
});

layui.use(['layer', 'badou'], function () {
    var Layer = layui.layer;
    var badou = layui.badou;

    //点击包含.btn-dialog的元素时弹出dialog
    $(document).on('click', '.btn-dialog,.dialogit', function (e) {
        var that = this;
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
});

