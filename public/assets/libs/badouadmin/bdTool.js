layui.define(['bdHttp', 'xmSelect'], function (exports) {
    "use strict";
    var http = layui.bdHttp;
    var xmSelect = layui.xmSelect;
    var bdTool = {
        // 远程下拉选择框
        remoteSelect: function () {
            var themeColor = localStorage.getItem("theme-color-color");
            var dark = localStorage.getItem("dark");
            if ($('.remoteSelect').length > 0) {
                $(document).on('click', '.remoteSelectClearAll', function () {
                    xmSelect.batch(null, 'setValue', []);
                });

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
                    var initValue = $(this).data('init-value');
                    var pidname = $(this).data('pidname') || 'pid';
                    var toolbarShow = $(this).data('toolbar-show') || true;
                    var toolbarShowIcon = $(this).data('toolbar-showIcon') || true;
                    var toolbarList = $(this).data('toolbar-list') || ["ALL", "CLEAR"];
                    var inputId = $(this).data('input-id');

                    var maxSelectLimit = $(this).data('max-select-limit');
                    var orderBy = $(this).data('order-by');
                    var params = $(this).data('params');
                    var inputValue = '';
                    // 设置值
                    if (inputId) {
                        inputValue = $('#' + inputId).val();
                        initValue = inputValue;
                    }
                    if (typeof initValue === 'string' && initValue) {
                        initValue = initValue.split(',');
                    }

                    var options = {
                        el: '#' + id,
                        toolbar: {
                            show: toolbarShow,
                            showIcon: toolbarShowIcon,
                            list: toolbarList
                        },
                        data: [],
                        paging: pagination,
                        pageSize: pageSize,
                        initValue: initValue,
                        prop: {
                            name: field,
                            value: key
                        },
                        theme: {
                            color: themeColor
                        },
                        tree: {
                            //是否显示树状结构
                            show: true,
                            //是否展示三角图标
                            showFolderIcon: true,
                            //是否显示虚线
                            showLine: true,
                            //间距
                            indent: 20,
                            //默认展开节点的数组, 为 true 时, 展开所有节点
                            expandedKeys: true,
                            //是否严格遵守父子模式
                            strict: false,
                        },
                        model: {
                            icon: 'show',
                            label: {
                                type: 'block',
                                block: {
                                    template: function (item, sels) {
                                        return item.name;
                                    },
                                },
                            }
                        },
                        on: function (data) {
                            //arr:  当前多选已选中的数据
                            var arr = data.arr;
                            var values = arr.map(function (item) {
                                return item[key];
                            });
                            if (inputId) {
                                $('#' + inputId).val(values.join(','))
                            }
                        }
                    }

                    if (dark == 'true') {
                        options.theme.hover = '#000';
                    }

                    // 单选
                    if (!multiple) {
                        // options.max = 1;
                        // options.maxMethod = function (data, arr) {
                        //     console.log(data, arr);
                        //     remoteSelect.setValue([arr]);
                        // }
                        options.radio = true;
                        options.clickClose = true;
                        // options.model.icon = 'hidden';
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
                        isTree: isTree,
                        pidname: pidname
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
                            if (isTree) {
                                remoteSelect.changeExpandedKeys(true)
                            }

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
        // 清空远程下拉
        clearRemoteSelect: function () {
            // 获取所有远程选择框的实例
            $('.remoteSelect').each(function () {
                var $this = $(this);
                var id = $(this).attr('id');
                var select = xmSelect.get('#' + id, true);
                // 获取必要的数据属性
                var inputId = $this.data('input-id');
                if (select) {
                    // 清空选择
                    select.setValue([]);
                    // 确保关联的 input 也被清空
                    if (inputId) {
                        $('#' + inputId).val('');
                    }
                }
            });
        },
        // 时间选择组件
        laydate: function () {
            if ($('.laydate').length > 0) {
                $('.laydate').each(function (i) {
                    var type = $(this).data('type') || 'datetime';
                    var range = $(this).data('range') || false;
                    var isIntValue = $(this).data('is-init-value') ?? true;

                    var options = {
                        elem: this,
                        type: type,
                        isInitValue: isIntValue,
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
                    if (!$(this).val()) {
                        options.value = new Date();
                    }
                    layui.laydate.render(options);
                });
            }
        }
    };

    exports('bdTool', bdTool);
});