import createAxios from '/@/utils/axios'

export const url = '/admin/cms.memberField/'

export function getList() {
    return createAxios({ url: url + 'index' })
}
