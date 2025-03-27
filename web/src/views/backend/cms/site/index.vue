<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="state.remark" :title="state.remark" type="info" show-icon />
        <!-- 表单 -->
        <el-card shadow="never">
            <DataLang style="margin-bottom: 20px; margin-left: 0px" @change-data-lang="changeDataLang"></DataLang>
            <el-scrollbar v-loading="state.loading" class="ba-table-form-scrollbar">
                <el-tabs v-model="state.activeTab" type="card">
                    <el-tab-pane class="basic-tab-pane" name="basic" :label="t('cms.site.basic')">
                        <div class="ba-operate-form">
                            <el-form
                                v-if="!state.loading"
                                ref="formRef"
                                @submit.prevent=""
                                @keyup.enter="onSubmit(formRef)"
                                :model="state.form"
                                :label-position="config.layout.shrink ? 'top' : 'right'"
                                label-width="200px"
                                :rules="rules"
                            >
                                <FormItem
                                    :label="t('cms.site.title')"
                                    type="string"
                                    v-model="state.form.title"
                                    prop="title"
                                    :placeholder="t('Please input field', { field: t('cms.site.title') })"
                                />
                                <FormItem
                                    :label="t('cms.site.subtitle')"
                                    type="string"
                                    v-model="state.form.subtitle"
                                    prop="subtitle"
                                    :placeholder="t('Please input field', { field: t('cms.site.subtitle') })"
                                />
                                <FormItem
                                    :label="t('cms.site.domain')"
                                    type="string"
                                    v-model="state.form.domain"
                                    prop="domain"
                                    :placeholder="t('Please input field', { field: t('cms.site.domain') })"
                                />
                                <FormItem
                                    :label="t('cms.site.logo')"
                                    type="image"
                                    v-model="state.form.logo"
                                    prop="logo"
                                    :placeholder="t('Please input field', { field: t('cms.site.logo') })"
                                />
                                <FormItem
                                    :label="t('cms.site.keywords')"
                                    type="string"
                                    v-model="state.form.keywords"
                                    prop="keywords"
                                    :placeholder="t('Please input field', { field: t('cms.site.keywords') })"
                                />
                                <FormItem
                                    :label="t('cms.site.description')"
                                    type="textarea"
                                    v-model="state.form.description"
                                    prop="description"
                                    :placeholder="t('Please input field', { field: t('cms.site.description') })"
                                />
                                <FormItem
                                    :label="t('cms.site.icp')"
                                    type="string"
                                    v-model="state.form.icp"
                                    prop="icp"
                                    :placeholder="t('Please input field', { field: t('cms.site.icp') })"
                                />
                                <FormItem
                                    :label="t('cms.site.theme')"
                                    type="string"
                                    v-model="state.form.theme"
                                    prop="theme"
                                    :placeholder="t('Please input field', { field: t('cms.site.theme') })"
                                />
                                <FormItem
                                    :label="t('cms.site.statistical')"
                                    type="string"
                                    v-model="state.form.statistical"
                                    prop="statistical"
                                    :placeholder="t('Please input field', { field: t('cms.site.statistical') })"
                                />
                                <FormItem
                                    :label="t('cms.site.copyright')"
                                    type="string"
                                    v-model="state.form.copyright"
                                    prop="copyright"
                                    :placeholder="t('Please input field', { field: t('cms.site.copyright') })"
                                />
                            </el-form>
                        </div>
                    </el-tab-pane>
                </el-tabs>
            </el-scrollbar>
            <div style="margin-left: 200px">
                <el-button v-blur :loading="state.loading" @click="onSubmit(formRef)" type="primary">
                    {{ t('Save') }}
                </el-button>
            </div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, reactive, provide } from 'vue'
import type { FormInstance, FormItemRule } from 'element-plus'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import { index, postData } from '/@/api/backend/cms/Site'
import { uuid } from '/@/utils/random'
import DataLang from '/@/components/datalang/index.vue'
import { useCmsLang } from '/@/stores/cms/lang'
defineOptions({
    name: 'cms/site',
})
const { t } = useI18n()
const config = useConfig()
const formRef = ref<FormInstance>()
provide('baTable', false)

const cmsLang = useCmsLang()

const state: {
    loading: boolean
    config: anyObj
    remark: string
    activeTab: string
    showAddForm: boolean
    rules: Partial<Record<string, FormItemRule[]>>
    form: anyObj
    formKey: string
    params: anyObj
} = reactive({
    loading: true,
    config: [],
    remark: '',
    activeTab: 'basic',
    showAddForm: false,
    rules: {},
    form: {},
    formKey: uuid(),
    params: {
        acode: cmsLang.getDataLang(),
    },
})

const getIndex = () => {
    index(state.params)
        .then((res) => {
            state.loading = false
            state.form = res.data.data
            state.formKey = uuid()
        })
        .catch(() => {
            state.loading = false
        })
}

function onSubmit(formEl: FormInstance | undefined = undefined) {
    if (!formRef.value) return
    formRef.value.validate((valid) => {
        if (valid) {
            state.form.acode = cmsLang.getDataLang()
            postData('edit', state.form)
        }
    })
}

onMounted(() => {
    getIndex()
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({})

const changeDataLang = (e: any) => {
    state.params.acode = e.datalang
    getIndex()
}
</script>

<style scoped lang="scss"></style>
