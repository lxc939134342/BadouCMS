<template>
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['info'].includes(baTable.form.operate!)"
        @close="close"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t('cms.membercomment.info') : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.contentid') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.content.title }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.comment') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.comment }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.status') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.status_text }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.uid') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.user.nickname }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.puid') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items?.puser?.nickname || '-' }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.likes') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.likes }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.oppose') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.oppose }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.user_ip') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.user_ip }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.user_os') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.user_os }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.user_bs') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.user_bs }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('cms.membercomment.create_time') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.create_time }}
                </el-col>
            </el-row>
        </el-scrollbar>
        <template #footer>
            <el-button type="primary" @click="close">{{ t('cms.membercomment.close') }}</el-button>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import type baTableClass from '/@/utils/baTable'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()
const baTable = inject('baTable') as baTableClass

const close = () => {
    baTable.form.operate = ''
}
</script>

<style scoped>
.el-row {
    border-bottom: 1px solid #ebeef5;
    line-height: 40px;
}

.el-col:first-child {
    color: #606266;
    background-color: #f5f7fa;
    padding-left: 15px;
}

.el-col:last-child {
    padding-left: 15px;
}
</style>
