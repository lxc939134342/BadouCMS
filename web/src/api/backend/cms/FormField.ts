import createAxios from '/@/utils/axios'

export const url = '/admin/cms.formField/'

/**
 * 定义 根据fcode获取全部字段 API接口
 * @param fcode 表单编码ID
 */
export const getFormFieldList = async (fcode: number | string) => {
    return await createAxios({
        url: url + 'getFormFieldList',
        method: 'get',
        params: {
            fcode,
        },
    })
}
