<template>
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['info'].includes(baTable.form.operate!)"
        @close="close"
        @open="open"
        width="50%"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t('user.user.info') : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.User name') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.username }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.nickname') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.nickname }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.grouping') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.group?.name || '-' }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.head portrait') }}
                </el-col>
                <el-col :span="18">
                    <div class="avatar" v-if="baTable.form.items!.avatar" style="padding: 10px 0">
                        <img :src="fullUrl(baTable.form.items!.avatar)" style="width: 50px; height: 50px; object-fit: contain" />
                    </div>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.level') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items?.member_group?.gname || '-' }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.email') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.email || '-' }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.mobile') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.mobile || '-' }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.Gender') }}
                </el-col>
                <el-col :span="18">
                    {{
                        baTable.form.items!.gender == 1
                            ? t('user.user.male')
                            : baTable.form.items!.gender == 2
                              ? t('user.user.female')
                              : t('user.user.unknown')
                    }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.birthday') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.birthday }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.balance') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.money }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.Personal signature') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.motto }}
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    {{ t('user.user.Status') }}
                </el-col>
                <el-col :span="18">
                    {{ baTable.form.items!.status == 'enable' ? t('user.user.enable') : t('user.user.disable') }}
                </el-col>
            </el-row>
            <template v-if="memberFieldList.length > 0">
                <el-divider content-position="left">自定义字段</el-divider>

                <el-row v-for="item in memberFieldList" :key="item.id">
                    <el-col :span="6">
                        {{ item.description }}
                    </el-col>
                    <el-col :span="18">
                        {{ baTable.form.items![item.name] || '-' }}
                    </el-col>
                </el-row>
            </template>
        </el-scrollbar>
        <template #footer>
            <el-button type="primary" @click="close">{{ t('user.user.Close') }}</el-button>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import type baTableClass from '/@/utils/baTable'
import { fullUrl } from '/@/utils/common'
import { useI18n } from 'vue-i18n'
import { getList as getMemberFieldList } from '/@/api/backend/cms/MemeberField'

const { t } = useI18n()
const baTable = inject('baTable') as baTableClass
const memberFieldList = ref<any[]>([])

const close = () => {
    baTable.form.operate = ''
}
const open = () => {
    getMemberFieldList().then((res) => {
        if (res.code == 1) {
            memberFieldList.value = res.data.list
            console.log(memberFieldList.value)
        }
    })
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
