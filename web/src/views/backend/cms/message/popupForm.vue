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
                {{ baTable.form.operate ? t(`cms.message.reply`) : '' }}
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
                    <template v-for="(item, index) in formFields">
                        <FormItem
                            type="string"
                            :label="item!.description"
                            v-model="baTable.form.items![item.name]"
                            :prop="item!.name"
                            :input-attr="{ disabled: baTable.form.operate === 'Edit' }"
                            :placeholder="t('Please input field', { field: item.description })"
                        />
                    </template>

                    <FormItem
                        :label="t('cms.message.user_ip')"
                        :input-attr="{ disabled: baTable.form.operate == 'Edit' }"
                        type="string"
                        v-model="baTable.form.items!.user_ip"
                        prop="user_ip"
                        :placeholder="t('Please input field', { field: t('cms.message.user_ip') })"
                    />
                    <FormItem
                        :label="t('cms.message.user_os')"
                        :input-attr="{ disabled: baTable.form.operate == 'Edit' }"
                        type="string"
                        v-model="baTable.form.items!.user_os"
                        prop="user_os"
                        :placeholder="t('Please input field', { field: t('cms.message.user_os') })"
                    />
                    <FormItem
                        :label="t('cms.message.user_bs')"
                        :input-attr="{ disabled: baTable.form.operate == 'Edit' }"
                        type="string"
                        v-model="baTable.form.items!.user_bs"
                        prop="user_bs"
                        :placeholder="t('Please input field', { field: t('cms.message.user_bs') })"
                    />
                    <FormItem
                        :label="t('cms.message.create_time')"
                        :input-attr="{ disabled: baTable.form.operate == 'Edit' }"
                        type="string"
                        v-model="baTable.form.items!.create_time"
                        prop="create_time"
                        :placeholder="t('Please input field', { field: t('cms.message.create_time') })"
                    />

                    <FormItem
                        :label="t('cms.message.recontent')"
                        type="textarea"
                        v-model="baTable.form.items!.recontent"
                        prop="recontent"
                        :placeholder="t('Please input field', { field: t('cms.message.recontent') })"
                    />

                    <FormItem
                        :label="t('cms.message.status')"
                        type="radio"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :input-attr="{ content: { '1': '显示', '0': '隐藏' } }"
                        :placeholder="t('Please select field', { field: t('cms.message.status') })"
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

import { getFormFieldList } from '/@/api/backend/cms/FormField'

const config = useConfig()
const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

interface FormField {
    id: number
    fcode: string
    name: string
    length: number
    required: string
    description: string
    sorting: number
}

const formFields = ref<FormField[]>([])
getFormFieldList(1).then((res) => {
    if (res.code == 1) {
        formFields.value = res.data.list
    }
})

const { t } = useI18n()
const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    recontent: [buildValidatorData({ name: 'required', title: t('cms.message.recontent') })],
    uid: [buildValidatorData({ name: 'number', title: t('cms.message.uid') })],
})
</script>

<style scoped lang="scss"></style>
