import createAxios from '/@/utils/axios'

export const url = '/admin/cms.Company/'

export const actionUrl = new Map([
    ['index', url + 'index'],
    ['add', url + 'add'],
    ['edit', url + 'edit'],
    ['del', url + 'del'],
])

export function index(params: object) {
    return createAxios({
        url: url + 'index',
        method: 'get',
        params: params,
    })
}

export function postData(action: string, data: anyObj) {
    return createAxios(
        {
            url: actionUrl.get(action),
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}
