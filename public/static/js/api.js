window.requests = []
window.tokenRefreshing = false
const pendingMap = new Map()
const loadingInstance = {
    target: null,
    count: 0,
}

var userInfoKey = 'userInfo'
var userTokenKey = 'ba-user-token'
var userTokenRefreshKey = 'ba-user-token-refresh'

function getUrl() {
    return window.location.protocol + '//' + window.location.host
}


/**
 * 创建`Axios`
 * 默认开启`reductDataFormat(简洁响应)`,返回类型为`ApiPromise`
 * 关闭`reductDataFormat`,返回类型则为`AxiosPromise`
 */
function createAxios(axiosConfig, options, loading) {
    const Axios = axios.create({
        baseURL: getUrl(),
        timeout: 1000 * 10,
        headers: {
            'think-lang': 'zh-cn',
            server: true,
        },
        responseType: 'json',
    })



    // 合并默认请求选项
    options = Object.assign(
        {
            CancelDuplicateRequest: true, // 是否开启取消重复请求, 默认为 true
            loading: false, // 是否开启loading层效果, 默认为false
            reductDataFormat: true, // 是否开启简洁的数据结构响应, 默认为true
            showErrorMessage: true, // 是否开启接口错误信息展示,默认为true
            showCodeMessage: true, // 是否开启code不为1时的信息提示, 默认为true
            showSuccessMessage: false, // 是否开启code为1时的信息提示, 默认为false
            anotherToken: '', // 当前请求使用另外的用户token
        },
        options
    )

    // 请求拦截
    Axios.interceptors.request.use(
        (config) => {
            removePending(config)
            options.CancelDuplicateRequest && addPending(config)
            // 创建loading实例
            if (options.loading) {
                loadingInstance.count++
                if (loadingInstance.count === 1) {
                    loadingInstance.target = ElLoading.service(loading)
                }
            }
            var uToken = userInfo.getToken('auth')
            // 自动携带token
            if (config.headers) {
                const userToken = options.anotherToken || uToken
                if (userToken) (config.headers)['ba-user-token'] = userToken
            }

            return config
        },
        (error) => {
            return Promise.reject(error)
        }
    )

    // 响应拦截
    Axios.interceptors.response.use(
        (response) => {
            removePending(response.config)
            options.loading && closeLoading(options) // 关闭loading

            if (response.config.responseType == 'json') {
                if (response.data && response.data.code !== 1) {
                    if (response.data.code == 409) {
                        if (!window.tokenRefreshing) {
                            window.tokenRefreshing = true
                            return refreshToken()
                                .then((res) => {
                                    if (res.data.type == 'user-refresh') {
                                        userInfo.setToken(res.data.token, 'auth')
                                        response.headers['ba-user-token'] = `${res.data.token}`
                                        window.requests.forEach((cb) => cb(res.data.token, 'user-refresh'))
                                    }
                                    window.requests = []
                                    return Axios(response.config)
                                })
                                .catch((err) => {
                                    userInfo.removeToken()
                                    if (router.currentRoute.value.name != 'userLogin') {
                                        router.push({ name: 'userLogin' })
                                        return Promise.reject(err)
                                    } else {
                                        response.headers['ba-user-token'] = ''
                                        window.requests.forEach((cb) => cb('', 'user-refresh'))
                                        window.requests = []
                                        return Axios(response.config)
                                    }
                                })
                                .finally(() => {
                                    window.tokenRefreshing = false
                                })
                        } else {
                            return new Promise((resolve) => {
                                // 用函数形式将 resolve 存入，等待刷新后再执行
                                window.requests.push((token, type) => {
                                    response.headers['ba-user-token'] = `${token}`
                                    resolve(Axios(response.config))
                                })
                            })
                        }
                    }
                    if (options.showCodeMessage) {
                        ElementPlus.ElNotification({
                            type: 'error',
                            message: response.data.msg,
                            zIndex: 9999,
                        })
                    }

                    // code不等于1, 页面then内的具体逻辑就不执行了
                    return Promise.reject(response.data)
                } else if (options.showSuccessMessage && response.data && response.data.code == 1) {
                    ElementPlus.ElNotification({
                        message: response.data.msg ? response.data.msg : '操作成功',
                        type: 'success',
                        zIndex: 9999,
                    })
                }
            }

            return options.reductDataFormat ? response.data : response
        },
        (error) => {
            error.config && removePending(error.config)
            options.loading && closeLoading(options) // 关闭loading
            options.showErrorMessage && httpErrorStatusHandle(error) // 处理错误状态码
            return Promise.reject(error) // 错误继续返回给到具体页面
        }
    )

    return Axios(axiosConfig)
}


function refreshToken() {
    return createAxios({
        url: '/api/common/refreshToken',
        method: 'POST',
        data: {
            refreshToken: userInfo.getToken('refresh'),
        },
    })
}


var userInfo = {
    state: {
        id: 0,
        username: '',
        nickname: '',
        email: '',
        mobile: '',
        avatar: '',
        gender: 0,
        birthday: '',
        money: 0,
        score: 0,
        last_login_time: '',
        last_login_ip: '',
        join_time: '',
        motto: '',
        token: '',
        refresh_token: '',
    },
    dataFill(state) {
        this.state = { ...this.state, ...state }
        localStorage.setItem(userInfoKey, JSON.stringify(this.state))
    },
    removeToken() {
        localStorage.removeItem('token')
        localStorage.removeItem('refresh_token')
    },
    setToken(token, type) {
        const key = type == 'auth' ? 'token' : 'refresh_token'
        var state = JSON.parse(localStorage.getItem(userInfoKey))
        this.state = { ...this.state, ...state }
        this.state[key] = token
        localStorage.setItem(userInfoKey, JSON.stringify(this.state))
    },
    getToken(type) {
        var state = JSON.parse(localStorage.getItem(userInfoKey))
        this.state = { ...this.state, ...state }
        return type === 'auth' ? this.state.token : this.state.refresh_token
    },
}

