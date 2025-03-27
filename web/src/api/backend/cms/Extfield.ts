import createAxios from '/@/utils/axios'

export const url = '/admin/cms.Extfield/'

export function models() {
    return createAxios({
        url: url + 'models',
        method: 'get',
    })
}

export function typeText(){
    return createAxios({
        url: url + 'typeText',
        method: 'get',
    })
}


/**
 * 获取当前模型下的字段列表
 * @param mcode 模型ID
 * @returns
 */
export const getFields = (mcode: number|string) => {
    return createAxios({
        url: url + 'getModelFields',
        method: 'get',
        params: {
            mcode
        }
    })
}
