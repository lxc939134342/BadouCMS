<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader :buttons="['refresh', 'columnDisplay']">
            <el-tooltip :content="t('backupdb.table header backups the database')" placement="top">
                <el-button
                    @click="handleBackupTable"
                    :disabled="baTable.table.selection!.length > 0 ? false : true"
                    v-blur
                    class="table-header-operate"
                    type="primary"
                >
                    <Icon color="#ffffff" name="el-icon-Coin" />
                    <span class="table-header-operate-text">{{ t('backupdb.button backups table') }}</span>
                </el-button>
            </el-tooltip>
            <el-tooltip :content="t('backupdb.table header optimize the database')" placement="top">
                <el-button
                    @click="handleOptimizeTable"
                    :disabled="baTable.table.selection!.length > 0 ? false : true"
                    v-blur
                    class="table-header-operate"
                    type="warning"
                >
                    <Icon color="#ffffff" name="el-icon-Finished" />
                    <span class="table-header-operate-text">{{ t('backupdb.button optimize table') }}</span>
                </el-button>
            </el-tooltip>
            <el-tooltip :content="t('backupdb.table header repair the database')" placement="top">
                <el-button
                    @click="handleRepairTable"
                    :disabled="baTable.table.selection!.length > 0 ? false : true"
                    v-blur
                    class="table-header-operate"
                    type="success"
                >
                    <Icon color="#ffffff" name="el-icon-MagicStick" />
                    <span class="table-header-operate-text">{{ t('backupdb.button repair table') }}</span>
                </el-button>
            </el-tooltip>
            <el-tooltip :content="t('backupdb.table header backups the database')" placement="top">
                <el-button v-blur class="table-header-operate" type="primary" @click="handleBackupDb">
                    <Icon color="#ffffff" name="el-icon-Coin" />
                    <span class="table-header-operate-text">{{ t('backupdb.button backups db') }}</span>
                </el-button>
            </el-tooltip>
        </TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef" :pagination="false"> </Table>
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, ref, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import { backupTable, optimizeTable, repairTable, backupDb } from '/@/api/backend/backupdb'
import { ElMessage, ElNotification } from 'element-plus'
const { t } = useI18n()
const optButtons: OptButton[] = []
const tableRef = ref()
/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(new baTableApi('/admin/backupdb/'), {
    pk: 'id',
    column: [
        { type: 'selection', align: 'center', operator: false },
        {
            label: t('backupdb.table name'),
            prop: 'table_name',
            operator: false,
        },
        {
            label: t('backupdb.engine'),
            prop: 'engine',
            operator: false,
            width: 100,
        },
        {
            label: t('backupdb.collation'),
            prop: 'collation',
            operator: false,
            width: 180,
        },
        {
            label: t('backupdb.version'),
            prop: 'version',
            operator: false,
            width: 80,
        },
        {
            label: t('backupdb.rows'),
            prop: 'rows',
            operator: false,
            width: 80,
        },
        {
            label: t('backupdb.create time'),
            prop: 'create_time',
            operator: false,
            width: 180,
        },
        {
            label: t('backupdb.update time'),
            prop: 'update_time',
            operator: false,
            width: 180,
        },
        {
            label: t('backupdb.size'),
            prop: 'size',
            operator: false,
            width: 100,
        },
        {
            label: t('backupdb.bredundancy'),
            prop: 'bredundancy',
            operator: false,
            width: 130,
        },
        {
            label: t('backupdb.comment'),
            prop: 'comment',
            operator: false,
            width: 200,
        },
    ],
    dblClickNotEditColumn: [undefined],
})

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})

// 备份表
const handleBackupTable = () => {
    if (baTable.table.selection!.length == 0) {
        ElMessage.warning(t('backupdb.no selected table'))
        return
    }
    const tables = baTable.table.selection!.map((item) => item.table_name)

    backupTable({ tables }).then((res) => {
        if (res.code == 1) {
            ElNotification({
                type: 'success',
                message: res.msg,
            })
            baTable.getIndex()
        }
    })
}

// 备份数据库
const handleBackupDb = () => {
    backupDb().then((res) => {
        if (res.code == 1) {
            ElNotification({
                type: 'success',
                message: res.msg,
            })
            baTable.getIndex()
        }
    })
}

// 优化表
const handleOptimizeTable = () => {
    if (baTable.table.selection!.length == 0) {
        ElMessage.warning(t('backupdb.no selected table'))
        return
    }
    const tables = baTable.table.selection!.map((item) => item.table_name)

    optimizeTable({ tables }).then((res) => {
        if (res.code == 1) {
            ElNotification({
                type: 'success',
                message: res.msg,
            })
            baTable.getIndex()
        }
    })
}

// 修复表
const handleRepairTable = () => {
    if (baTable.table.selection!.length == 0) {
        ElMessage.warning(t('backupdb.no selected table'))
        return
    }
    const tables = baTable.table.selection!.map((item) => item.table_name)

    repairTable({ tables }).then((res) => {
        if (res.code == 1) {
            ElNotification({
                type: 'success',
                message: res.msg,
            })
        }
    })
}
</script>

<style scoped lang="scss">
.table-header-operate {
    margin-left: 12px;
}
.default-main {
    margin-bottom: 0;
}
</style>
