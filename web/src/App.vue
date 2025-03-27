<template>
    <el-config-provider :locale="lang">
        <router-view></router-view>
    </el-config-provider>
</template>
<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useConfig } from '/@/stores/config'
import { setTitleFromRoute } from '/@/utils/common'
import iconfontInit from '/@/utils/iconfont'
import { init as viteInit } from '/@/utils/vite'
// modules import mark, Please do not remove.
console.log('App.vue import 1') // Code from module 'ueditorplus'(avi)

const route = useRoute()
const config = useConfig()

// 初始化 element 的语言包
const { getLocaleMessage } = useI18n()
const lang = getLocaleMessage(config.lang.defaultLang) as any
onMounted(() => {
    viteInit()
    iconfontInit()

    // Modules onMounted mark, Please do not remove.
    console.log('App.vue onMounted1') // Code from module 'ueditorplus'(avo)
    console.log('App.vue onMounted1') // Code from module 'cmsskin'(avo)
})

// 监听路由变化时更新浏览器标题
watch(
    () => route.path,
    () => {
        setTitleFromRoute()
    }
)
</script>
