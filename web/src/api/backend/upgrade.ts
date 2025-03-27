import createAxios from '/@/utils/axios'

export const url = '/admin/Upgrade/'

export function version() {
    return createAxios(
        {
            url: url + 'version',
            method: 'get',
        },
        {
            showCodeMessage: false,
        }
    )
}

export function getList() {
    return createAxios({
        url: url + 'getList',
        method: 'get',
    })
}

export function backup() {
    return createAxios(
        {
            url: url + 'backup',
            method: 'get',
            timeout: 60 * 1000,
        },
        {
            loading: true,
        },
        {
            text: '备份中……',
            background: 'rgba(0, 0, 0, 0.7)',
        }
    )
}

export function upgrade(params: object = {}) {
    return createAxios(
        {
            url: url + 'update',
            method: 'post',
            data: params,
            timeout: 60 * 1000,
        },
        {
            loading: true,
        },
        {
            text: '升级中……',
            background: 'rgba(0, 0, 0, 0.7)',
        }
    )
}
