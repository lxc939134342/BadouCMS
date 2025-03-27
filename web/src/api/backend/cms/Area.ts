import createAxios from '/@/utils/axios'

export const url = '/admin/cms.Area/'

/* 获取语言列表 */
export function getLangs() {
    return createAxios({
        url: url + 'get_langs',
        method: 'get',
    })
}
