layui.define(['jquery', 'form', 'bdHttp'], function (exports) {
    'use strict';

    var $ = layui.jquery;
    var form = layui.form;
    var http = layui.bdHttp;

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
            var modal = '<div class="layui-card layui-form badouadmin-commonsearch ' + (that.config.visible ? '' : 'layui-hide') + '">' + html + '</div>';
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

            html.push('<div class="layui-card-body">');
            html.push('<form class="layui-form" action="' + that.config.action + '" method="post">');
            html.push('<div class="layui-row layui-col-space10">');

            $.each(columns, function (i, col) {
                if (col.type != 'checkbox' && col.type != 'radio' && col.field && col.field !== 'operate' && col.operate !== false) {
                    html.push('<div class="layui-col-xs12 layui-col-sm6 layui-col-md4 layui-col-lg3">');
                    html.push('<div class="layui-form-item">');
                    html.push('<label class="layui-form-label">' + col.title + '</label>');
                    html.push('<div class="layui-input-block">');

                    // 隐藏的操作符字段
                    col.operate = col.operate ? col.operate.toUpperCase() : '=';
                    html.push('<input type="hidden" name="' + col.field + '-operate" value="' + col.operate + '">');

                    if (col.searchList) {
                        // 下拉选择框
                        html.push('<select name="' + col.field + '" lay-filter="commonsearch">');
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
                    } else if (col.searchType == 'remoteSelect') {
                        if (!col.extend) col.extend = '';
                        // 远程下拉选择框
                        html.push('<div class="remoteSelect" ' + col.extend + '></div>');
                    } else {
                        // 普通输入框
                        var placeholder = col.placeholder || col.title;
                        var type = col.type || 'text';
                        var defaultValue = col.defaultValue || '';

                        if (/BETWEEN$/.test(col.operate)) {
                            // 范围搜索
                            var defaultValueArr = defaultValue.toString().match(/\|/) ? defaultValue.split('|') : ['', ''];
                            var placeholderArr = placeholder.toString().match(/\|/) ? placeholder.split('|') : [placeholder, placeholder];

                            html.push('<div class="layui-row layui-col-space5" style="display:inline-block">');
                            html.push('<div class="layui-col-xs6">');
                            html.push('<input type="' + type + '" name="' + col.field + '" value="' + defaultValueArr[0] + '" placeholder="' + placeholderArr[0] + '" class="layui-input">');
                            html.push('</div>');
                            html.push('<div class="layui-col-xs6">');
                            html.push('<input type="' + type + '" name="' + col.field + '" value="' + defaultValueArr[1] + '" placeholder="' + placeholderArr[1] + '" class="layui-input">');
                            html.push('</div>');
                            html.push('</div>');
                        } else {
                            // 普通搜索
                            html.push('<input type="' + type + '" name="' + col.field + '" value="' + defaultValue + '" placeholder="' + placeholder + '" class="layui-input">');
                        }
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
            html.push('<button class="layui-btn layui-btn-normal" lay-submit lay-filter="commonsearch">搜索</button>');
            html.push('<button type="reset" class="layui-btn layui-btn-primary">重置</button>');
            html.push('</div>');
            html.push('</div>');
            html.push('</div>');

            html.push('</div>');
            html.push('</form>');
            html.push('</div>');

            return html.join('');
        },

        _bindEvents: function () {
            var that = this;
            CommonSearch._remoteSelect();
            // 表单提交
            form.on('submit(commonsearch)', function (data) {
                that._triggerSearch();
                return false;
            });

            // 重置搜索
            $('.layui-commonsearch button[type="reset"]').on('click', function () {
                setTimeout(function () {
                    that._triggerSearch();
                }, 1);
            });
        },
        // 远程下拉选择框
        _remoteSelect: function (params) {
            $('.remoteSelect').each(function (i) {
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
                    el: this,
                    toolbar: { show: true },
                    data: [],
                    paging: pagination,
                    pageSize: pageSize,
                    prop: {
                        name: field,
                        value: key
                    }
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
            });
        },
        _triggerSearch: function () {
            var that = this;
            if (typeof that.config.onSearch === 'function') {
                that.config.onSearch(that._getSearchParams());
            }
        },

        _getSearchParams: function () {
            var formElem = $('.layui-commonsearch form');
            var params = {};
            var filters = {};

            formElem.find('input[name$="-operate"]').each(function () {
                var name = $(this).attr('name').replace('-operate', '');
                var operate = $(this).val();
                var value = formElem.find('[name="' + name + '"]').val();

                if (value !== '') {
                    params[name] = operate;
                    filters[name] = value;
                }
            });

            return {
                op: params,
                filter: filters
            };
        },

        toggle: function () {
            $('.badouadmin-commonsearch').toggleClass('layui-hide');
        }
    };

    exports('tableSearch', CommonSearch);
});