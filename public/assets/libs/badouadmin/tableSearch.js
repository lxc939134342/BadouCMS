layui.define(['jquery', 'form', 'bdHttp', 'bdTool'], function (exports) {
    'use strict';

    var $ = layui.jquery;
    var form = layui.form;
    var http = layui.bdHttp;
    var bdTool = layui.bdTool;
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
                        html.push('<input type="hidden" class="remoteSelect-name" name="' + col.field + '" value="">');
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
                        html.push('<input type="' + type + '" name="' + col.field + '" value="' + defaultValue + '" placeholder="' + placeholder + '" class="layui-input laydate" autocomplete="off" data-range="true">');
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
            // 远程下拉选择框
            bdTool.remoteSelect();
            // 时间组件
            bdTool.laydate();
            // 表单提交
            form.on('submit(commonsearch)', function (data) {
                that._triggerSearch();
                return false;
            });

            // 重置搜索
            $('.badouadmin-commonsearch button[type="reset"]').on('click', function () {
                setTimeout(function () {
                    that._resetRemoteSelect();
                }, 1);
            });
        },
        // 重置远程下拉选择框
        _resetRemoteSelect: function () {
            bdTool.remoteSelect();
            this._triggerSearch();
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
                // 处理xm-select组件
                var remoteSelect = $(this).siblings('.remoteSelect');
                if (remoteSelect.length > 0) {
                    var id = remoteSelect.attr('id');
                    var select = xmSelect.get('#' + id, true);
                    if (select) {
                        value = select.getValue('valueStr');
                        obj.val(value)
                    }
                }
                if (obj.length > 1) {
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