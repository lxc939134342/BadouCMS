import createAxios from '/@/utils/axios'

export const url = '/admin/cms.Models/'

export function add() {
    return createAxios({
        url: url + 'add',
        method: 'get',
    })
}
