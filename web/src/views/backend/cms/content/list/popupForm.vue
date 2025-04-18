<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog bd-right-full-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="calc(100vw - 230px)"
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
                                :label="t('cms.content.scode')"
                                type="remoteSelect"
                                v-model="baTable.form.items!.scode"
                                prop="scode"
                                :placeholder="t('Please select field', { field: t('cms.content.scode') })"
                                :style="{ width: '50%' }"
                                :input-attr="{
                                    pk: 'content_sort.scode',
                                    params: {
                                        select: true,
                                        order: 'sorting,asc',
                                        istop: 0,
                                        acode: baTable.table.filter!.acode,
                                        search: [{ field: 'mcode', operator: '=', val: mcode }],
                                    },
                                    field: 'name',
                                    remoteUrl: '/admin/cms.ContentSort/index',
                                }"
                            />

                            <FormItem
                                :label="t('cms.content.title')"
                                type="string"
                                :style="{ width: '50%' }"
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
                                        :placeholder="t('Please input field')"
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
                                        :placeholder="t('Please input field')"
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
                                :style="{ width: '50%' }"
                                :placeholder="t('cms.content.Please enter the article tag, English comma separated')"
                            />

                            <FormItem
                                :label="t('cms.content.author')"
                                type="string"
                                v-model="baTable.form.items!.author"
                                prop="author"
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.author') })"
                            />

                            <FormItem
                                :label="t('cms.content.source')"
                                type="string"
                                v-model="baTable.form.items!.source"
                                prop="source"
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.source') })"
                            />

                            <FormItem
                                :label="t('cms.content.pics')"
                                type="images"
                                v-model="baTable.form.items!.pics"
                                prop="pics"
                                :placeholder="t('Please input field', { field: t('cms.content.pics') })"
                            />

                            <el-form-item :label="t('cms.content.parameter')">
                                <label style="margin-right: 10px">{{ t('cms.content.istop') }}</label>
                                <el-switch v-model="baTable.form.items!.istop" active-value="1" inactive-value="0" />

                                <label style="margin-left: 15px; margin-right: 10px">{{ t('cms.content.isrecommend') }}</label>
                                <el-switch v-model="baTable.form.items!.isrecommend" active-value="1" inactive-value="0" />

                                <label style="margin-left: 15px; margin-right: 10px">{{ t('cms.content.isheadline') }}</label>
                                <el-switch v-model="baTable.form.items!.isheadline" active-value="1" inactive-value="0" />
                            </el-form-item>

                            <FormItem
                                :label="t('cms.content.gid')"
                                type="remoteSelect"
                                v-model="baTable.form.items!.gid"
                                prop="gid"
                                :input-attr="{
                                    remoteUrl: '/admin/cms.memberGroup/getUserLevel',
                                    pk: 'gcode',
                                    field: 'gname',
                                }"
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.gid') })"
                            />

                            <FormItem
                                :label="t('cms.content.gtype')"
                                type="remoteSelect"
                                v-model="baTable.form.items!.gtype"
                                prop="gtype"
                                :input-attr="{
                                    remoteUrl: '/admin/cms.memberGroup/getGtypeList',
                                    pk: 'value',
                                    field: 'label',
                                }"
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.gtype') })"
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
                                :label="t('cms.content.subscode')"
                                type="remoteSelect"
                                v-model="baTable.form.items!.subscode"
                                prop="subscode"
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.subscode') })"
                                :input-attr="{
                                    pk: 'content_sort.id',
                                    params: {
                                        select: true,
                                        order: 'sorting,asc',
                                        istop: 0,
                                        search: [{ field: 'mcode', operator: '=', val: mcode }],
                                    },
                                    field: 'name',
                                    remoteUrl: '/admin/cms.ContentSort/index',
                                }"
                            />

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
                                :style="{ width: '50%' }"
                                v-model="baTable.form.items!.subtitle"
                                prop="subtitle"
                                :placeholder="t('Please input field', { field: t('cms.content.subtitle') })"
                            />
                            <FormItem
                                :label="t('cms.content.filename')"
                                type="string"
                                v-model="baTable.form.items!.filename"
                                prop="filename"
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.filename') })"
                            />

                            <FormItem
                                :label="t('cms.content.outlink')"
                                type="string"
                                v-model="baTable.form.items!.outlink"
                                prop="outlink"
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.outlink') })"
                            />

                            <FormItem
                                :label="t('cms.content.insufficientPermissionPrompt')"
                                type="string"
                                :style="{ width: '50%' }"
                                v-model="baTable.form.items!.gnote"
                                prop="gnote"
                                :placeholder="t('Please input field', { field: t('cms.content.insufficientPermissionPrompt') })"
                            />

                            <FormItem
                                :label="t('cms.content.date')"
                                type="datetime"
                                v-model="baTable.form.items!.date"
                                prop="date"
                                :style="{ width: '50%' }"
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
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.keywords') })"
                            />
                            <FormItem
                                :label="t('cms.content.description')"
                                type="textarea"
                                v-model="baTable.form.items!.description"
                                prop="description"
                                :style="{ width: '50%' }"
                                :placeholder="t('Please input field', { field: t('cms.content.description') })"
                            />
                            <FormItem
                                :label="t('cms.content.sorting')"
                                type="number"
                                prop="sorting"
                                :input-attr="{ step: 1 }"
                                :style="{ width: '50%' }"
                                v-model.number="baTable.form.items!.sorting"
                                :placeholder="t('Please input field', { field: t('cms.content.sorting') })"
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
