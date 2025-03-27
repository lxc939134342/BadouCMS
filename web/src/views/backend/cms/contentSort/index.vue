<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'unfold', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.contentSort.name') })"
        >
            <template #refreshAppend>
                <DataLang></DataLang>
            </template>
            <template #default>
                <el-button class="table-header-operate" @click="onBatchAdd" type="primary">
                    <Icon color="#ffffff" name="el-icon-Finished" />
                    <span class="table-header-operate-text">{{ t('cms.contentSort.batchadd') }}</span>
                </el-button>
            </template>
        </TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef" :pagination="false">
            <template #name>
                <el-table-column :label="t('cms.contentSort.name')" align="left">
                    <template #default="scope">
                        {{ scope.row.name }}
                    </template>
                </el-table-column>
            </template>
            <template #sorting>
                <el-table-column :label="t('cms.contentSort.sorting')" align="left">
                    <template #default="scope">
                        <el-input v-model="scope.row.sorting" @change="sortingChange(scope.row.sorting, scope.row)" />
                    </template>
                </el-table-column>
            </template>
        </Table>

        <!-- 表单 -->
        <PopupForm />

        <!-- 批量添加表单 -->
        <BatchAddForm />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import BatchAddForm from './batchAddForm.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import { url } from '/@/api/backend/cms/ContentSort'
import DataLang from '/@/components/datalang/index.vue'
import { cloneDeep } from 'lodash-es'

defineOptions({
    name: 'cms/contentSort',
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
            {
                label: t('cms.contentSort.name'),
                prop: 'name',
                align: 'left',
                render: 'slot',
                slotName: 'name',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('cms.contentSort.id'), prop: 'scode', align: 'center', width: 100, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('cms.contentSort.filename'),
                prop: 'filename',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.contentSort.mcode'),
                prop: 'models.name',
                render: 'tag',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.contentSort.listtpl'),
                prop: 'listtpl',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.contentSort.contenttpl'),
                prop: 'contenttpl',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.contentSort.outlink'),
                prop: 'outlink',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.contentSort.sorting'),
                prop: 'sorting',
                render: 'slot',
                slotName: 'sorting',
                align: 'center',
                operator: 'RANGE',
                sortable: false,
            },
            {
                label: t('cms.contentSort.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: {},
            },
            {
                label: t('cms.contentSort.create_time'),
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
        defaultItems: { status: '1', sorting: 255, gtype: '4' },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})

/*列表排序修改*/
const sortingChange = (value: any, data: any) => {
    data.loading = true
    baTable.api
        .postData('edit', {
            [baTable.table.pk!]: data[baTable.table.pk!],
            sorting: value,
        })
        .then(() => {
            baTable.onTableAction('field-change', { value: value, ...data })
            baTable.table.data = []
            baTable.getIndex()
        })
        .finally(() => {
            data.loading = false
        })
}

const onBatchAdd = () => {
    baTable.form.operate = 'BatchAdd'
    baTable.form.items = cloneDeep(baTable.form.defaultItems)
}
</script>

<style scoped lang="scss"></style>
