import createAxios from '/@/utils/axios'

export const url = '/admin/cms.Slide/'

/**
 * 定义 获取分组List API接口
 * @returns 
 */
export function getGid() {
    return createAxios({
        url: url + 'getGid',
        method: 'get',
    })
}
