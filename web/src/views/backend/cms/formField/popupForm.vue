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
                        :label="t('cms.formField.description')"
                        type="string"
                        v-model="baTable.form.items!.description"
                        prop="description"
                        :placeholder="t('Please input field', { field: t('cms.formField.description') })"
                    />
                    <FormItem
                        :label="t('cms.formField.name')"
                        disabled
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('cms.formField.name') })"
                        :input-attr="{ disabled: baTable.form.operate === 'Edit' }"
                    />
                    <FormItem
                        :label="t('cms.formField.length')"
                        disabled
                        type="number"
                        prop="length"
                        :input-attr="{ step: 1, disabled: baTable.form.operate === 'Edit' }"
                        v-model.number="baTable.form.items!.length"
                        :placeholder="t('Please input field', { field: t('cms.formField.length') })"
                    />
                    <FormItem
                        :label="t('cms.formField.required')"
                        type="radio"
                        v-model="baTable.form.items!.required"
                        prop="required"
                        :input-attr="{ content: { '1': t('cms.formField.yes'), '0': t('cms.formField.no') } }"
                        :placeholder="t('Please input field', { field: t('cms.formField.required') })"
                    />

                    <FormItem
                        :label="t('cms.formField.sorting')"
                        type="number"
                        prop="sorting"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.sorting"
                        :placeholder="t('Please input field', { field: t('cms.formField.sorting') })"
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

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    description: [buildValidatorData({ name: 'required', title: t('cms.formField.description') })],
    name: [buildValidatorData({ name: 'required', title: t('cms.formField.name') })],
    length: [buildValidatorData({ name: 'required', title: t('cms.formField.length') })],
    sorting: [buildValidatorData({ name: 'number', title: t('cms.formField.sorting') })],
})
</script>

<style scoped lang="scss"></style>
