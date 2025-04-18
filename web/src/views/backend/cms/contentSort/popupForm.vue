<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>

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
                <el-tab-pane class="basic-tab-pane" name="basic" :label="t('cms.contentSort.basic')">
                    <div
                        class="ba-operate-form"
                        :class="'ba-' + baTable.form.operate + '-form'"
                        :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
                    >
                        <FormItem
                            :label="t('cms.contentSort.pcode')"
                            type="remoteSelect"
                            v-model="baTable.form.items!.pcode"
                            prop="pcode"
                            :input-attr="{
                                pk: 'content_sort.scode',
                                params: { select: true, order: 'sorting,asc', acode: baTable.table.filter!.acode },
                                field: 'name',
                                remoteUrl: indexUrl,
                                placeholder: t('cms.contentSort.Please select the column'),
                            }"
                        />

                        <FormItem
                            :label="t('cms.contentSort.name')"
                            type="string"
                            v-model="baTable.form.items!.name"
                            prop="name"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.name') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.filename')"
                            type="string"
                            v-model="baTable.form.items!.filename"
                            prop="filename"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.filename') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.mcode')"
                            type="remoteSelect"
                            v-model="baTable.form.items!.mcode"
                            prop="mcode"
                            :input-attr="{
                                pk: 'mcode',
                                field: 'name',
                                remoteUrl: url + 'models',
                                placeholder: t('Please select field', { field: t('cms.contentSort.mcode') }),
                                onRow: onModelsRow,
                            }"
                        />
                        <FormItem
                            :label="t('cms.contentSort.listtpl')"
                            type="remoteSelect"
                            v-model="baTable.form.items!.listtpl"
                            prop="listtpl"
                            :input-attr="{
                                remoteUrl: url + 'getTpls',
                            }"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.listtpl') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.contenttpl')"
                            type="remoteSelect"
                            v-model="baTable.form.items!.contenttpl"
                            prop="contenttpl"
                            :input-attr="{
                                remoteUrl: url + 'getTpls',
                            }"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.contenttpl') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.status')"
                            type="radio"
                            v-model="baTable.form.items!.status"
                            prop="status"
                            :input-attr="{ content: { '1': '显示', '0': '隐藏' } }"
                            :placeholder="t('Please select field', { field: t('cms.contentSort.status') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.gid')"
                            type="remoteSelect"
                            v-model="baTable.form.items!.gid"
                            prop="gid"
                            :input-attr="{
                                remoteUrl: '/admin/cms.memberGroup/getUserLevel',
                                pk: 'gcode',
                                field: 'gname',
                            }"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.gid') })"
                        />

                        <FormItem
                            :label="t('cms.contentSort.gtype')"
                            type="remoteSelect"
                            v-model="baTable.form.items!.gtype"
                            prop="gtype"
                            :input-attr="{
                                remoteUrl: '/admin/cms.memberGroup/getGtypeList',
                                pk: 'value',
                                field: 'label',
                            }"
                            :placeholder="t('Please select field', { field: t('cms.contentSort.gtype') })"
                        />
                    </div>
                </el-tab-pane>
                <el-tab-pane name="senior" class="senior-tab-pane" :label="t('cms.contentSort.senior')">
                    <div
                        class="ba-operate-form"
                        :class="'ba-' + baTable.form.operate + '-form'"
                        :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
                    >
                        <FormItem
                            :label="t('cms.contentSort.subname')"
                            type="string"
                            v-model="baTable.form.items!.subname"
                            prop="subname"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.subname') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.def1')"
                            type="string"
                            v-model="baTable.form.items!.def1"
                            prop="def1"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.def1') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.def2')"
                            type="string"
                            v-model="baTable.form.items!.def2"
                            prop="def2"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.def2') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.def3')"
                            type="string"
                            v-model="baTable.form.items!.def3"
                            prop="def3"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.def3') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.outlink')"
                            type="string"
                            v-model="baTable.form.items!.outlink"
                            prop="outlink"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.outlink') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.ico')"
                            type="image"
                            v-model="baTable.form.items!.ico"
                            prop="ico"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.ico') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.pic')"
                            type="image"
                            v-model="baTable.form.items!.pic"
                            prop="pic"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.pic') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.title')"
                            type="string"
                            v-model="baTable.form.items!.title"
                            prop="title"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.title') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.keywords')"
                            type="string"
                            v-model="baTable.form.items!.keywords"
                            prop="keywords"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.keywords') })"
                        />
                        <FormItem
                            :label="t('cms.contentSort.description')"
                            type="textarea"
                            v-model="baTable.form.items!.description"
                            prop="description"
                            :placeholder="t('Please input field', { field: t('cms.contentSort.description') })"
                        />
                    </div>
                </el-tab-pane>
            </el-tabs>
        </el-form>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
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
import { url } from '/@/api/backend/cms/ContentSort'

const config = useConfig()
const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass
const indexUrl = baTable.api.actionUrl.get('index')

const state: { activeTab: string } = reactive({
    activeTab: 'basic',
})

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('cms.contentSort.name') })],
    mcode: [buildValidatorData({ name: 'required', title: t('cms.contentSort.mcode') })],
    sorting: [buildValidatorData({ name: 'number', title: t('cms.contentSort.sorting') })],
    create_time: [buildValidatorData({ name: 'date', title: t('cms.contentSort.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('cms.contentSort.update_time') })],
})

/* 根据模型切换模板 */
const onModelsRow = (row: any) => {
    baTable.form.items!.listtpl = row.listtpl
    baTable.form.items!.contenttpl = row.contenttpl
}
</script>

<style scoped lang="scss">
.el-tabs {
    margin-top: 10px;
    .el-tab-pane {
        height: 50vh;
        overflow-y: auto;
    }
    .ba-operate-form {
        padding-top: 10px;
    }
}
</style>
