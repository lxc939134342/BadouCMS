import createAxios from '/@/utils/axios'

/**
 * 获取待迁移的表列表
 */
export function getTables(params: { pbootPath: string }) {
    return createAxios({
        url: '/admin/PbootToBadoucms/getTables',
        method: 'post',
        data: params,
    })
}

/**
 * 验证PbootCMS配置
 */
export function checkConfig(params: { pbootPath: string }) {
    return createAxios({
        url: '/admin/PbootToBadoucms/checkConfig',
        method: 'post',
        data: params,
    })
}

/**
 * 执行数据迁移
 */
export function migrate(params: any = {}) {
    return createAxios({
        url: '/admin/PbootToBadoucms/migrate',
        method: 'post',
        data: params,
    })
}

/**
 * 执行文件迁移
 */
export function migrateFiles(params: any = {}) {
    return createAxios({
        url: '/admin/PbootToBadoucms/migrateFiles',
        method: 'post',
        data: params,
    })
}
