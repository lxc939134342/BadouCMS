import createAxios from '/@/utils/axios'

export const url = '/admin/cms.Content/'

/* 复制数据 与 移动数据 */
export function copy_move(type: string, scode: string, ids: string[]) {
    return createAxios(
        {
            url: url + type,
            method: 'post',
            data: {
                scode,
                ids,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

/* 修改排序 */
export function edit_sorting(id: string, sorting: number) {
    return createAxios({
        url: url + 'editSorting',
        method: 'post',
        data: { id, sorting },
    })
}
