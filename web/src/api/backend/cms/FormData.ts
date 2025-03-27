import createAxios from '/@/utils/axios'

export const url = '/admin/cms.FormData/'

/**
 * 定义 根据fcode获取全部字段 API接口
 * @param fcode 表单编码ID
 */
// export const getFormFieldList = (fcode: number|string)=> {
//     return createAxios({
//         url: url + 'getFormFieldList',
//         method: 'get',
//         params: {
//             fcode
//         }
//     })
// }
