<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog bd-right-full-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="95%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <el-form
                v-if="!baTable.form.loading"
                ref="formRef"
                @submit.prevent=""
                @keyup.enter="baTable.onSubmit(formRef)"
                :model="baTable.form.items"
                :label-position="config.layout.shrink ? 'top' : 'right'"
                :label-width="baTable.form.labelWidth + 'px'"
                :rules="rules"
            >
                <el-tabs v-model="state.activeTab" type="card">
                    <el-tab-pane class="basic-tab-pane" name="basic" :label="t('cms.content.basic')">
                        <div
                            class="ba-operate-form"
                            :class="'ba-' + baTable.form.operate + '-form'"
                            :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
                        >
                            <FormItem
                                :label="t('cms.content.title')"
                                type="string"
                                v-model="baTable.form.items!.title"
                                prop="title"
                                :placeholder="t('Please input field', { field: t('cms.content.title') })"
                            />
                            <FormItem
                                :label="t('cms.content.ico')"
                                type="image"
                                v-model="baTable.form.items!.ico"
                                prop="ico"
                                :placeholder="t('Please input field', { field: t('cms.content.ico') })"
                            />

                            <template v-for="item in fields" :key="item.name">
                                <template v-if="item.component == 'textarea'">
                                    <FormItem
                                        @keyup.enter.stop=""
                                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                                        :label="item!.description"
                                        :type="item!.component"
                                        v-model="baTable.form.items![item.name]"
                                        :prop="item!.name"
                                        :input-attr="{
                                            content: item!.content,
                                        }"
                                        :placeholder="t('Please input field', { field: t(item!.name) })"
                                    />
                                </template>
                                <template v-else>
                                    <FormItem
                                        :label="item!.description"
                                        :type="item!.component"
                                        v-model="baTable.form.items![item.name]"
                                        :prop="item!.name"
                                        :input-attr="{
                                            content: item!.content,
                                        }"
                                        :placeholder="t('Please input field', { field: t(item!.name) })"
                                    />
                                </template>
                            </template>

                            <FormItem
                                :label="t('cms.content.content')"
                                type="editor"
                                v-model="baTable.form.items!.content"
                                prop="content"
                                @keyup.enter.stop=""
                                @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                                :placeholder="t('Please input field', { field: t('cms.content.content') })"
                            />

                            <FormItem
                                :label="t('cms.content.tags')"
                                type="string"
                                v-model="baTable.form.items!.tags"
                                prop="tags"
                                :placeholder="t('cms.content.Please enter the article tag, English comma separated')"
                            />

                            <FormItem
                                :label="t('cms.content.author')"
                                type="string"
                                v-model="baTable.form.items!.author"
                                prop="author"
                                :placeholder="t('Please input field', { field: t('cms.content.author') })"
                            />

                            <FormItem
                                :label="t('cms.content.source')"
                                type="string"
                                v-model="baTable.form.items!.source"
                                prop="source"
                                :placeholder="t('Please input field', { field: t('cms.content.source') })"
                            />

                            <FormItem
                                :label="t('cms.content.pics')"
                                type="images"
                                v-model="baTable.form.items!.pics"
                                prop="pics"
                                :placeholder="t('Please input field', { field: t('cms.content.pics') })"
                            />
                        </div>
                    </el-tab-pane>

                    <el-tab-pane name="senior" class="senior-tab-pane" :label="t('cms.content.senior')">
                        <div
                            class="ba-operate-form"
                            :class="'ba-' + baTable.form.operate + '-form'"
                            :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
                        >
                            <FormItem
                                :label="t('cms.content.titlecolor')"
                                type="color"
                                v-model="baTable.form.items!.titlecolor"
                                prop="titlecolor"
                                :placeholder="t('Please input field', { field: t('cms.content.titlecolor') })"
                            />

                            <FormItem
                                :label="t('cms.content.subtitle')"
                                type="string"
                                v-model="baTable.form.items!.subtitle"
                                prop="subtitle"
                                :placeholder="t('Please input field', { field: t('cms.content.subtitle') })"
                            />

                            <FormItem
                                :label="t('cms.content.date')"
                                type="datetime"
                                v-model="baTable.form.items!.date"
                                prop="date"
                                :blockHelp="t('cms.content.Warm Tip: Set a future time for scheduled publishing')"
                                :placeholder="t('Please select field', { field: t('cms.content.date') })"
                            />

                            <FormItem
                                :label="t('cms.content.enclosure')"
                                type="file"
                                v-model="baTable.form.items!.enclosure"
                                prop="enclosure"
                                :placeholder="t('Please input field', { field: t('cms.content.enclosure') })"
                            />

                            <FormItem
                                :label="t('cms.content.keywords')"
                                type="string"
                                v-model="baTable.form.items!.keywords"
                                prop="keywords"
                                :placeholder="t('Please input field', { field: t('cms.content.keywords') })"
                            />
                            <FormItem
                                :label="t('cms.content.description')"
                                type="textarea"
                                v-model="baTable.form.items!.description"
                                prop="description"
                                :placeholder="t('Please input field', { field: t('cms.content.description') })"
                            />

                            <FormItem
                                :label="t('cms.content.status')"
                                type="radio"
                                v-model="baTable.form.items!.status"
                                prop="status"
                                :input-attr="{ content: { '1': '显示', '0': '隐藏' } }"
                                :placeholder="t('Please select field', { field: t('cms.content.status') })"
                            />
                        </div>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
        </el-scrollbar>
        <template #footer>
            <div :style="'margin-left:' + baTable.form.labelWidth! + 'px'" style="text-align: left">
                <el-button @click="baTable.toggleForm()">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import type { FormInstance, FormItemRule } from 'element-plus'
import { inject, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import type baTableClass from '/@/utils/baTable'
import { buildValidatorData } from '/@/utils/validate'
import { getFields } from '/@/api/backend/cms/Extfield'

interface Field {
    component: string
    content: object
    description: string
    id: number
    name: string
    sorting: number
    type: string
    value: string
    mcode: string
}

const config = useConfig()
const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const mcode = inject('mcode') as string
const fields = ref<Field[]>([])

const { t } = useI18n()

const state: { activeTab: string } = reactive({
    activeTab: 'basic',
})

getFields(mcode).then((res) => {
    if (res.code == 1) {
        fields.value = res.data
    }
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({})

setTimeout(() => {
    rules.title = [buildValidatorData({ name: 'required', title: t('cms.content.title') })]
    rules.content = [buildValidatorData({ name: 'editorRequired', title: t('cms.content.content') })]
    rules.scode = [buildValidatorData({ name: 'required', title: t('cms.content.scode') })]
}, 200)
</script>

<style scoped lang="scss"></style>
