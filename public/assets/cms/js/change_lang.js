layui.define(['dropdown'], function (exports) {
    var dropdown = layui.dropdown;

    exports('changeLang', function (options) {
        var badou = layui.badou;
        var table = options.table || null;
        console.log(Config);
        dropdown.render({
            elem: '#changeLang',
            customName: {
                title: 'name',
                id: 'acode'
            },
            data: Config.alist,
            click: function (obj) {
                if (table) {
                    badou.api.ajax({ url: 'cms.base/changelang', data: { acode: obj.acode } }, function () {
                        var id = table.initTable.config.id;
                        table.api.events.toolbar.refresh(id);
                        $('#changeLang .lang-title').text(obj.name);
                    });
                }
            }
        });
    });
});
