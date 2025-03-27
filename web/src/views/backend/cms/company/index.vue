<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="state.remark" :title="state.remark" type="info" show-icon />
        <!-- 表单 -->
        <el-card shadow="never">
            <DataLang style="margin-bottom: 20px; margin-left: 0px" @change-data-lang="changeDataLang"></DataLang>
            <el-scrollbar v-loading="state.loading" class="ba-table-form-scrollbar">
                <el-tabs v-model="state.activeTab" type="card">
                    <el-tab-pane class="basic-tab-pane" name="basic" :label="t('cms.company.basic')">
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
                                    :label="t('cms.company.name')"
                                    type="string"
                                    v-model="state.form.name"
                                    prop="name"
                                    :placeholder="t('Please input field', { field: t('cms.company.name') })"
                                />
                                <FormItem
                                    :label="t('cms.company.address')"
                                    type="string"
                                    v-model="state.form.address"
                                    prop="address"
                                    :placeholder="t('Please input field', { field: t('cms.company.address') })"
                                />
                                <FormItem
                                    :label="t('cms.company.postcode')"
                                    type="string"
                                    v-model="state.form.postcode"
                                    prop="postcode"
                                    :placeholder="t('Please input field', { field: t('cms.company.postcode') })"
                                />
                                <FormItem
                                    :label="t('cms.company.contact')"
                                    type="string"
                                    v-model="state.form.contact"
                                    prop="contact"
                                    :placeholder="t('Please input field', { field: t('cms.company.contact') })"
                                />
                                <FormItem
                                    :label="t('cms.company.mobile')"
                                    type="string"
                                    v-model="state.form.mobile"
                                    prop="mobile"
                                    :placeholder="t('Please input field', { field: t('cms.company.mobile') })"
                                />
                                <FormItem
                                    :label="t('cms.company.phone')"
                                    type="textarea"
                                    v-model="state.form.phone"
                                    prop="phone"
                                    :placeholder="t('Please input field', { field: t('cms.company.phone') })"
                                />
                                <FormItem
                                    :label="t('cms.company.fax')"
                                    type="string"
                                    v-model="state.form.fax"
                                    prop="fax"
                                    :placeholder="t('Please input field', { field: t('cms.company.fax') })"
                                />
                                <FormItem
                                    :label="t('cms.company.email')"
                                    type="string"
                                    v-model="state.form.email"
                                    prop="email"
                                    :placeholder="t('Please input field', { field: t('cms.company.email') })"
                                />
                                <FormItem
                                    :label="t('cms.company.qq')"
                                    type="string"
                                    v-model="state.form.qq"
                                    prop="qq"
                                    :placeholder="t('Please input field', { field: t('cms.company.qq') })"
                                />
                                <FormItem
                                    :label="t('cms.company.weixin')"
                                    type="image"
                                    v-model="state.form.weixin"
                                    prop="weixin"
                                    :placeholder="t('Please input field', { field: t('cms.company.weixin') })"
                                />
                                <FormItem
                                    :label="t('cms.company.blicense')"
                                    type="string"
                                    v-model="state.form.blicense"
                                    prop="blicense"
                                    :placeholder="t('Please input field', { field: t('cms.company.blicense') })"
                                />
                                <FormItem
                                    :label="t('cms.company.other')"
                                    type="string"
                                    v-model="state.form.other"
                                    prop="other"
                                    :placeholder="t('Please input field', { field: t('cms.company.other') })"
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
import { index, postData } from '/@/api/backend/cms/Company'
import { uuid } from '/@/utils/random'
import DataLang from '/@/components/datalang/index.vue'
import { useCmsLang } from '/@/stores/cms/lang'
defineOptions({
    name: 'cms/company',
})
const { t } = useI18n()
const config = useConfig()
const formRef = ref<FormInstance>()
provide('baTable', false)

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
        acode: useCmsLang().getDataLang(),
    },
})

const getIndex = () => {
    index(state.params)
        .then((res: any) => {
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
            state.form.acode = useCmsLang().getDataLang()
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
