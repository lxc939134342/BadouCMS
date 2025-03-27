<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog class="ba-operate-dialog" :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)" @close="baTable.toggleForm" width="50%">
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div class="ba-operate-form" :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'">
                <el-form v-if="!baTable.form.loading" ref="formRef" @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)" :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'" :rules="rules">

                    <!-- <FormItem :label="t('cms.slide.acode')" type="string" v-model="baTable.form.items!.acode"
                        prop="acode" :placeholder="t('Please input field', { field: t('cms.slide.acode') })" /> -->


                    <FormItem :label="t('cms.slide.gid')" type="remoteSelect" prop="gid" required="true"
                        v-model="baTable.form.items!.gid" :input-attr="{
                            multiple: false, //多选
                            pk: 'gid',
                            field: 'gid_text',
                            remoteUrl: url + 'add',
                            placeholder: t('Please select field', { field: t('cms.slide.gid_text') }),
                        }" />

                    <FormItem :label="t('cms.slide.pic')" type="image" v-model="baTable.form.items!.pic" prop="pic"
                        :placeholder="t('Please input field', { field: t('cms.slide.pic') })" required="true" />
                    <FormItem :label="t('cms.slide.link')" type="string" v-model="baTable.form.items!.link" prop="link"
                        :placeholder="t('Please input field', { field: t('cms.slide.link') })" />
                    <FormItem :label="t('cms.slide.title')" type="string" v-model="baTable.form.items!.title"
                        prop="title" :placeholder="t('Please input field', { field: t('cms.slide.title') })" />
                    <FormItem :label="t('cms.slide.subtitle')" type="string" v-model="baTable.form.items!.subtitle"
                        prop="subtitle" :placeholder="t('Please input field', { field: t('cms.slide.subtitle') })" />
                    <FormItem :label="t('cms.slide.sorting')" type="number" prop="sorting" :input-attr="{ step: 1 }"
                        v-model.number="baTable.form.items!.sorting"
                        :placeholder="t('Please input field', { field: t('cms.slide.sorting') })" />
                    <!-- <FormItem :label="t('cms.slide.create_user')" type="string" v-model="baTable.form.items!.create_user" prop="create_user" :placeholder="t('Please input field', { field: t('cms.slide.create_user') })" />
                    <FormItem :label="t('cms.slide.update_user')" type="string" v-model="baTable.form.items!.update_user" prop="update_user" :placeholder="t('Please input field', { field: t('cms.slide.update_user') })" /> -->
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm()">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)"
                    type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') :
                        t('Save') }}
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
import { url } from '/@/api/backend/cms/Slide'

const config = useConfig()
const formRef = ref<FormInstance>()
const baTable = inject('baTable') as baTableClass

//console.info(baTable.table.data)

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    gid: [buildValidatorData({ name: 'required', title: t('cms.slide.gid') })],
    pic: [buildValidatorData({ name: 'required', title: t('cms.slide.pic') })],
})


const value = ref('')


</script>

<style scoped lang="scss"></style>
