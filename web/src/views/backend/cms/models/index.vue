<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.models.name') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"></Table>

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import { add, url } from '/@/api/backend/cms/Models'
import { getArrayKey } from '/@/utils/common'

defineOptions({
    name: 'cms/models',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi(url),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.models.id'), prop: 'mcode', align: 'center', width: 90, operator: 'RANGE', sortable: 'custom' },
            { label: t('cms.models.name'), prop: 'name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: t('cms.models.type'),
                prop: 'type_text',
                render: 'tag',
                custom: { 单页: 'primary', 列表: 'warning' },
                align: 'center',
                operator: 'eq',
                sortable: false,
            },
            {
                label: t('cms.models.urlname'),
                prop: 'urlname',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.models.listtpl'),
                prop: 'listtpl',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.models.contenttpl'),
                prop: 'contenttpl',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('cms.models.status'), prop: 'status', align: 'center', render: 'switch', operator: 'eq', sortable: false, replaceValue: {} },
            {
                label: t('cms.models.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { status: '1' },
    },
    {
        toggleForm({ operate, operateIds }) {
            if (operate == 'Add' || operate == 'Edit') {
                baTable.form.loading = true
                add().then((res) => {
                    baTable.form.extend!.typeList = res.data.typeList
                    baTable.form.loading = false
                })
            }
        },
    }
)

/*禁止删除*/
let btnIndex = getArrayKey(baTable.table.column, 'render', 'buttons') // 找到 render=buttons 的列
//找到表格表头 第 9 列
// console.log(btnIndex)
baTable.table.column[btnIndex].buttons![1].display = (row: TableRow) => {
    // console.log(row)
    // console.log(row.issystem != 1)
    return row.issystem != 1
}

// console.info(baTable.table.column[btnIndex].buttons![0])
// console.info(baTable.table.column[btnIndex].buttons![1])

baTable.table.column[0].selectable = (row: TableRow) => {
    // console.info(row)
    return row.issystem != 1
}

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
