import createAxios from '/@/utils/axios'

export const url = '/admin/cms.memberGroup/'

/**
 * 获取等级选择列表
 */
export const getSelect = async () => {
    return await createAxios({ url: url + 'getSelect' })
}
