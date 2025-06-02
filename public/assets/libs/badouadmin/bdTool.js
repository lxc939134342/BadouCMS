layui.define(['bdHttp'], function (exports) {
    "use strict";
    var http = layui.bdHttp;
    var bdTool = {
        // 远程下拉选择框
        remoteSelect: function () {
            var themeColor = localStorage.getItem("theme-color-color");
            var dark = localStorage.getItem("dark");
            if ($('.remoteSelect').length > 0) {
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
                    console.log(remoteSelect);
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
            }
        },
        // 时间选择组件
        laydate: function () {
            if ($('.laydate').length > 0) {
                $('.laydate').each(function (i) {
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
        }
    };

    exports('bdTool', bdTool);
});