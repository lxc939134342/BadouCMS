import { defineStore } from 'pinia'
import { reactive } from 'vue'
import { STORE_CONFIG } from '/@/stores/constant/cacheKey'
import type { Lang, Layout } from '/@/stores/interface'

export const useConfig = defineStore(
    'config',
    () => {
        const layout: Layout = reactive({
            // 全局
            showDrawer: false,
            shrink: false,
            // 后台布局方式，可选值<Default|Classic|Streamline|Double>
            layoutMode: 'Classic',
            // 后台主页面切换动画，可选值<slide-right|slide-left|el-fade-in-linear|el-fade-in|el-zoom-in-center|el-zoom-in-top|el-zoom-in-bottom>
            mainAnimation: 'slide-right',
            isDark: false,

            // 侧边栏
            menuBackground: ['#2c2d34', '#1d1e1f'],
            menuColor: ['#C8CACE', '#CFD3DC'],
            menuActiveBackground: ['#353940', '#353940'],
            menuActiveColor: ['#ffffff', '#ffffff'],
            menuTopBarBackground: ['#2c2d34', '#1d1e1f'],
            menuWidth: 230,
            menuDefaultIcon: 'fa fa-circle-o',
            menuCollapse: false,
            menuUniqueOpened: false,
            menuShowTopBar: true,

            // 顶栏
            headerBarTabColor: ['#000000', '#ffffff'],
            headerBarTabActiveBackground: ['#ffffff', '#1d1e1f'],
            headerBarTabActiveColor: ['#000000', '#ffffff'],
            headerBarBackground: ['#ffffff', '#1d1e1f'],
            headerBarHoverBackground: ['#f5f5f5', '#18222c'],
        })

        const lang: Lang = reactive({
            defaultLang: 'zh-cn',
            fallbackLang: 'zh-cn',
            langArray: [
                { name: 'zh-cn', value: '中文简体' },
                { name: 'en', value: 'English' },
            ],
        })

        function menuWidth() {
            if (layout.shrink) {
                return layout.menuCollapse ? '0px' : layout.menuWidth + 'px'
            }
            // 菜单是否折叠
            return layout.menuCollapse ? '64px' : layout.menuWidth + 'px'
        }

        function setLang(val: string) {
            lang.defaultLang = val
        }

        function onSetLayoutColor(data = layout.layoutMode) {
            // 切换布局时，如果是为默认配色方案，对菜单激活背景色重新赋值
            const tempValue = layout.isDark ? { idx: 1, color: '#1d1e1f', newColor: '#141414' } : { idx: 0, color: '#ffffff', newColor: '#f5f5f5' }
            if (
                data == 'Classic' &&
                layout.headerBarBackground[tempValue.idx] == tempValue.color &&
                layout.headerBarTabActiveBackground[tempValue.idx] == tempValue.color
            ) {
                layout.headerBarTabActiveBackground[tempValue.idx] = tempValue.newColor
            } else if (
                data == 'Default' &&
                layout.headerBarBackground[tempValue.idx] == tempValue.color &&
                layout.headerBarTabActiveBackground[tempValue.idx] == tempValue.newColor
            ) {
                layout.headerBarTabActiveBackground[tempValue.idx] = tempValue.color
            }
        }

        function setLayoutMode(data: string) {
            layout.layoutMode = data
            onSetLayoutColor(data)
        }

        const setLayout = (name: keyof Layout, value: any) => {
            layout[name] = value as never
        }

        const getColorVal = function (name: keyof Layout): string {
            const colors = layout[name] as string[]
            if (layout.isDark) {
                return colors[1]
            } else {
                return colors[0]
            }
        }

        return { layout, lang, menuWidth, setLang, setLayoutMode, setLayout, getColorVal, onSetLayoutColor }
    },
    {
        persist: {
            key: STORE_CONFIG,
        },
    }
)
