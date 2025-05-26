layui.define(['jquery', 'form', 'bdHttp'], function (exports) {
    'use strict';

    var $ = layui.jquery;
    var form = layui.form;
    var http = layui.bdHttp;
    var ColumnsForSearch = [];
    var CommonSearch = {
        config: {
            elem: null,
            action: "",
            template: "",
            visible: false,
            renderDefault: true
        },

        set: function (options) {
            var that = this;
            that.config = $.extend({}, that.config, options);
            return that;
        },

        render: function (options) {
            var that = this;
            that.set(options);

            if (!that.config.elem) {
                console.error('CommonSearch: elem参数不能为空');
                return;
            }

            var html = that._createForm(that.config.columns);
            var modal = '<div class="badouadmin-commonsearch ' + (that.config.visible ? '' : 'layui-hide') + '">' + html + '</div>';
            $(that.config.elem).before(modal);

            that._bindEvents();
            form.render();
        },

        _createForm: function (columns) {
            var that = this;
            var html = [];
            if (that.config.template) {
                return layui.laytpl(that.config.template).render({ columns: columns });
            }

            html.push('<form class="layui-form" action="' + that.config.action + '" method="post">');
            html.push('<div class="layui-row layui-col-space10">');

            $.each(columns, function (i, col) {
                if (col.field && col.field !== 'operate' && col.operate !== false) {
                    ColumnsForSearch.push(col);
                    html.push('<div class="layui-col-xs12 layui-col-sm6 layui-col-md4 layui-col-lg3">');
                    html.push('<div class="layui-form-item">');
                    html.push('<label class="layui-form-label">' + col.title + '</label>');
                    html.push('<div class="layui-input-block">');

                    // 隐藏的操作符字段
                    col.operate = col.operate ? col.operate.toUpperCase() : '=';
                    var hiddenOperateHtml = '<input type="hidden" class="badouadmin-commonsearch-operate" data-name="' + col.field + '" id="' + col.field + '" name="' + col.field + '-opearate" value="' + col.operate + '">';

                    var placeholder = col.placeholder || col.title;
                    var type = col.inputType || 'text';
                    var defaultValue = col.defaultValue || '';

                    if (col.searchList) {
                        html.push(hiddenOperateHtml);
                        // 下拉选择框
                        html.push('<select id="' + col.field + '" name="' + col.field + '" lay-filter="commonsearch">');
                        html.push('<option value="">请选择</option>');

                        if (typeof col.searchList === 'object') {
                            $.each(col.searchList, function (key, value) {
                                if (value.constructor === Object) {
                                    key = value.id;
                                    value = value.name;
                                }
                                html.push('<option value="' + key + '"' + (key == col.defaultValue ? ' selected' : '') + '>' + value + '</option>');
                            });
                        }

                        html.push('</select>');
                    }
                    // 远程下拉选择框
                    else if (col.searchType == 'remoteSelect') {
                        if (!col.extend) col.extend = '';
                        html.push(hiddenOperateHtml);
                        html.push('<div id="remoteSelect-' + col.field + '" class="remoteSelect" ' + col.extend + '></div>');
                    }
                    // 区间范围
                    else if (col.searchType == 'between') {
                        // 范围搜索
                        var defaultValueArr = defaultValue.toString().match(/\|/) ? defaultValue.split('|') : ['', ''];
                        var placeholderArr = placeholder.toString().match(/\|/) ? placeholder.split('|') : [placeholder, placeholder];
                        col.operate = 'BETWEEN';
                        html.push('<input type="hidden" class="badouadmin-commonsearch-operate" data-name="' + col.field + '" id="' + col.field + '" name="' + col.field + '-opearate" value="' + col.operate + '">');
                        html.push('<div class="layui-row layui-col-space5" style="display:inline-block">');
                        html.push('<div class="layui-col-xs6">');
                        html.push('<input type="' + type + '" id="' + col.field + '-min" name="' + col.field + '" value="' + defaultValueArr[0] + '" placeholder="' + placeholderArr[0] + '" class="layui-input">');
                        html.push('</div>');
                        html.push('<div class="layui-col-xs6">');
                        html.push('<input type="' + type + '" id="' + col.field + '-max" name="' + col.field + '" value="' + defaultValueArr[1] + '" placeholder="' + placeholderArr[1] + '" class="layui-input">');
                        html.push('</div>');
                        html.push('</div>');
                    }
                    // 时间组件
                    else if (col.searchType == 'time') {
                        col.operate = 'RANGE';
                        html.push('<input type="hidden" class="badouadmin-commonsearch-operate" data-name="' + col.field + '" id="' + col.field + '" name="' + col.field + '-opearate" value="' + col.operate + '">');
                        html.push('<input type="' + type + '" name="' + col.field + '" value="' + defaultValue + '" placeholder="' + placeholder + '" class="layui-input badouadmin-datetime" data-range="true">');
                    }
                    else {
                        html.push(hiddenOperateHtml);
                        // 普通搜索
                        html.push('<input type="' + type + '" name="' + col.field + '" value="' + defaultValue + '" placeholder="' + placeholder + '" class="layui-input">');
                    }

                    html.push('</div>');
                    html.push('</div>');
                    html.push('</div>');
                }
            });

            // 搜索按钮
            html.push('<div class="layui-col-xs12 layui-col-sm6 layui-col-md4 layui-col-lg3">');
            html.push('<div class="layui-form-item">');
            html.push('<div class="layui-input-block">');
            html.push('<button class="layui-btn btn-theme-color commonsearch-submit" lay-submit lay-filter="commonsearch">  搜索</button>');
            html.push('<button type="reset" class="layui-btn layui-btn-primary layui-border commonsearch-rest"> 重置</button>');
            html.push('</div>');
            html.push('</div>');
            html.push('</div>');

            html.push('</div>');
            html.push('</form>');

            return html.join('');
        },
        // 绑定事件
        _bindEvents: function () {
            var that = this;
            CommonSearch._remoteSelect();
            // 时间组件
            that._laydate();
            // 表单提交
            form.on('submit(commonsearch)', function (data) {
                that._triggerSearch();
                return false;
            });

            // 重置搜索
            $('.badouadmin-commonsearch button[type="reset"]').on('click', function () {
                setTimeout(function () {
                    that._resetRemoteSelect();
                    that._triggerSearch();
                }, 1);
            });
        },
        // 远程下拉选择框
        _remoteSelect: function (params) {
            var themeColor = localStorage.getItem("theme-color-color");
            var dark = localStorage.getItem("dark");
            $('.remoteSelect').each(function (i) {
                var id = $(this).attr('id');
                var url = $(this).data('source');
                var field = $(this).data('field');
                var searchField = $(this).data('search-field');
                var key = $(this).data('primary-key') || 'id';
                var pagination = $(this).data('pagination') || false;
                var pageSize = $(this).data('page-size') || 10;
                var multiple = $(this).data('multiple');
                var isTree = $(this).data('is-tree') || 0;

                var maxSelectLimit = $(this).data('max-select-limit');
                var orderBy = $(this).data('order-by');
                var params = $(this).data('params');

                var options = {
                    el: '#' + id,
                    toolbar: { show: true },
                    data: [],
                    paging: pagination,
                    pageSize: pageSize,
                    prop: {
                        name: field,
                        value: key
                    },
                    theme: {
                        color: themeColor
                    }
                }

                if (dark == 'true') {
                    options.theme.hover = '#000';
                }

                // 单选
                if (!multiple) {
                    options.radio = true;
                    options.clickClose = true;
                    options.model = {
                        label: {
                            type: 'text',
                        }
                    };
                }
                // 多选的最大数量
                if (maxSelectLimit) {
                    options.maxSelectLimit = maxSelectLimit;
                }

                var remoteSelect = xmSelect.render(options);
                var data = {
                    pageNumber: 1,
                    pageSize: pageSize,
                    showField: field,
                    keyField: key,
                    orderBy: orderBy,
                    custom: params,
                    isTree: isTree
                };
                // 搜索字段
                if (searchField) {
                    searchField = searchField.split(',');
                    data.searchField = searchField;
                }

                if (url) {
                    http.api.ajax({
                        url: url,
                        data: data
                    }, function (ret, res) {
                        remoteSelect.update({
                            data: ret.list
                        });
                        return false;
                    }, function (ret) {
                        return false;
                    });
                }

                window.addEventListener('storage', (e) => {
                    // 暗色模式
                    if (e.key === 'dark') {
                        if (e.newValue == 'true') {
                            xmSelect.batch('', 'update', {
                                theme: {
                                    hover: '#000'
                                }
                            });
                        } else {
                            xmSelect.batch('', 'update', {
                                theme: {
                                    hover: '#f2f2f2'
                                }
                            });
                        }
                    }
                    if (e.key === 'theme-color-color') {
                        xmSelect.batch('', 'update', {
                            theme: {
                                color: e.newValue
                            }
                        });
                    }
                });
            });
        },
        // 时间组件
        _laydate: function () {
            if ($('.badouadmin-datetime').length > 0) {
                $('.badouadmin-datetime').each(function (i) {
                    var type = $(this).data('type') || 'datetime';
                    var range = $(this).data('range') || false;

                    var options = {
                        elem: this,
                        type: type,
                        trigger: 'click'
                    };
                    if (range) {
                        options['range'] = range;
                        options['shortcuts'] = [{
                            text: "今天",
                            value: function () {
                                var today = new Date();
                                return [
                                    new Date(today.getFullYear(), today.getMonth(), today.getDate()),
                                    new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59)
                                ];
                            }
                        },
                        {
                            text: "昨天",
                            value: function () {
                                var yesterday = new Date();
                                yesterday.setDate(yesterday.getDate() - 1);
                                return [
                                    new Date(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate()),
                                    new Date(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate(), 23, 59, 59)
                                ];
                            }
                        },
                        {
                            text: "最近7天",
                            value: function () {
                                var today = new Date();
                                var sevenDaysAgo = new Date();
                                sevenDaysAgo.setDate(today.getDate() - 7);
                                return [
                                    new Date(sevenDaysAgo.getFullYear(), sevenDaysAgo.getMonth(), sevenDaysAgo.getDate()),
                                    new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59)
                                ];
                            }
                        },
                        {
                            text: "最近30天",
                            value: function () {
                                var today = new Date();
                                var sevenDaysAgo = new Date();
                                sevenDaysAgo.setDate(today.getDate() - 30);
                                return [
                                    new Date(sevenDaysAgo.getFullYear(), sevenDaysAgo.getMonth(), sevenDaysAgo.getDate()),
                                    new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59)
                                ];
                            }
                        },
                        {
                            text: "本月",
                            value: function () {
                                var date = new Date();
                                var year = date.getFullYear();
                                var month = date.getMonth();
                                return [
                                    new Date(year, month, 1),
                                    new Date(year, month + 1, 0, 23, 59, 59)
                                ];
                            }
                        },
                        {
                            text: "上个月",
                            value: function () {
                                var date = new Date();
                                var year = date.getFullYear();
                                var month = date.getMonth();
                                return [
                                    new Date(year, month - 1, 1),
                                    new Date(year, month, 0, 23, 59, 59)
                                ];
                            }
                        }
                        ]
                    }
                    layui.laydate.render(options);
                });
            }
        },
        // 重置远程下拉选择框
        _resetRemoteSelect: function () {
            $('.remoteSelect').each(function (i) {
                var id = $(this).attr('id');
                var remoteSelect = xmSelect.get('#' + id, true);

                if (remoteSelect) {
                    remoteSelect.setValue([]);
                }
            });
        },
        // 触发搜索事件
        _triggerSearch: function () {
            var that = this;
            if (typeof that.config.onSearch === 'function') {
                var searchQuery = that._getSearchQuery(true)
                that.config.onSearch({
                    op: JSON.stringify(searchQuery.op),
                    filter: JSON.stringify(searchQuery.filter)
                });
            }
        },
        // 获取搜索参数
        _getSearchQuery: function (removeempty) {
            var op = {};
            var filter = {};
            var formElem = $('.badouadmin-commonsearch form');
            $(".badouadmin-commonsearch-operate", formElem).each(function (i) {
                var name = $(this).data("name");
                var obj = $("[name='" + name + "']", formElem);
                if (obj.length == 0)
                    return true;
                var sym = $(this).is("select") ? $("option:selected", this).val() : $(this).val().toUpperCase();
                var value = obj.val();  // 普通表单字段
                var vObjCol = ColumnsForSearch[i];
                var process = vObjCol && typeof vObjCol.process == 'function' ? vObjCol.process : null;

                if (obj.length > 1) {
                    // 处理xm-select组件
                    var remoteSelect = $(this).next('.remoteSelect');
                    if (remoteSelect.length > 0) {
                        var id = remoteSelect.attr('id');
                        var select = xmSelect.get('#' + id, true);
                        if (select) {
                            value = select.getValue('valueStr');
                        }
                    }

                    if (/BETWEEN$/.test(sym)) {
                        var value_begin = $.trim($("[name='" + name + "']:first", formElem).val()),
                            value_end = $.trim($("[name='" + name + "']:last", formElem).val());
                        if (value_begin.length || value_end.length) {
                            if (process) {
                                value_begin = process(value_begin, 'begin');
                                value_end = process(value_end, 'end');
                            }
                            value = value_begin + ',' + value_end;
                        } else {
                            value = '';
                        }
                    } else {
                        value = $("[name='" + name + "']:checked", formElem).val();
                        value = process ? process(value) : value;
                    }
                } else {
                    value = process ? process(obj.val()) : obj.val();
                }
                if (removeempty && (value === '' || value == null || ($.isArray(value) && value.length === 0)) && !sym.match(/null/i)) {
                    return true;
                }

                if (value !== undefined && value !== '') {
                    op[name] = sym;
                    filter[name] = value;
                }
            });
            return { op: op, filter: filter };
        },

        // 显示搜索框
        toggle: function () {
            $('.badouadmin-commonsearch').toggleClass('layui-hide');
        }
    };

    exports('tableSearch', CommonSearch);
});