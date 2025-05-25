<template>
    <div class="data-lang">
        <el-select v-model="state.datalang" placeholder="数据区域" style="width: 130px">
            <el-option v-for="item in state.options" :key="item.value" :label="item.value + ' ' + item.label" :value="item.value" />
        </el-select>
        <el-button type="success" @click="changeDataLang" :icon="Switch">切换</el-button>
    </div>
</template>

<script setup lang="ts">
import { reactive, inject, onMounted, computed } from 'vue'
import type baTableClass from '/@/utils/baTable'
import { Switch } from '@element-plus/icons-vue'
import { useCmsLang } from '/@/stores/cms/lang'

const baTable = inject('baTable') as baTableClass

const emits = defineEmits<{
    // 数据区域被点击
    (e: 'changeDataLang', value: any): void
}>()
const cmsLang = useCmsLang()
cmsLang.init()

const state: {
    datalang: string
    options: anyObj
} = reactive({
    datalang: cmsLang.getDataLang(),
    options: cmsLang.getLangs(),
})
/* 加载语言列表 */
state.options = computed(() => cmsLang.getLangs())

onMounted(() => {
    if (baTable) {
        baTable.table.filter!.acode = state.datalang
        baTable.form.defaultItems!.acode = state.datalang
    }
})

const changeDataLang = () => {
    cmsLang.setDataLang(state.datalang)
    if (baTable) {
        /* 重新加载数据 */
        baTable.table.filter!.acode = state.datalang
        baTable.form.defaultItems!.acode = state.datalang
        baTable.getIndex()

        baTable.initComSearch()
    }

    emits('changeDataLang', { datalang: state.datalang })
}
</script>

<style lang="scss">
.data-lang {
    margin: 0 12px;
    .el-select__wrapper {
        border-top-right-radius: 0px !important;
        border-bottom-right-radius: 0px !important;
    }

    .el-button {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
}
</style>
