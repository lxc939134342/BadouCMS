layui.define(['dropdown'], function (exports) {
    var dropdown = layui.dropdown;

    exports('changeLang', function (options) {
        var badou = layui.badou;
        var table = options.table || null;
        var click = options.click || null;
        $('#changeLang .lang-title').text(Config.atitle);
        dropdown.render({
            elem: '#changeLang',
            customName: {
                title: 'name',
                id: 'acode'
            },
            data: Config.alist,
            click: function (obj) {
                badou.api.ajax({ url: 'cms.base/changelang', data: { acode: obj.acode } }, function () {
                    if (table) {
                        var id = table.initTable.config.id;
                        table.api.events.toolbar.refresh(id);
                    }
                    if (click) {
                        click(obj);
                    }
                    $('#changeLang .lang-title').text(obj.name);
                    Config.atitle = obj.name;
                });
            }
        });
    });
});
