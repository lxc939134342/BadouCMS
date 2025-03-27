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
                    <!-- <FormItem :label="t('cms.form.fcode')" type="string" v-model="baTable.form.items!.fcode" prop="fcode" :placeholder="t('Please input field', { field: t('cms.form.fcode') })" /> -->
                    <FormItem
                        :label="t('cms.form.form_name')"
                        type="string"
                        v-model="baTable.form.items!.form_name"
                        prop="form_name"
                        :placeholder="t('Please input field', { field: t('cms.form.form_name') })"
                    />
                    <FormItem
                        :label="t('cms.form.table_name')"
                        type="string"
                        v-model="baTable.form.items!.table_name"
                        prop="table_name"
                        :placeholder="t('Please input field', { field: t('cms.form.table_name') })"
                        :input-attr="{ disabled: t(baTable.form.operate) === '编辑' }"
                    />

                    <!--                    :input-attr="{ disabled: true }"-->
                    <!-- <FormItem :label="t('cms.form.create_user')" type="string" v-model="baTable.form.items!.create_user" prop="create_user" :placeholder="t('Please input field', { field: t('cms.form.create_user') })" /> -->
                    <!-- <FormItem :label="t('cms.form.update_user')" type="string" v-model="baTable.form.items!.update_user" prop="update_user" :placeholder="t('Please input field', { field: t('cms.form.update_user') })" /> -->
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

// console.log(baTable)

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    form_name: [buildValidatorData({ name: 'required', title: t('cms.form.form_name') })],
    table_name: [buildValidatorData({ name: 'required', title: t('cms.form.table_name') })],
})
</script>

<style scoped lang="scss"></style>
