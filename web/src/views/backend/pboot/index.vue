<template>
    <div class="default-main ba-table-box">
        <el-card class="table-main" shadow="never">
            <div class="table-header">
                <h3 class="table-header-title">PbootCMS数据迁移</h3>
            </div>
            <div class="table-main-content" style="margin-top: 20px">
                <el-alert type="success">
                    <p>1、请先将pbootcms程序复制到BadouCms的跟目录下</p>
                    <p>2、在下方的PbootCMS文件夹中填入刚刚复制过来的pbootcms程序名称</p>
                    <p>3、迁移过程中请勿刷新页面或关闭浏览器。</p>
                </el-alert>
                <div class="config-form" v-if="!configVerified">
                    <el-form :model="configForm" label-width="150px">
                        <el-form-item label="PbootCMS文件夹">
                            <el-input v-model="configForm.pbootPath" placeholder="请输入PbootCMS的文件夹名称" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="verifyConfig" :loading="verifying">验证配置</el-button>
                        </el-form-item>
                    </el-form>
                </div>
                <div v-if="configVerified">
                    <div class="config-info">
                        <el-alert :title="'已成功连接到 ' + (dbType === 'mysql' ? 'MySQL' : 'SQLite') + ' 数据库'" type="success" show-icon />
                    </div>
                    <div class="table-list">
                        <el-table :data="tables" style="width: 100%">
                            <el-table-column prop="name" label="表名" />
                            <el-table-column prop="target" label="目标表名" />
                            <el-table-column prop="type" label="类型">
                                <template #default="scope">
                                    <el-tag :type="scope.row.type === 'system' ? '' : 'success'">{{
                                        scope.row.type === 'system' ? '系统表' : '自定义表'
                                    }}</el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column prop="count" label="记录数" />
                            <el-table-column prop="status" label="状态">
                                <template #default="scope">
                                    <el-tag :type="getStatusType(scope.row.status)">{{ getStatusText(scope.row.status) }}</el-tag>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                    <div class="migration-actions">
                        <el-button type="primary" @click="startMigration" :loading="migrating" :disabled="!hasUnmigratedTables">
                            {{ hasUnmigratedTables ? (migrating ? '迁移中...' : '开始迁移全部') : '迁移完成' }}
                        </el-button>
                    </div>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { migrate, checkConfig, getTables, migrateFiles } from '/@/api/backend/pboot'
import { backupDb } from '/@/api/backend/backupdb'

const configForm = ref({
    pbootPath: '',
})
const configVerified = ref(false)
const verifying = ref(false)
const dbType = ref('')
const migrating = ref(false)
const tables = ref<any[]>([])

const hasUnmigratedTables = computed(() => {
    return tables.value.some((table) => table.status !== 'success')
})

const getStatusType = (status: string) => {
    switch (status) {
        case 'success':
            return 'success'
        case 'error':
            return 'danger'
        case 'pending':
            return 'info'
        default:
            return ''
    }
}

const getStatusText = (status: string) => {
    switch (status) {
        case 'success':
            return '已完成'
        case 'error':
            return '失败'
        case 'pending':
            return '待迁移'
        default:
            return '待迁移'
    }
}

const loadTables = async () => {
    try {
        const res: any = await getTables(configForm.value)
        if (res.code === 1) {
            tables.value = res.data.map((table: any) => ({
                ...table,
                status: 'pending',
                migrating: false,
            }))
        } else {
            ElMessage.error(res.msg || '获取表列表失败')
        }
    } catch (error: any) {
        ElMessage.error(error.message || '获取表列表失败')
    }
}

const verifyConfig = async () => {
    if (!configForm.value.pbootPath) {
        ElMessage.warning('请输入PbootCMS目录路径')
        return
    }
    try {
        verifying.value = true
        const res: any = await checkConfig(configForm.value)
        if (res.code === 1) {
            configVerified.value = true
            dbType.value = res.data.type
            ElMessage.success(res.msg)
            await loadTables()
        } else {
            ElMessage.error(res.msg)
        }
    } catch (error: any) {
        ElMessage.error(error.message || '配置验证失败')
    } finally {
        verifying.value = false
    }
}

const startMigration = async () => {
    if (!configVerified.value) {
        ElMessage.warning('请先验证PbootCMS配置')
        return
    }
    try {
        migrating.value = true
        //先备份数据库
        const backupRes: any = await backupDb()
        if (backupRes.code !== 1) {
            ElMessage.error(backupRes.msg || '备份失败')
            return
        }
        // 已移除不再需要的pendingTables定义
        for (const table of tables.value.filter((t) => t.status === 'pending')) {
            try {
                const res: any = await migrate({
                    ...configForm.value,
                    tableName: table.name,
                })
                if (res.code === 1) {
                    table.status = 'success'
                }
            } catch {
                table.status = 'error'
            }
        }
        migrating.value = false

        //迁移数据成功后再迁移文件
        const fileRes: any = await migrateFiles(configForm.value)
        if (fileRes.code !== 1) {
            ElMessage.error(fileRes.msg)
        }
    } catch (error: any) {
        migrating.value = false
        ElMessage.error(error.message || '迁移失败')
    }
}
</script>

<style scoped lang="scss">
.migration-info {
    margin-bottom: 20px;
    p {
        margin: 5px 0;
        color: #666;
    }
}

.migration-progress {
    margin: 20px 0;
    .progress-info {
        margin-top: 10px;
        color: #666;
    }
}

.config-form {
    margin: 20px 0;
}

.config-info {
    margin: 20px 0;
}

.migration-actions {
    margin-top: 20px;
}
</style>
