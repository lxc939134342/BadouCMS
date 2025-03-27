import createAxios from '/@/utils/axios'

export const url = '/admin/cms.memberComment/'

/**
 * 审核 / 禁用
 */
export const review = async (ids: string[], status: number) => {
    return await createAxios({ url: url + 'review', method: 'POST', data: { ids, status } })
}
