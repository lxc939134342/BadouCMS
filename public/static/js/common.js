(function loadLangScript() {
    var oscript = document.createElement("script")
    oscript.src = "/ajax/lang?controllername=" + Config.controllername + "&lang=" + Config.language + "&callback=lang"
    oscript.async = false // 设置为同步加载
    document.head.appendChild(oscript) // 改为添加到head中以提高优先级
})();

function lang(data) {
    window.Lang = data
}

function __() {
    var args = arguments,
        string = args[0],
        i = 1;
    string = string.toLowerCase();
    //string = typeof Lang[string] != 'undefined' ? Lang[string] : string;
    if (typeof Lang !== 'undefined' && typeof Lang[string] !== 'undefined') {
        if (typeof Lang[string] == 'object')
            return Lang[string];
        string = Lang[string];
    } else if (string.indexOf('.') !== -1 && false) {
        var arr = string.split('.');
        var current = Lang[arr[0]];
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
}

function checkFileMimetype(fileName, fileType) {
    if (!fileName) return false
    const siteConfig = Config.siteConfig

    const allowedSuffixes = Array.isArray(siteConfig.upload.allowedSuffixes)
        ? siteConfig.upload.allowedSuffixes
        : siteConfig.upload.allowedSuffixes.toLowerCase().split(',')
    const allowedMimeTypes = Array.isArray(siteConfig.upload.allowedMimeTypes)
        ? siteConfig.upload.allowedMimeTypes
        : siteConfig.upload.allowedMimeTypes.toLowerCase().split(',')

    const fileSuffix = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase()
    if (allowedSuffixes.includes(fileSuffix) || allowedSuffixes.includes('.' + fileSuffix)) {
        return true
    }
    if (fileType && allowedMimeTypes.includes(fileType)) {
        return true
    }
    return false
}


/**
 * 生成唯一标识
 * @param prefix 前缀
 * @returns 唯一标识
 */
function shortUuid(prefix = '') {
    const time = Date.now()
    const random = Math.floor(Math.random() * 1000000000)
    if (!window.unique) window.unique = 0
    window.unique++
    return prefix + '_' + random + window.unique + String(time)
}


function timeFormat(dateTime, fmt = 'yyyy-mm-dd hh:MM:ss') {
    if (dateTime == 'none') return __('None')
    if (!dateTime) dateTime = Number(new Date())
    if (dateTime.toString().length === 10) {
        dateTime = +dateTime * 1000
    }

    const date = new Date(dateTime)
    let ret
    const opt = {
        'y+': date.getFullYear().toString(), // 年
        'm+': (date.getMonth() + 1).toString(), // 月
        'd+': date.getDate().toString(), // 日
        'h+': date.getHours().toString(), // 时
        'M+': date.getMinutes().toString(), // 分
        's+': date.getSeconds().toString(), // 秒
    }
    for (const k in opt) {
        ret = new RegExp('(' + k + ')').exec(fmt)
        if (ret) {
            fmt = fmt.replace(ret[1], ret[1].length == 1 ? opt[k] : padStart(opt[k], ret[1].length, '0'))
        }
    }
    return fmt
}


/**
 * 字符串补位
 */
function padStart(str, maxLength, fillString = ' ') {
    if (str.length >= maxLength) return str

    const fillLength = maxLength - str.length
    let times = Math.ceil(fillLength / fillString.length)
    while ((times >>= 1)) {
        fillString += fillString
        if (times === 1) {
            fillString += fillString
        }
    }
    return fillString.slice(0, fillLength) + str
}

// 初始化验证码
function refreshCaptcha($img) {
    var captchaId = "";
    var captchaUrl = Config.captchaUrl;
    captchaId = window.shortUuid();
    $img.data("id", captchaId);
    $img.attr("src", `${captchaUrl}?id=${captchaId}`);
}