/**
 * 处理异常
 * @param {*} error
 */
function httpErrorStatusHandle(error) {
    // 处理被取消的请求
    if (axios.isCancel(error)) return console.error(__('Automatic cancellation due to duplicate request:') + error.message)
    let message = ''
    if (error && error.response) {
        switch (error.response.status) {
            case 302:
                message = __('Interface redirected!')
                break
            case 400:
                message = __('Incorrect parameter!')
                break
            case 401:
                message = __('You do not have permission to operate!')
                break
            case 403:
                message = __('You do not have permission to operate!')
                break
            case 404:
                message = __('Error requesting address:') + error.response.config.url
                break
            case 408:
                message = __('Request timed out!')
                break
            case 409:
                message = __('The same data already exists in the system!')
                break
            case 500:
                message = __('Server internal error!')
                break
            case 501:
                message = __('Service not implemented!')
                break
            case 502:
                message = __('Gateway error!')
                break
            case 503:
                message = __('Service unavailable!')
                break
            case 504:
                message = __('The service is temporarily unavailable Please try again later!')
                break
            case 505:
                message = __('HTTP version is not supported!')
                break
            default:
                message = __('Abnormal problem, please contact the website administrator!')
                break
        }
    }
    if (error.message.includes('timeout')) message = __('Network request timeout!')
    if (error.message.includes('Network'))
        message = window.navigator.onLine ? __('Server exception!') : __('You are disconnected!')

    ElementPlus.ElNotification({
        type: 'error',
        message,
        zIndex: 9999,
    })
}

/**
 * 关闭Loading层实例
 */
function closeLoading(options) {
    if (options.loading && loadingInstance.count > 0) loadingInstance.count--
    if (loadingInstance.count === 0) {
        loadingInstance.target.close()
        loadingInstance.target = null
    }
}

/**
 * 储存每个请求的唯一cancel回调, 以此为标识
 */
function addPending(config) {
    const pendingKey = getPendingKey(config)
    config.cancelToken =
        config.cancelToken ||
        new axios.CancelToken((cancel) => {
            if (!pendingMap.has(pendingKey)) {
                pendingMap.set(pendingKey, cancel)
            }
        })
}

/**
 * 删除重复的请求
 */
function removePending(config) {
    const pendingKey = getPendingKey(config)
    if (pendingMap.has(pendingKey)) {
        const cancelToken = pendingMap.get(pendingKey)
        cancelToken(pendingKey)
        pendingMap.delete(pendingKey)
    }
}

/**
 * 生成每个请求的唯一key
 */
function getPendingKey(config) {
    let { data } = config
    const { url, method, params, headers } = config
    if (typeof data === 'string') data = JSON.parse(data) // response里面返回的config.data是个字符串对象
    return [
        url,
        method,
        headers && (headers).batoken ? (headers).batoken : '',
        headers && (headers)['ba-user-token'] ? (headers)['ba-user-token'] : '',
        JSON.stringify(params),
        JSON.stringify(data),
    ].join('&')
}

/**
 * 根据请求方法组装请求数据/参数
 */
function requestPayload(method, data) {
    if (method == 'GET') {
        return {
            params: data,
        }
    } else if (method == 'POST') {
        return {
            data: data,
        }
    }
}

/**
 * 发送邮件
 */
function sendEms(email, event, extend) {
    return createAxios(
        {
            url: '/api/Ems/send',
            method: 'POST',
            data: {
                email: email,
                event: event,
                ...extend,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 发送短信
 */
function sendSms(mobile, templateCode, extend) {
    return createAxios(
        {
            url: '/api/Sms/send',
            method: 'POST',
            data: {
                mobile: mobile,
                template_code: templateCode,
                ...extend,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 验证账户
 */
function postVerification(data) {
    return createAxios({
        url: '/account/verification',
        method: 'post',
        data: data,
    })
}

/**
 * 绑定账号
 */
function postChangeBind(data) {
    return createAxios(
        {
            url: '/account/changeBind',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 修改密码
 */
function changePassword(params) {
    return createAxios(
        {
            url: '/account/changePassword',
            method: 'POST',
            data: params,
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 积分日志
 */
function getIntegralLog(page, pageSize) {
    return createAxios({
        url: '/account/integral',
        method: 'POST',
        data: {
            page: page,
            limit: pageSize,
        },
    })
}

/**
 * 余额日志
 */
function getBalanceLog(page, pageSize) {
    return createAxios({
        url: '/account/balance',
        method: 'POST',
        data: {
            page: page,
            limit: pageSize,
        },
    })
}

/**
 * 我的评论
 */
function getMyComment(page, pageSize) {
    return createAxios({
        url: '/comment/mycomment',
        method: 'POST',
        data: { page, limit: pageSize },
    })
}

/**
 * 删除评论
 */
function deleteComment(id) {
    return createAxios({
        url: '/comment/delete',
        method: 'POST',
        data: { id },
    })
}