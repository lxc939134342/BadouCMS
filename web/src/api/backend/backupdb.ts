import createAxios from '/@/utils/axios'

export const url = '/admin/Backupdb/'

export function backupTable(params: any) {
    return createAxios({
        url: url + 'backupTable',
        method: 'post',
        data: params,
    })
}

export function backupDb() {
    return createAxios({
        url: url + 'backupDb',
        method: 'post',
    })
}

export function optimizeTable(params: any) {
    return createAxios({
        url: url + 'optimize',
        method: 'post',
        data: params,
    })
}

export function repairTable(params: any) {
    return createAxios({
        url: url + 'repair',
        method: 'post',
        data: params,
    })
}
