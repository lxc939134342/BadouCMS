import createAxios from '/@/utils/axios'

export const url = '/admin/cms.Label/'

/* 获取标签内容 */
export function getContent() {
    return createAxios({
        url: url + 'content',
        method: 'get',
    })
}

/* 类型文字 */
export function typeText() {
    return createAxios({
        url: url + 'typeText',
        method: 'get',
    })
}

/* 设置标签内容 */
export function postData(data: anyObj) {
    return createAxios(
        {
            url: url + 'content',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}
