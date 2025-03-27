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
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
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
                    <FormItem
                        :label="t('cms.memberfield.description')"
                        type="string"
                        v-model="baTable.form.items!.description"
                        prop="description"
                        :placeholder="t('Please input field', { field: t('cms.memberfield.description') })"
                    />
                    <FormItem
                        :label="t('cms.memberfield.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('cms.memberfield.name') })"
                        :block-help="t('cms.memberfield.nameHelp')"
                    />
                    <FormItem
                        :label="t('cms.memberfield.length')"
                        type="number"
                        prop="length"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.length"
                        :placeholder="t('Please input field', { field: t('cms.memberfield.length') })"
                    />
                    <FormItem
                        :label="t('cms.memberfield.required')"
                        type="switch"
                        v-model="baTable.form.items!.required"
                        prop="required"
                        :placeholder="t('Please input field', { field: t('cms.memberfield.required') })"
                    />

                    <FormItem
                        :label="t('cms.memberfield.sorting')"
                        type="number"
                        prop="sorting"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.sorting"
                        :placeholder="t('Please input field', { field: t('cms.memberfield.sorting') })"
                    />
                    <FormItem
                        :label="t('cms.memberfield.status')"
                        type="switch"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :placeholder="t('Please select field', { field: t('cms.memberfield.status') })"
                    />
                </el-form>
            </div>
        </el-scrollbar>
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

const config = useConfig()
const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

/* 验证字段名称 */
function validateName(rule: any, val: string, callback: Function) {
    if (/^[a-zA-Z][a-zA-Z0-9_]*$/.test(val)) {
        callback()
    }
    callback(new Error(t('cms.memberfield.nameHelp')))
}
const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('cms.memberfield.name') }), { validator: validateName }],
    description: [buildValidatorData({ name: 'required', title: t('cms.memberfield.description') })],
    length: [buildValidatorData({ name: 'number', title: t('cms.memberfield.length') })],
    sorting: [buildValidatorData({ name: 'number', title: t('cms.memberfield.sorting') })],
})
</script>

<style scoped lang="scss"></style>
