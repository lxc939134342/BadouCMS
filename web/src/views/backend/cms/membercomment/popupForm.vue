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
                        :label="t('cms.membercomment.contentid')"
                        type="number"
                        prop="contentid"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.contentid"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.contentid') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.comment')"
                        type="string"
                        v-model="baTable.form.items!.comment"
                        prop="comment"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.comment') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.uid')"
                        type="number"
                        prop="uid"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.uid"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.uid') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.puid')"
                        type="number"
                        prop="puid"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.puid"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.puid') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.likes')"
                        type="number"
                        prop="likes"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.likes"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.likes') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.oppose')"
                        type="number"
                        prop="oppose"
                        :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.oppose"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.oppose') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.status')"
                        type="radio"
                        v-model="baTable.form.items!.status"
                        prop="status"
                        :input-attr="{ content: {} }"
                        :placeholder="t('Please select field', { field: t('cms.membercomment.status') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.user_ip')"
                        type="string"
                        v-model="baTable.form.items!.user_ip"
                        prop="user_ip"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.user_ip') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.user_os')"
                        type="string"
                        v-model="baTable.form.items!.user_os"
                        prop="user_os"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.user_os') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.user_bs')"
                        type="string"
                        v-model="baTable.form.items!.user_bs"
                        prop="user_bs"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.user_bs') })"
                    />
                    <FormItem
                        :label="t('cms.membercomment.update_user')"
                        type="string"
                        v-model="baTable.form.items!.update_user"
                        prop="update_user"
                        :placeholder="t('Please input field', { field: t('cms.membercomment.update_user') })"
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
    pid: [buildValidatorData({ name: 'number', title: t('cms.membercomment.pid') })],
    contentid: [buildValidatorData({ name: 'number', title: t('cms.membercomment.contentid') })],
    uid: [buildValidatorData({ name: 'number', title: t('cms.membercomment.uid') })],
    puid: [buildValidatorData({ name: 'number', title: t('cms.membercomment.puid') })],
    likes: [buildValidatorData({ name: 'number', title: t('cms.membercomment.likes') })],
    oppose: [buildValidatorData({ name: 'number', title: t('cms.membercomment.oppose') })],
    create_time: [buildValidatorData({ name: 'date', title: t('cms.membercomment.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('cms.membercomment.update_time') })],
})
</script>

<style scoped lang="scss"></style>
