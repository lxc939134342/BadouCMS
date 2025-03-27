import { defineStore } from 'pinia'
import { getLangs as getLangsApi } from '/@/api/backend/cms/Area'
interface cmsLang {
    datalang: string
    langs: Array<{
        label: string
        value: string
    }>
}

export const useCmsLang = defineStore('cmsLang', {
    state: (): cmsLang => {
        return {
            datalang: 'cn',
            langs: [],
        }
    },
    actions: {
        init() {
            getLangsApi().then((res) => {
                if (res.code == 1) {
                    this.langs = res.data
                }
            })
        },
        setDataLang(lang: string) {
            this.datalang = lang
        },
        getDataLang() {
            return this.datalang
        },
        getLangs() {
            return this.langs
        },
    },
    persist: {
        key: 'CMS_LANG',
        storage: localStorage,
    },
})
