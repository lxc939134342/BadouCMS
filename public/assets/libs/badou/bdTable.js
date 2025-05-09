layui.define(['jquery', 'http'], function (exports) {
    "use strict";
    var MOD_NAME = 'bdTable',
        table = layui.table,
        $ = layui.jquery,
        http = layui.http,
        laytpl = layui.laytpl;

    var bdTable = {
        table: null,
        initTable: null,
        table_elem: null,
        extend: {
            index_url: '',
            add_url: '',
            edit_url: '',
            del_url: '',
            import_url: '',
            multi_url: '',
        },
        button: {
            edit: {
                name: 'edit',
                icon: 'fa fa-pencil',
                title: __('Edit'),
                extend: 'data-toggle="tooltip" data-container="body"',
                classname: 'layui-btn layui-bg-green layui-btn-xs'
            },
            del: {
                name: 'del',
                icon: 'fa fa-trash',
                title: __('Del'),
                extend: 'data-toggle="tooltip" data-container="body"',
                classname: 'layui-btn layui-bg-red layui-btn-xs'
            },
        },
        // 表格渲染
        render: function (options) {
            options.pk = options.pk || 'id';
            options.cols = options.cols || [];
            options.url = options.url || '';
            bdTable.initTable = bdTable.table.render(options);
            bdTable.table_elem = options.elem;
            bdTable.api.bindevent();
            return bdTable.initTable;
        },
        api: {
            // 初始化layui的table 或者 treeTable
            init: function (options) {
                if (!options.table) {
                    console.log('请先初始化表格');
                    return false;
                }
                bdTable.table = options.table;
                bdTable.extend = $.extend(bdTable.extend, options.extend)
            },
            //事件绑定
            bindevent: function () {


                // var tableId = tableId || bdTable.init.table_render_id;
                // var options = layui.table.getOptions(tableId);
                // // 监听表格开关切换
                // bdTable.events.switch(options, tableId);
            },
            // 表格格式化
            formatter: {
                operate: function (data) {
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                    // 所有按钮名称
                    var names = [];
                    buttons.forEach(function (item) {
                        names.push(item.name);
                    });
                    if (bdTable.extend.edit_url !== '' && names.indexOf('edit') === -1) {
                        bdTable.button.edit.url = bdTable.extend.edit_url;
                        buttons.push(bdTable.button.edit);
                    }
                    if (bdTable.extend.del_url !== '' && names.indexOf('del') === -1) {
                        buttons.push(bdTable.button.del);
                    }
                    return bdTable.api.buttonlink(data, buttons, 'operate');
                },
                buttons: function (data) {
                    // 默认按钮组
                    var buttons = $.extend([], this.buttons || []);
                    return bdTable.api.buttonlink(data, buttons, 'buttons');
                },
                icon: function (data) {
                    var field = this.field;
                    try {
                        var value = getItemField(data, field);
                    } catch (e) {
                        var value = undefined;
                    }
                    return '<i class="' + value + '"></i>';
                },
                switch: function (data) {
                    var that = this;
                    var field = that.field;
                    that.filter = that.filter || that.field || null;
                    that.checked = that.checked || 1;
                    that.tips = that.tips || '开|关';
                    try {
                        var value = getItemField(data, field);
                    } catch (e) {
                        var value = undefined;
                    }
                    var checked = value === that.checked ? 'checked' : '';
                    return laytpl('<input type="checkbox" name="' + that.field + '" value="' + data.id + '" lay-skin="switch" data-field="' + that.field + '" lay-text="' + that.tips + '" lay-filter="' + that.filter + '" ' + checked + ' >').render(data);
                },
                status: function (data) {
                    var custom = { normal: 'success', hidden: 'gray', deleted: 'danger', locked: 'info' };
                    if (typeof this.custom !== 'undefined') {
                        custom = $.extend(custom, this.custom);
                    }
                    this.custom = custom;
                    this.icon = 'iconfont icon-circle-fill';
                    return bdTable.api.formatter.normal.call(this, data);
                },
                normal: function (data) {
                    var that = this;
                    var colorArr = ["danger", "success", "primary", "warning", "info", "gray", "red", "yellow", "aqua", "blue", "navy", "teal", "olive", "lime", "fuchsia", "purple", "maroon"];
                    var custom = {};
                    if (typeof that.custom !== 'undefined') {
                        custom = $.extend(custom, that.custom);
                    }
                    var field = that.field;
                    try {
                        var value = getItemField(data, field);
                        value = value == null || value.length === 0 ? '' : value.toString();
                    } catch (e) {
                        var value = undefined;
                    }
                    value = value == null || value.length === 0 ? '' : value.toString();
                    var keys = typeof that.selectList === 'object' ? Object.keys(that.selectList) : [];
                    var index = keys.indexOf(value);
                    var color = value && typeof custom[value] !== 'undefined' ? custom[value] : null;
                    var display = index > -1 ? that.selectList[value] : null;
                    var icon = typeof that.icon !== 'undefined' ? that.icon : null;
                    if (!color) {
                        color = index > -1 && typeof colorArr[index] !== 'undefined' ? colorArr[index] : 'primary';
                    }
                    if (!display) {
                        display = value.charAt(0).toUpperCase() + value.slice(1);
                    }
                    var html = '<span class="layui-font-' + color + '">' + (icon ? '<i class="' + icon + '"></i>' : '') + display + '</span>';
                    if (that.search != false) {
                        html = '<a href="javascript:;" class="searchit" lay-tips="点击搜索 ' + display + '" data-field="' + this.field + '" data-value="' + value + '">' + html + '</a>';
                    }
                    return html;
                },
                text: function (data) {
                    var field = this.field;
                    try {
                        var value = getItemField(data, field);
                    } catch (e) {
                        var value = undefined;
                    }
                    return '<span class="line-limit-length">' + value + '</span>';
                },
                value: function (data) {
                    var field = this.field;
                    try {
                        var value = getItemField(data, field);
                    } catch (e) {
                        var value = undefined;
                    }
                    return '<span>' + value + '</span>';
                },
                datetime: function (data) {
                    var that = this;
                    var field = that.field;
                    try {
                        var value = getItemField(data, field);
                    } catch (e) {
                        var value = undefined;
                    }
                    var datetimeFormat = typeof that.datetimeFormat === 'undefined' ? 'yyyy-MM-dd HH:mm:ss' : that.datetimeFormat;
                    if (value && isNaN(Date.parse(value))) {
                        return layui.util.toDateString(value * 1000, datetimeFormat)
                    } else if (value && !isNaN(Date.parse(value))) {
                        return layui.util.toDateString(Date.parse(value), datetimeFormat)
                    } else {
                        return '-';
                    }
                }
            },
            events: {
                switch: function (option, id) {
                    console.log(option)
                    var modifyReload = option.modifyReload || false;
                    // layui.form.on('switch(switchStatus)', function(obj) {
                    //     var that = $(this);
                    //     var url = $(this).attr('data-url') || option.init.multi_url;
                    //     var field = $(this).attr('data-field') || 'status';
                    //     var data = {
                    //         id: obj.value,
                    //         param: field + '=' + (obj.elem.checked ? 1 : 0),
                    //     };
                    //     Yzn.api.ajax({
                    //         url: url,
                    //         data: data,
                    //     }, function(data, ret) {
                    //         if (modifyReload) {
                    //             layui.table.reload(tableId);
                    //         }
                    //     }, function(data, ret) {
                    //         that.trigger('click');
                    //         layui.form.render('checkbox');
                    //     });
                    // });
                }
            },
            buttonlink: function (data, buttons, type) {
                var html = [];
                var hidden, visible, disable, url, classname, icon, text, title, refresh, confirm, extend,
                    dropdown, link;
                var dropdowns = {};
                var table = bdTable.initTable
                var row = data
                $.each(buttons, function (i, j) {
                    if (type === 'operate') {
                        if (['add', 'edit', 'del', 'multi'].indexOf(j.name) > -1 && !bdTable.extend[j.name + "_url"]) {
                            return true;
                        }
                    }
                    var attr = $(bdTable.table_elem).data(type + "-" + j.name);

                    if (typeof attr === 'undefined' || attr) {
                        hidden = typeof j.hidden === 'function' ? j.hidden.call(table, row, j) : (typeof j.hidden !== 'undefined' ? j.hidden : false);
                        if (hidden) {
                            return true;
                        }
                        visible = typeof j.visible === 'function' ? j.visible.call(table, row, j) : (typeof j.visible !== 'undefined' ? j.visible : true);
                        if (!visible) {
                            return true;
                        }
                        dropdown = j.dropdown ? j.dropdown : '';
                        url = j.url ? j.url : '';
                        url = typeof url === 'function' ? url.call(table, row, j) : (url ? http.api.fixurl(bdTable.api.replaceurl(url, row, table)) : 'javascript:;');
                        classname = j.classname ? j.classname : 'layui-btn layui-btn-xs';
                        icon = j.icon ? j.icon : '';
                        text = typeof j.text === 'function' ? j.text.call(table, row, j) : j.text ? j.text : '';
                        title = typeof j.title === 'function' ? j.title.call(table, row, j) : j.title ? j.title : text;
                        refresh = j.refresh ? 'data-refresh="' + j.refresh + '"' : '';
                        confirm = typeof j.confirm === 'function' ? j.confirm.call(table, row, j) : (typeof j.confirm !== 'undefined' ? j.confirm : false);
                        confirm = confirm ? 'data-confirm="' + confirm + '"' : '';
                        extend = typeof j.extend === 'function' ? j.extend.call(table, row, j) : (typeof j.extend !== 'undefined' ? j.extend : '');
                        disable = typeof j.disable === 'function' ? j.disable.call(table, row, j) : (typeof j.disable !== 'undefined' ? j.disable : false);
                        if (disable) {
                            classname = classname + ' disabled';
                        }
                        link = '<a href="' + url + '" class="' + classname + '" ' + (confirm ? confirm + ' ' : '') + (refresh ? refresh + ' ' : '') + extend + ' title="' + title + '"><i class="' + icon + '"></i>' + (text ? ' ' + text : '') + '</a>';
                        if (dropdown) {
                            if (typeof dropdowns[dropdown] == 'undefined') {
                                dropdowns[dropdown] = [];
                            }
                            dropdowns[dropdown].push(link);
                        } else {
                            html.push(link);
                        }
                    }
                });
                // if (!$.isEmptyObject(dropdowns)) {
                //     var dropdownHtml = [];
                //     $.each(dropdowns, function (i, j) {
                //         dropdownHtml.push('<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown">' + i + '</button><button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown"><span class="caret"></span></button><ul class="dropdown-menu dropdown-menu-right"><li>' + j.join('</li><li>') + '</li></ul></div>');
                //     });
                //     html.unshift(dropdownHtml.join(' '));
                // }
                return html.join(' ');
            },
            //替换URL中的数据
            replaceurl: function (url, row, table) {
                var options = table ? table.config : null;
                var ids = options ? row[options.pk] : 0;
                row.ids = ids ? ids : (typeof row.ids !== 'undefined' ? row.ids : 0);

                url = url == null || url.length === 0 ? '' : url.toString();
                //自动添加ids参数
                url = !url.match(/(?=([?&]ids=)|(\/ids\/)|(\{ids}))/i) ?
                    url + (url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + '{ids}' : url;
                url = url.replace(/\{(.*?)\}/gi, function (matched) {
                    matched = matched.substring(1, matched.length - 1);
                    var temp = matched.split('.').reduce(function (obj, key) {
                        return obj === null || obj === undefined ? '' : obj[key];
                    }, row);
                    temp = http.api.escape(temp);
                    return temp;
                });
                return url;
            },
        },
        //生成工具栏
        renderToolbar: function (options) {
            var d = options.toolbar,
                tableId = options.id,
                searchInput = options.searchInput,
                elem = options.elem,
                init = options.init;
            d = d || [];
            var toolbarHtml = '';
            $.each(d, function (i, v) {
                if (v === 'refresh') {
                    toolbarHtml += '<a lay-event="btn-refresh" href="javascript:;" class="layui-btn layui-btn-sm yzn-btn-primary btn-refresh" data-table-refresh="' + tableId + '"><i class="iconfont icon-loop-left-line"></i> </a>\n';
                } else if (v === 'add') {
                    if (bdTable.auth('add', elem)) {
                        toolbarHtml += '<a lay-event="btn-add" href="javascript:;" class="layui-btn layui-btn-normal layui-btn-sm"><i class="iconfont icon-add-fill"></i> 添加</a>\n';
                    }
                } else if (v === 'edit') {
                    if (bdTable.auth('edit', elem)) {
                        toolbarHtml += '<a lay-event="btn-edit" href="javascript:;" class="layui-btn layui-btn-normal layui-btn-sm layui-btn-disabled btn-disabled" data-table="' + tableId + '"><i class="iconfont icon-edit-2-line"></i> 编辑</a>\n';
                    }
                } else if (v === 'delete') {
                    if (bdTable.auth('delete', elem)) {
                        toolbarHtml += '<a lay-event="btn-delete" href="javascript:;" class="layui-btn layui-btn-sm layui-btn-danger layui-btn-disabled btn-disabled" data-href="' + init.delete_url + '" data-table="' + tableId + '"><i class="iconfont icon-delete-bin-line"></i> 删除</a>\n';
                    }
                } else if (v === 'recyclebin') {
                    if (bdTable.auth('recyclebin', elem)) {
                        toolbarHtml += '<a class="layui-btn layui-btn-warm layui-btn-sm btn-dialog" href="' + init.recyclebin_url + '" data-title="回收站"><i class="iconfont icon-recycle-line"></i> 回收站</a>\n';
                    }
                } else if (v === 'restore') {
                    if (bdTable.auth('restore', elem)) {
                        toolbarHtml += '<a lay-event="btn-multi" class="layui-btn layui-btn-sm confirm layui-btn-disabled btn-disabled" href="javascript:;" data-url="' + init.restore_url + '" data-action="restore" data-table="' + tableId + '"><i class="iconfont icon-arrow-go-back-line"></i> 还原</a>\n';
                    }
                } else if (v === 'destroy') {
                    if (bdTable.auth('destroy', elem)) {
                        toolbarHtml += '<a lay-event="btn-multi" class="layui-btn layui-btn-sm confirm layui-btn-danger layui-btn-disabled btn-disabled" href="javascript:;" data-url="' + init.destroy_url + '" data-action="destroy" data-table="' + tableId + '"><i class="iconfont icon-close-fill"></i> 销毁</a>\n';
                    }
                } else if (typeof v === "object") {
                    $.each(v, function (ii, vv) {
                        if (bdTable.auth(vv.auth, elem)) {
                            toolbarHtml += bdTable.buildToolbarHtml(vv);
                        }
                    });
                }
            });
            if (searchInput) {
                toolbarHtml += '<input id="layui-input-search" value="" placeholder="搜索" class="layui-input layui-hide-xs" style="display:inline-block;width:auto;float: right;\n' + 'margin:2px 25px 0 0;height:28px;">\n'
            }
            return '<div>' + toolbarHtml + '</div>';
        },
        auth: function (operate, elem) {
            var attr = $(elem).data("operate-" + operate);
            if (typeof attr === 'undefined' || attr) {
                return true;
            }
            if (operate.indexOf('?') >= 0) operate = operate.replace(/([?#])[^'"]*/, '');           //去除参数
            if ($(elem).attr('data-operate-' + operate.toLowerCase()) === '1') {
                return true;
            } else {
                return false;
            }
        },
        buildToolbarHtml: function (j) {
            j.html = j.html || '';
            if (j.html !== '') {
                return j.html;
            }

            var hidden, html, url, classname, refresh, extend, text, title, icon;
            hidden = typeof j.hidden === 'function' ? j.hidden.call(Table, j) : (typeof j.hidden !== 'undefined' ? j.hidden : false);
            if (hidden) {
                return '';
            }
            text = j.text ? j.text : '';
            title = j.title ? j.title : text;
            icon = j.icon ? j.icon : '';

            classname = j.class ? j.class : '';
            refresh = j.refresh ? 'data-refresh="' + j.refresh + '"' : '';
            url = j.url ? j.url : '';
            url = url ? Yzn.api.fixurl(j.url) : 'javascript:;';
            extend = typeof j.extend !== 'undefined' ? j.extend : '';

            html = '<a href="' + url + '" class="' + classname + '" ' + (refresh ? refresh + ' ' : '') + extend + ' title="' + title + '" data-table="' + Table.init.table_render_id + '"><i class="' + icon + '"></i>' + (text ? ' ' + text : '') + '</a>\n';
            return html;
        },


    }

    //获取字段值
    function getItemField(item, field) {
        var customValue = field.split('.').reduce(function (obj, key) {
            return obj === null || obj === undefined ? '' : obj[key];
        }, item);

        return typeof customValue === 'string' ?
            (item.LAY_COL.escape !== false ? layui.util.escape(customValue) : customValue) :
            customValue;
    }

    exports(MOD_NAME, bdTable);
});