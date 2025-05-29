layui.define(['jquery', 'bdHttp', 'tableSearch'], function (exports) {
    "use strict";
    var MOD_NAME = 'bdTable',
        $ = layui.jquery,
        http = layui.bdHttp,
        Layer = layui.layer,
        tableSearch = layui.tableSearch,
        laytpl = layui.laytpl;


    var bdTable = {
        // 导入layui的table 或者 treeTable
        table: null,
        // 初始化layui的table 或者 treeTable
        initTable: null,
        // 表格的dom
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
                extend: "lay-event='btn-editone'",
                classname: 'layui-btn layui-btn-sm',
            },
            del: {
                name: 'del',
                icon: 'fa fa-trash',
                title: __('Del'),
                extend: "lay-event='btn-delone'",
                classname: 'layui-btn layui-bg-red layui-btn-sm'
            },
        },
        // 表格渲染
        render: function (options) {
            options.pk = options.pk || 'id';
            options.cols = options.cols || [];
            options.url = options.url || '';
            options.searchFormVisible = options.searchFormVisible || false;
            options.commonSearch === undefined || options.commonSearch === true ? options.commonSearch = true : options.commonSearch = false;
            options.defaultToolbar = options.defaultToolbar || [
                "filter",
                "exports",
                "print", // 内置工具
            ];

            // 高级搜索按钮
            if (options.commonSearch) {
                options.defaultToolbar.push({
                    title: __('Common Search'),
                    layEvent: 'LAYTABLE_SEARCH',
                    icon: 'layui-icon-search',
                    onClick: function (obj) {
                        tableSearch.toggle()
                    }
                });
            }
            //是否始终显示高级搜索表单
            if (options.searchFormVisible) {
                tableSearch.set({ visible: options.searchFormVisible })
            }

            // 后端返回数据格式
            if (typeof options.parseData !== 'function') {
                options.parseData = function (res) {
                    var code = res.code;
                    //兼容后端返回数据格式 1 成功 0 失败
                    code == 1 ? code = 0 : code = -1;
                    return {
                        "code": code, // 解析接口状态
                        "msg": res.msg, // 解析提示文本
                        "count": res.count, // 解析数据长度
                        "data": res.data // 解析数据列表
                    };
                }
            }

            bdTable.initTable = bdTable.table.render(options);
            bdTable.table_elem = options.elem;

            // 渲染搜索表单
            tableSearch.render(
                {
                    elem: bdTable.table_elem,
                    columns: options.cols[0]
                }
            )
            // 监听搜索表单提交
            tableSearch.set({
                onSearch: function (value, item) {
                    bdTable.table.reloadData(
                        bdTable.initTable.config.id,
                        {
                            where: value,
                        }
                    )
                }
            })
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
                var id = bdTable.initTable.config.id;
                // 监听选中
                bdTable.api.events.toolbarCheckbox(id);
                // 监听工具栏事件
                bdTable.table.on('toolbar(' + id + ')', function (obj) {
                    var attrEvent = obj.event;
                    if ($(this).hasClass('disabled')) {
                        return false;
                    }

                    if (bdTable.api.events.toolbar.hasOwnProperty(attrEvent)) {
                        bdTable.api.events.toolbar[attrEvent] && bdTable.api.events.toolbar[attrEvent].call(this, id, obj);
                    }
                    return false;
                });
                // 监听操作事件
                bdTable.table.on('tool(' + id + ')', function (obj) {
                    var attrEvent = obj.event;
                    if (bdTable.api.events.operate.hasOwnProperty(attrEvent)) {
                        bdTable.api.events.operate[attrEvent] && bdTable.api.events.operate[attrEvent].call(this, id, obj);
                    }
                    return false;
                });
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
                    var value = bdTable.api.formatter.value.call(this, data);
                    return '<i class="' + value + '"></i>';
                },
                switch: function (data) {
                    var that = this;
                    that.filter = that.filter || that.field || null;
                    that.checked = that.checked || 1;
                    var value = parseInt(bdTable.api.formatter.value.call(this, data));
                    var checked = value === that.checked ? 'checked' : '';
                    var html = laytpl('<input type="checkbox" name="' + that.field + '" value="' + data.id + '" lay-skin="switch" data-field="' + that.field + '" lay-filter="' + that.filter + '" ' + checked + ' >').render(data);

                    // 监听表格开关切换
                    bdTable.api.events.switch(that.filter);
                    return html
                },
                status: function (data) {
                    var custom = { normal: 'success', hidden: 'gray', deleted: 'danger', locked: 'info' };
                    if (typeof this.custom !== 'undefined') {
                        custom = $.extend(custom, this.custom);
                    }
                    this.custom = custom;
                    this.icon = 'layui-icon layui-icon-circle-dot';
                    return bdTable.api.formatter.normal.call(this, data);
                },
                normal: function (data) {
                    var that = this;
                    var colorArr = ["danger", "success", "primary", "warning", "info", "gray", "red", "yellow", "aqua", "blue", "navy", "teal", "olive", "lime", "fuchsia", "purple", "maroon"];
                    var custom = {};
                    if (typeof that.custom !== 'undefined') {
                        custom = $.extend(custom, that.custom);
                    }
                    var value = bdTable.api.formatter.value.call(this, data);
                    value = value == null || value.length === 0 ? '' : value.toString();
                    var keys = typeof that.searchList === 'object' ? Object.keys(that.searchList) : [];
                    var index = keys.indexOf(value);
                    var color = value && typeof custom[value] !== 'undefined' ? custom[value] : null;
                    var display = index > -1 ? that.searchList[value] : null;
                    var icon = typeof that.icon !== 'undefined' ? that.icon : null;
                    if (!color) {
                        color = index > -1 && typeof colorArr[index] !== 'undefined' ? colorArr[index] : 'primary';
                    }
                    if (!display) {
                        display = value.charAt(0).toUpperCase() + value.slice(1);
                    }
                    var html = '<span class="layui-font-' + color + '">' + (icon ? '<i class="' + icon + '"></i> ' : '') + display + '</span>';
                    if (that.search != false) {
                        html = '<a href="javascript:;" class="searchit" lay-tips="点击搜索 ' + display + '" data-field="' + this.field + '" data-value="' + value + '">' + html + '</a>';
                    }
                    return html;
                },
                text: function (data) {
                    var value = bdTable.api.formatter.value.call(this, data);
                    return '<span class="line-limit-length">' + value + '</span>';
                },
                value: function (data) {
                    var field = this.field;
                    try {
                        var value = getItemField(data, field);
                    } catch (e) {
                        var value = undefined;
                    }
                    return value;
                },
                flag: function (data) {
                    var that = this;
                    var value = bdTable.api.formatter.value.call(this, data);
                    value = value == null || value.length === 0 ? '' : value.toString();
                    var colorArr = { 1: 'red', 2: 'orange', 3: 'green', 4: 'blue', 5: 'purple', 6: 'black' };
                    //如果字段列有定义custom
                    if (typeof this.custom !== 'undefined') {
                        colorArr = $.extend(colorArr, this.custom);
                    }
                    var field = this.field;
                    if (typeof this.customField !== 'undefined') {
                        var customValue = this.customField.split('.').reduce(function (obj, key) {
                            return obj === null || obj === undefined ? '' : obj[key];
                        }, row);
                        value = http.api.escape(customValue);
                        field = this.customField;
                    }
                    if (typeof that.searchList === 'object' && typeof that.searchList.then === 'function') {
                        $.when(that.searchList).done(function (ret) {
                            if (ret.data && ret.data.searchlist && $.isArray(ret.data.searchlist)) {
                                that.searchList = ret.data.searchlist;
                            } else if (ret.constructor === Array || ret.constructor === Object) {
                                that.searchList = ret;
                            }
                        })
                    }
                    if (typeof that.searchList === 'object' && typeof that.custom === 'undefined') {
                        var i = 0;
                        var searchValues = Object.values(colorArr);
                        $.each(that.searchList, function (key, val) {
                            if (typeof colorArr[key] == 'undefined') {
                                colorArr[key] = searchValues[i];
                                i = typeof searchValues[i + 1] === 'undefined' ? 0 : i + 1;
                            }
                        });
                    }

                    //渲染Flag
                    var html = [];
                    var arr = $.isArray(value) ? value : value != '' ? value.split(',') : [];
                    var color, display, label;
                    $.each(arr, function (i, value) {
                        value = value == null || value.length === 0 ? '' : value.toString();
                        if (value === '')
                            return true;

                        color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                        display = typeof that.searchList !== 'undefined' && typeof that.searchList[value] !== 'undefined' ? that.searchList[value] : __(value.charAt(0).toUpperCase() + value.slice(1));
                        value = http.api.escape(value);
                        display = http.api.escape(display);
                        label = '<span class="layui-badge layui-bg-' + color + '">' + display + '</span>';
                        if (that.operate) {
                            html.push('<a href="javascript:;" class="searchit" title="' + __('Click to search %s', display) + '" data-field="' + field + '" data-value="' + value + '">' + label + '</a>');
                        } else {
                            html.push(label);
                        }
                    });
                    return html.join(' ');
                },
                label: function (data) {
                    return bdTable.api.formatter.flag.call(this, data);
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
            // 批量操作请求
            multi: function (action, ids, table, elem) {
                var options = table.config;
                var data = elem ? $(elem).data() : {};
                ids = ($.isArray(ids) ? ids.join(",") : ids);
                var url = typeof data.url !== "undefined" ? data.url : (action == "del" ? bdTable.extend.del_url : bdTable.extend.multi_url);
                var params = typeof data.params !== "undefined" ? (typeof data.params == 'object' ? $.param(data.params) : data.params) : '';

                options = { url: http.api.fixurl(url), data: { action: action, ids: ids, params: params } };
                http.api.ajax(options, function (data, ret) {
                    var success = $(elem).data("success") || $.noop;
                    if (typeof success === 'function') {
                        if (false === success.call(elem, data, ret)) {
                            return false;
                        }
                    }

                    bdTable.api.events.toolbar.refresh(table.config.id);
                }, function (data, ret) {
                    var error = $(elem).data("error") || $.noop;
                    if (typeof error === 'function') {
                        if (false === error.call(elem, data, ret)) {
                            return false;
                        }
                    }
                });
            },
            events: {
                // 选择数据时触发disabled
                toolbarCheckbox: function (id) {
                    var table = bdTable.table;
                    table.on('checkbox(' + id + ')', function (obj) {
                        var checkStatus = table.checkStatus(obj.config.id);

                        $('.layui-table-tool .btn-disabled')
                            .toggleClass('disabled', !checkStatus.data.length);
                        return false;
                    })
                },
                // 左侧工具栏
                toolbar: {
                    // 刷新表格
                    refresh: function (id) {
                        var table = bdTable.table;
                        table.reload(id);
                    },
                    // 添加
                    add: function (id) {
                        var url = bdTable.extend.add_url;
                        http.api.open(url, $(this).data("original-title") || $(this).attr("title") || __('Add'), $(this).data() || {});
                    },
                    // 修改
                    edit: function (id, obj) {
                        var table = bdTable.initTable;

                        var ids = bdTable.api.selectedids();
                        if (ids.length === 0) {
                            layer.msg(__('Please select at least one item'));
                            return false;
                        }

                        var title = $(this).data('title') || $(this).attr("title") || __('Edit');
                        var data = $(this).data() || {};
                        delete data.title;

                        //循环弹出多个编辑框
                        $.each(ids, function (index, row) {
                            var url = bdTable.extend.edit_url;
                            row = $.extend({}, row ? row : {}, { id: row[obj.pk] });
                            url = bdTable.api.replaceurl(url, row, table);
                            http.api.open(url, typeof title === 'function' ? title.call(table, row) : title, data);
                        });
                    },
                    // 删除
                    del: function (id, obj) {
                        var that = this;
                        var table = bdTable.initTable;
                        var ids = bdTable.api.selectedids();
                        if (ids.length === 0) {
                            layer.msg(__('Please select at least one item'));
                            return false;
                        }
                        var idarr = [];
                        $.each(ids, function (i, v) {
                            idarr.push(v[table.config.pk]);
                        });
                        Layer.confirm(
                            __('Are you sure you want to delete the %s selected item?', ids.length),
                            { icon: 3, title: __('Warning'), offset: 0, shadeClose: true, btn: [__('OK'), __('Cancel')] },
                            function (index) {
                                bdTable.api.multi("del", idarr, table, that);
                                Layer.close(index);
                            }
                        );
                    }
                },
                operate: {
                    // 编辑
                    'btn-editone': function (id, obj) {
                        var title = $(this).data('title') || $(this).attr("title") || __('Edit');
                        var data = $(this).data() || {};
                        var row = obj.data;
                        var table = bdTable.initTable;
                        delete data.title;

                        var url = bdTable.extend.edit_url;
                        row = $.extend({}, row ? row : {}, { id: row[obj.pk] });
                        url = bdTable.api.replaceurl(url, row, table);
                        http.api.open(url, typeof title === 'function' ? title.call(table, row) : title, data);
                        return false;
                    },
                    // 删除
                    'btn-delone': function (id, obj) {
                        var table = bdTable.initTable;
                        var data = obj.data;
                        var top = $(this).offset().top - $(window).scrollTop();
                        var left = $(this).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        Layer.confirm(__('Are you sure you want to delete this item?'), { icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true, btn: [__('OK'), __('Cancel')] },
                            function (index) {
                                bdTable.api.multi("del", data[table.config.pk], table, this);
                                Layer.close(index);
                            }
                        );
                    }
                },

                switch: function (filter) {
                    var table = bdTable.initTable;
                    var id = table.config.id;
                    layui.form.on('switch(' + filter + ')', function (obj) {
                        var that = $(this);
                        var url = $(this).attr('data-url') || bdTable.extend.multi_url;
                        var field = $(this).attr('data-field') || 'status';
                        var data = {
                            ids: obj.value,
                            params: field + '=' + (obj.elem.checked ? 1 : 0),
                        };
                        http.api.ajax({
                            url: url,
                            data: data,
                        }, function (data, ret) {
                            bdTable.api.events.toolbar.refresh(id);
                        }, function (data, ret) {
                            that.trigger('click');
                            layui.form.render('checkbox');
                        });
                    });
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
            // 获取选中的条目ID集合
            selectedids: function () {
                var table = bdTable.initTable;
                var id = table.config.id;
                var checkStatus = bdTable.table.checkStatus(id),
                    data = checkStatus.data;
                var arr = [];
                $.each(data, function (i, v) {
                    arr.push(v);
                });
                return arr;
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