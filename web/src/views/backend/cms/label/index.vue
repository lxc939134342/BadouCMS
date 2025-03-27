<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="state.remark" :title="state.remark" type="info" show-icon />
        <el-card shadow="never">
            <el-scrollbar v-loading="state.loading" class="ba-table-form-scrollbar">
                <el-tabs v-model="state.activeTab" type="card" @tab-click="tabClick">
                    <el-tab-pane class="basic-tab-pane" name="basic" :label="t('cms.label.basic')">
                        <div class="ba-operate-form">
                            <el-form
                                v-if="!state.loading"
                                ref="formRef"
                                @submit.prevent=""
                                @keyup.enter="onSubmit(formRef)"
                                :model="state.form"
                                :label-position="config.layout.shrink ? 'top' : 'right'"
                                label-width="180px"
                                :rules="rules"
                            >
                                <template v-for="(item, index) in fields" :key="item.name">
                                    <el-row :gutter="15">
                                        <el-col :span="20">
                                            <template v-if="item.component == 'textarea'">
                                                <FormItem
                                                    @keyup.enter.stop=""
                                                    @keyup.ctrl.enter="onSubmit(formRef)"
                                                    :label="item!.description"
                                                    :type="item!.component"
                                                    v-model="item!.value"
                                                    :prop="item!.name"
                                                    :placeholder="t('Please input field', { field: item.description })"
                                                />
                                            </template>
                                            <template v-else-if="item.component == 'radio'">
                                                <FormItem
                                                    @keyup.enter.stop=""
                                                    @keyup.ctrl.enter="onSubmit(formRef)"
                                                    :label="item!.description"
                                                    :type="item!.component"
                                                    v-model="item!.value"
                                                    :prop="item!.name"
                                                    :input-attr="{ content: { 0: t('cms.label.close'), 1: t('cms.label.open') } }"
                                                    :placeholder="t('Please input field', { field: item.description })"
                                                />
                                            </template>
                                            <template v-else>
                                                <FormItem
                                                    :label="item!.description"
                                                    :type="item!.component"
                                                    v-model="item!.value"
                                                    :prop="item!.name"
                                                    :placeholder="t('Please input field', { field: item.description })"
                                                />
                                            </template>
                                        </el-col>
                                        <el-col :span="4">
                                            <el-tag size="large" type="info">{{ '{$bd.' + item.name + '}' }}</el-tag>
                                        </el-col>
                                    </el-row>
                                </template>
                            </el-form>
                        </div>
                        <div style="margin-left: 180px">
                            <el-button v-blur :loading="state.loading" @click="onSubmit(formRef)" type="primary">
                                {{ t('Save') }}
                            </el-button>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane class="list-tab-pane" name="list" :label="t('cms.label.list')">
                        <div style="margin: -16px">
                            <List></List>
                        </div>
                    </el-tab-pane>
                </el-tabs>
            </el-scrollbar>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, reactive, provide } from 'vue'
import type { FormInstance, FormItemRule } from 'element-plus'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import { getContent, postData } from '/@/api/backend/cms/Label'
import { uuid } from '/@/utils/random'
import { useCmsLang } from '/@/stores/cms/lang'
import List from './list.vue'
defineOptions({
    name: 'cms/label',
})
const { t } = useI18n()
const config = useConfig()
const formRef = ref<FormInstance>()

interface Field {
    component: string
    description: string
    id: number
    name: string
    type: string
    value: string
}
const fields = ref<Field[]>([])
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

const getContentData = () => {
    getContent()
        .then((res: any) => {
            state.form = res.data
            fields.value = res.data
            state.formKey = uuid()
        })
        .finally(() => {
            state.loading = false
        })
}

function onSubmit(formEl: FormInstance | undefined = undefined) {
    if (!formRef.value) return
    formRef.value.validate((valid) => {
        if (valid) {
            state.loading = true
            state.form.acode = useCmsLang().getDataLang()
            postData(state.form).finally(() => {
                state.loading = false
            })
        }
    })
}

onMounted(() => {
    getContentData()
})

const tabClick = () => {
    getContentData()
}

const rules: Partial<Record<string, FormItemRule[]>> = reactive({})
</script>

<style lang="scss"></style>
