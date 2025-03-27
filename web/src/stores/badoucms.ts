import { defineStore } from 'pinia'

interface badoucms {
    version: string
    apiUrl: string
    isUpdate: boolean
    upgradeDialog: boolean
}

export const useBadoucms = defineStore('Badoucms', {
    state: (): badoucms => {
        return {
            version: '1.0.0',
            apiUrl: '',
            isUpdate: false,
            upgradeDialog: false,
        }
    },
    actions: {
        dataFill(state: badoucms) {
            this.$state = state
        },
        setIsUpdate(isUpdate: boolean) {
            this.isUpdate = isUpdate
        },
    },
})
