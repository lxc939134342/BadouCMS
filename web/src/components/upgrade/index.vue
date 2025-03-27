<template>
    <div>
        <el-dialog v-model="badoucms.upgradeDialog" :title="t('upgrade.Upgrade')" @open="open" @close="close" class="ba-terminal-dialog main-dialog">
            <el-scrollbar ref="terminalScrollbarRef" :max-height="500" class="terminal-scrollbar">
                <el-alert
                    class="terminal-warning-alert"
                    :title="t('upgrade.Please back up your data and programs before upgrading')"
                    type="error"
                    :closable="false"
                />
                <el-timeline class="terminal-timeline" v-if="state.list.length > 0">
                    <el-timeline-item v-for="(item, index) in state.list" :key="index" class="task-item" :type="'success'" center placement="top">
                        <el-card>
                            <div>
                                <el-tag :type="'success'">{{ item.software.title }}</el-tag>
                                <span class="command">V{{ item?.version }}</span>
                                <div class="task-opt">
                                    <el-button
                                        :title="t('upgrade.Start upgrading')"
                                        size="small"
                                        v-blur
                                        type="primary"
                                        @click="update(item.version)"
                                        >{{ t('upgrade.Start upgrading') }}</el-button
                                    >
                                </div>
                            </div>
                            <div @click="item.showMessage = !item.showMessage" class="toggle-message-display">
                                <span>更新日志</span>
                                <Icon :name="item?.showMessage ? 'el-icon-ArrowUp' : 'el-icon-ArrowDown'" size="16" color="#909399" />
                            </div>
                            <div class="exec-message" v-if="item?.showMessage">
                                {{ item?.remark }}
                            </div>
                        </el-card>
                    </el-timeline-item>
                </el-timeline>
                <el-empty :image-size="80" v-else :description="t('upgrade.The current program is the latest')" />
            </el-scrollbar>

            <div class="terminal-buttons"></div>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { ElScrollbar, ElNotification } from 'element-plus'
import { reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { getList, upgrade, backup } from '/@/api/backend/upgrade'
import { useBadoucms } from '/@/stores/badoucms'

const { t } = useI18n()
const badoucms = useBadoucms()

interface ListItem {
    software: {
        title: string
    }
    version: string
    remark?: string
    showMessage: boolean
}
interface State {
    list: ListItem[]
}
const state: State = reactive({
    list: [],
})
const close = () => {
    badoucms.upgradeDialog = false
}

const open = () => {
    getList().then((res) => {
        if (res.code == 1) {
            state.list = res.data.map((item: ListItem) => ({
                ...item,
                showMessage: false, // 或者你可以设置为其他初始值
            }))
        }
    })
}

const update = (version: string) => {
    /* 先备份 */
    backup().then((res) => {
        if (res.code == 1) {
            upgrade({ version }).then((res) => {
                if (res.code == 1) {
                    badoucms.upgradeDialog = false
                    ElNotification.success({
                        title: t('upgrade.Upgrade success'),
                        message: t('upgrade.Upgrade success'),
                    })
                    setTimeout(() => {
                        // 刷新页面
                        window.location.reload()
                    }, 1000)
                }
            })
        }
    })
}
</script>

<style scoped lang="scss">
.terminal-warning-alert {
    margin: 0 0 20px 0;
}
.terminal-timeline {
    padding: 0 15px;
}
.command {
    font-size: var(--el-font-size-large);
    font-weight: bold;
    margin-left: 10px;
}
.exec-message {
    color: var(--ba-bg-color-overlay);
    font-size: 12px;
    line-height: 16px;
    padding: 6px;
    background-color: #424251;
    margin-top: 10px;
    min-height: 30px;
    max-height: 200px;
    overflow: auto;
    &::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    &::-webkit-scrollbar-thumb {
        background: #c8c9cc;
        border-radius: 4px;
        box-shadow: none;
        -webkit-box-shadow: none;
    }
    &::-webkit-scrollbar-track {
        background: var(--ba-bg-color);
    }
    &:hover {
        &::-webkit-scrollbar-thumb:hover {
            background: #909399;
        }
    }
}
@supports not (selector(::-webkit-scrollbar)) {
    .exec-message {
        scrollbar-width: thin;
        scrollbar-color: #c8c9cc #eaeaea;
    }
}
.toggle-message-display {
    padding-top: 10px;
    font-size: 13px;
    color: var(--el-text-color-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.task-opt {
    float: right;
}
.task-item.task-status-0:hover,
.task-item.task-status-3:hover,
.task-item.task-status-4:hover,
.task-item.task-status-5:hover {
    .task-opt {
        display: inline;
    }
}
.block-on-failure-tag {
    margin-left: 10px;
}
.terminal-menu-item {
    margin-bottom: 12px;
}
.terminal-menu-item + .terminal-menu-item {
    margin-left: 12px;
    margin-bottom: 12px;
}
.terminal-buttons {
    display: block;
    width: fit-content;
    margin: 0 auto;
    padding-top: 12px;
}
.config-buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-top: 20px;
    padding-right: 20px;
}
:deep(.main-dialog) {
    --el-dialog-padding-primary: 16px 16px 0 16px;
    .el-dialog__body {
        margin-top: 16px;
    }
}
:deep(.ba-terminal-dialog) {
    --el-dialog-width: 46% !important;
    .el-loading-spinner {
        --el-loading-spinner-size: 20px;
    }
}
@media screen and (max-width: 768px) {
    :deep(.ba-terminal-dialog) {
        --el-dialog-width: 80% !important;
    }
}
@media screen and (max-width: 540px) {
    :deep(.ba-terminal-dialog) {
        --el-dialog-width: 94% !important;
    }
}
</style>
