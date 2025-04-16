import { i18n } from '/@/lang/index'
import { loadlang } from '/@/api/backend/cms/Common'

export class Language {
    private static cache = new Map<string, any>()

    static async load(path: string, lang: string = 'zh-cn') {
        if (!path.includes('cms/content')) {
            return {}
        }
        const cacheKey = `${path}_${lang}`
        if (this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey)
        }
        try {
            const response = await loadlang(path, lang)
            console.log('Language response:', response) // 调试输出
            if (response.code === 1) {
                this.cache.set(cacheKey, response.data)
                // 处理路径，提取实际的模块路径
                const modulePath = path.replace(/^\.\/(?:backend|frontend)\/[^/]+\//, '').replace(/\.ts$/, '')
                console.log('Module path:', modulePath) // 调试输出
                this.mergeI18n(modulePath, response.data)
                return response.data
            }
            return {}
        } catch (error) {
            console.error('Language load failed:', error)
            return {}
        }
    }

    static mergeI18n(path: string, messages: any) {
        const locale = i18n.global.locale.value

        // 将路径转换为对象层级，并过滤掉 mcode 及其后的部分
        const pathParts = path
            .split('/')
            .filter(
                (segment) =>
                    segment !== '.' &&
                    !segment.startsWith('.') &&
                    segment !== 'backend' &&
                    segment !== 'frontend' &&
                    !segment.endsWith('.ts') &&
                    segment !== 'mcode'
            )
            .slice(0, 2) // 只保留前两级路径（cms/content）

        // 构建嵌套对象
        const nestedMessages = {}
        let current = nestedMessages
        pathParts.forEach((part, index) => {
            if (index === pathParts.length - 1) {
                ;(current as Record<string, any>)[part] = messages
            } else {
                ;(current as Record<string, any>)[part] = {}
                current = (current as Record<string, any>)[part]
            }
        })

        // 合并到当前语言环境
        i18n.global.mergeLocaleMessage(locale, nestedMessages)

        console.log('Language merged:', nestedMessages)
    }

    static preload(paths: string[], lang?: string) {
        return Promise.all(paths.map((path) => this.load(path, lang)))
    }
}
