<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.operate == 'BatchAdd'"
        @close="baTable.toggleForm"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t('cms.contentSort.batchadd') : '' }}
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
                    :placeholder="t('cms.contentSort.batchaddname')"
                />
                <FormItem
                    :label="t('cms.contentSort.mcode')"
                    type="remoteSelect"
                    v-model="baTable.form.items!.mcode"
                    prop="mcode"
                    :input-attr="{
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
            </div>
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
import { cloneDeep } from 'lodash-es'

const baTable = inject('baTable') as baTableClass
const indexUrl = baTable.api.actionUrl.get('index')

const config = useConfig()
const formRef = ref<FormInstance>()

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('cms.contentSort.name') })],
    mcode: [buildValidatorData({ name: 'required', title: t('cms.contentSort.mcode') })],
    sorting: [buildValidatorData({ name: 'number', title: t('cms.contentSort.sorting') })],
})
/* 根据模型切换模板 */
const onModelsRow = (row: any) => {
    baTable.form.items!.listtpl = row.listtpl
    baTable.form.items!.contenttpl = row.contenttpl
}
console.log(baTable.form)
if (baTable.form.operate == 'BatchAdd') {
    baTable.onSubmit = (formEl: FormInstance | undefined = undefined) => {
        if (
            baTable.runBefore('onSubmit', {
                formEl: formEl,
                operate: 'BatchAdd',
                items: baTable.form.items!,
            }) === false
        )
            return

        Object.keys(baTable.form.items!).forEach((item) => {
            if (baTable.form.items![item] === null) delete baTable.form.items![item]
        })

        // 表单验证通过后执行的api请求操作
        const submitCallback = () => {
            baTable.form.submitLoading = true
        }

        if (formEl) {
            baTable.form.ref = formEl
            formEl.validate((valid: boolean) => {
                if (valid) {
                    submitCallback()
                }
            })
        } else {
            submitCallback()
        }
    }
}
</script>
