layui.define(["bdHttp", "xmSelect"], function (exports) {
  "use strict";
  var http = layui.bdHttp;
  var xmSelect = layui.xmSelect;
  var bdTool = {
    /**
     * 获取 xmSelect 主题配置
     * @returns {Object} theme 配置对象
     */
    getXmSelectTheme: function () {
      var themeColor = localStorage.getItem("theme-color-color");
      var dark = localStorage.getItem("dark");
      var theme = {
        color: themeColor,
        maxColor: themeColor,
      };
      if (dark === "true") {
        theme.hover = "#000";
      }
      return theme;
    },

    /**
     * 初始化 xmSelect 主题监听器（监听 localStorage 变化自动更新主题）
     */
    initXmSelectThemeListener: function () {
      window.addEventListener("storage", function (e) {
        // 暗色模式
        if (e.key === "dark") {
          xmSelect.batch("", "update", {
            theme: {
              hover: e.newValue === "true" ? "#000" : "#f2f2f2",
            },
          });
        }
        // 主题颜色
        if (e.key === "theme-color-color") {
          xmSelect.batch("", "update", {
            theme: {
              color: e.newValue,
            },
          });
        }
      });
    },

    // 远程下拉选择框
    remoteSelect: function () {
      var self = this;
      if ($(".remoteSelect").length === 0) return;

      // 绑定清空按钮事件
      $(document).on("click", ".remoteSelectClearAll", function () {
        xmSelect.batch(null, "setValue", []);
      });

      $(".remoteSelect").each(function (index) {
        var $el = $(this);
        var id = $el.attr("id") || "remoteSelect_" + Date.now() + "_" + index;
        $el.attr("id", id); // 确保元素有 ID

        // 基础配置
        var config = {
          id: id,
          url: $el.data("source"),
          field: $el.data("field"),
          searchField: String($el.data("search-field") || "name").split(","),
          key: $el.data("primary-key") || "id",
          pagination: $el.data("pagination") || false,
          pageSize: $el.data("page-size") || 10,
          multiple: $el.data("multiple"),
          isTree: $el.data("is-tree") || 0,
          initValue: $el.data("init-value"),
          pidname: $el.data("pidname") || "pid",
          toolbarShow: $el.data("toolbar-show") !== false,
          toolbarShowIcon: $el.data("toolbar-showIcon") !== false,
          toolbarList: $el.data("toolbar-list") || ["ALL", "CLEAR"],
          inputId: $el.data("input-id"),
          maxSelectLimit: $el.data("max-select-limit"),
          orderBy: $el.data("order-by"),
          params: $el.data("params"),
          initData: $el.data("init-data") !== false,
          callbackName: $el.data("callback"),
          filterable: $el.data("filterable"),
          layVerify: $el.data("verify"),
          layVerType: $el.data("vertype") || "msg",
          layReqText: $el.data("reqtext") || "必选项不能为空",
          dataOptions: $el.data("options") || {},
        };

        // 如果开启搜索，强制开启分页
        if (config.filterable) {
          config.pagination = true;
        }

        // 处理初始值
        if (config.inputId && !config.initValue) {
          config.initValue = $("#" + config.inputId).val();
        }
        if (typeof config.initValue === "string" && config.initValue) {
          try {
            var parsed = JSON.parse(config.initValue);
            // 如果解析出来是数组，直接使用
            if (Array.isArray(parsed)) {
              config.initValue = parsed;
            } else {
              config.initValue = config.initValue.split(",");
            }
          } catch (e) {
            config.initValue = config.initValue.split(",");
          }
        }

        // 构建请求数据
        var buildRequestData = function (search, pageIndex) {
          var data = {
            pageNumber: pageIndex || 1,
            pageSize: config.pageSize,
            showField: config.field,
            keyField: config.key,
            orderBy: config.orderBy,
            custom: config.params,
            isTree: config.isTree,
            pidname: config.pidname,
          };
          // 搜索参数处理
          if (search) data.q_word = search;
          if (config.searchField.length) data.searchField = config.searchField;
          return data;
        };

        // 发起远程请求
        var fetchData = function (search, callback, show, pageIndex) {
          var data = buildRequestData(search, pageIndex);
          http.api.ajax(
            { url: config.url, data: data },
            function (ret) {
              var list = ret.list || [];
              var total = ret.total || 0;
              var totalPages = Math.ceil(total / config.pageSize);
              callback(list, totalPages);
              return false;
            },
            function () {
              callback([], 0);
            },
          );
        };

        // 构建 xmSelect 选项
        var options = {
          el: "#" + config.id,
          toolbar: {
            show: config.toolbarShow,
            showIcon: config.toolbarShowIcon,
            list: config.toolbarList,
          },
          filterable: config.filterable,
          remoteSearch: config.filterable,
          paging: config.pagination,
          pageRemote: config.pagination,
          pageSize: config.pageSize,
          initValue: config.initValue,
          layVerify: config.layVerify,
          layVerType: config.layVerType,
          layReqText: config.layReqText,
          prop: { name: config.field, value: config.key },
          theme: self.getXmSelectTheme(),
          tree: {
            show: config.isTree,
            showFolderIcon: true,
            showLine: true,
            indent: 20,
            expandedKeys: true,
            strict: false,
          },
          model: {
            label: {
              type: "block",
              block: {
                template: function (item) {
                  return item[config.field];
                },
              },
            },
          },
          // 事件监听
          on: function (data) {
            var values = data.arr.map(function (item) {
              return item[config.key];
            });
            // 同步隐藏域
            if (config.inputId && values.length) {
              $("#" + config.inputId)
                .val(values.join(","))
                .trigger("change");
            }
            // 执行外部回调
            if (
              config.callbackName &&
              typeof window[config.callbackName] === "function"
            ) {
              window[config.callbackName](values.join(","), data);
            }
          },
          // 远程方法
          remoteMethod: fetchData,
        };

        // 单选/多选特殊配置
        if (!config.multiple) {
          options.radio = true;
          options.clickClose = true;
        }

        // 数量限制
        if (config.maxSelectLimit) {
          options.maxSelectLimit = config.maxSelectLimit;
        }

        // 合并并渲染
        var mergedOptions = $.extend(true, {}, options, config.dataOptions);
        var selectInstance = xmSelect.render(mergedOptions);

        // 如果未开启搜索，初始化时主动请求数据
        if (config.url && config.initData && !config.filterable) {
          fetchData(
            "",
            function (list, totalPages) {
              var updateConfig = { data: list, autoRow: true };
              if (totalPages > 1) {
                updateConfig.paging = true;
                updateConfig.pageRemote = true;
              }
              selectInstance.update(updateConfig);
              if (config.isTree) selectInstance.changeExpandedKeys(true);
            },
            undefined,
            1,
          );
        } else if (
          config.url &&
          config.filterable &&
          config.initValue &&
          config.initValue.length > 0
        ) {
          // 如果开启搜索且有初始值，先请求初始值对应的完整数据
          var data = {
            pageNumber: 1,
            pageSize: 999,
            showField: config.field,
            keyField: config.key,
            orderBy: config.orderBy,
            custom: { [config.key]: config.initValue.join(",") },
            isTree: config.isTree,
            pidname: config.pidname,
          };

          http.api.ajax({ url: config.url, data: data }, function (ret) {
            var list = ret.list || [];
            // 更新数据，此时显示的是选中的数据
            selectInstance.update({
              data: list,
              autoRow: true,
            });
            return false;
          });
        }
      });

      // 初始化主题监听器
      self.initXmSelectThemeListener();
    },
    // 清空远程下拉
    clearRemoteSelect: function () {
      // 获取所有远程选择框的实例
      $(".remoteSelect").each(function () {
        var $this = $(this);
        var id = $(this).attr("id");
        var select = xmSelect.get("#" + id, true);
        // 获取必要的数据属性
        var inputId = $this.data("input-id");
        if (select) {
          // 清空选择
          select.setValue([]);
          // 确保关联的 input 也被清空
          if (inputId) {
            $("#" + inputId).val("");
          }
        }
      });
    },
    // 时间选择组件
    laydate: function () {
      if ($(".laydate").length > 0) {
        $(".laydate").each(function (i) {
          var type = $(this).data("type") || "datetime";
          var range = $(this).data("range") || false;
          var isIntValue = $(this).data("is-init-value") ?? true;

          var options = {
            elem: this,
            type: type,
            isInitValue: isIntValue,
            trigger: "click",
          };
          if (range) {
            options["range"] = range;
            options["shortcuts"] = [
              {
                text: "今天",
                value: function () {
                  var today = new Date();
                  return [
                    new Date(
                      today.getFullYear(),
                      today.getMonth(),
                      today.getDate(),
                    ),
                    new Date(
                      today.getFullYear(),
                      today.getMonth(),
                      today.getDate(),
                      23,
                      59,
                      59,
                    ),
                  ];
                },
              },
              {
                text: "昨天",
                value: function () {
                  var yesterday = new Date();
                  yesterday.setDate(yesterday.getDate() - 1);
                  return [
                    new Date(
                      yesterday.getFullYear(),
                      yesterday.getMonth(),
                      yesterday.getDate(),
                    ),
                    new Date(
                      yesterday.getFullYear(),
                      yesterday.getMonth(),
                      yesterday.getDate(),
                      23,
                      59,
                      59,
                    ),
                  ];
                },
              },
              {
                text: "最近7天",
                value: function () {
                  var today = new Date();
                  var sevenDaysAgo = new Date();
                  sevenDaysAgo.setDate(today.getDate() - 7);
                  return [
                    new Date(
                      sevenDaysAgo.getFullYear(),
                      sevenDaysAgo.getMonth(),
                      sevenDaysAgo.getDate(),
                    ),
                    new Date(
                      today.getFullYear(),
                      today.getMonth(),
                      today.getDate(),
                      23,
                      59,
                      59,
                    ),
                  ];
                },
              },
              {
                text: "最近30天",
                value: function () {
                  var today = new Date();
                  var sevenDaysAgo = new Date();
                  sevenDaysAgo.setDate(today.getDate() - 30);
                  return [
                    new Date(
                      sevenDaysAgo.getFullYear(),
                      sevenDaysAgo.getMonth(),
                      sevenDaysAgo.getDate(),
                    ),
                    new Date(
                      today.getFullYear(),
                      today.getMonth(),
                      today.getDate(),
                      23,
                      59,
                      59,
                    ),
                  ];
                },
              },
              {
                text: "本月",
                value: function () {
                  var date = new Date();
                  var year = date.getFullYear();
                  var month = date.getMonth();
                  return [
                    new Date(year, month, 1),
                    new Date(year, month + 1, 0, 23, 59, 59),
                  ];
                },
              },
              {
                text: "上个月",
                value: function () {
                  var date = new Date();
                  var year = date.getFullYear();
                  var month = date.getMonth();
                  return [
                    new Date(year, month - 1, 1),
                    new Date(year, month, 0, 23, 59, 59),
                  ];
                },
              },
            ];
          }
          if (!$(this).val()) {
            options.value = new Date();
          }
          layui.laydate.render(options);
        });
      }
    },
  };

  exports("bdTool", bdTool);
});
