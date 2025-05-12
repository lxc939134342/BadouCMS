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
    bdhttp: "bdHttp",
    bdTable: "bdTable",
    bdForm: "bdForm",
    badou: "badou",
}).use([], function () { });