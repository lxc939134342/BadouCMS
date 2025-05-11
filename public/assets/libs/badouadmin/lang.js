layui.define(function (exports) {
    var lang = {};
    var load = function (url, callback) {
        // 通过 ajax 加载语言包
        layui.jquery.ajax({
            url: Config.app_url + '/ajax/lang?callback=define&controllername=' + Config.controllername + '&lang=' + Config.language,
            dataType: 'jsonp',
            async: false,  // 同步加载
            success: function (res) {
                lang = res;
            }
        });
    }
    load()

    // 定义翻译函数
    var __ = function () {
        var args = arguments,
            string = args[0],
            i = 1;
        string = string.toLowerCase();
        //string = typeof lang[string] != 'undefined' ? lang[string] : string;
        if (typeof lang !== 'undefined' && typeof lang[string] !== 'undefined') {
            if (typeof lang[string] == 'object')
                return lang[string];
            string = lang[string];
        } else if (string.indexOf('.') !== -1 && false) {
            var arr = string.split('.');
            var current = lang[arr[0]];
            for (var i = 1; i < arr.length; i++) {
                current = typeof current[arr[i]] != 'undefined' ? current[arr[i]] : '';
                if (typeof current != 'object')
                    break;
            }
            if (typeof current == 'object')
                return current;
            string = current;
        } else {
            string = args[0];
        }
        return string.replace(/%((%)|s|d)/g, function (m) {
            // m is the matched format, e.g. %s, %d
            var val = null;
            if (m[2]) {
                val = m[2];
            } else {
                val = args[i];
                // A switch statement so that the formatter can be extended. Default is %s
                switch (m) {
                    case '%d':
                        val = parseFloat(val);
                        if (isNaN(val)) {
                            val = 0;
                        }
                        break;
                }
                i++;
            }
            return val;
        });
    };
    window.__ = __;
    // 导出模块
    exports('lang',{__,load});
});