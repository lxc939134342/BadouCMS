import createAxios from '/@/utils/axios'

export const url = '/admin/cms.Common/'

export function loadlang(path: string,lang: string) {
    return createAxios({
        url: url + 'loadlang',
        method: 'post',
        data:{
            path: path,
            lang:lang
        }
    })
}

export function sorting(url:string,id: number, sorting: number){
    return createAxios({
        url:url,
        method: 'post',
        data:{
            id: id,
            sorting: sorting
        }
    })
}
