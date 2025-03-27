<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.slide.quick Search Fields') })"
        >
        </TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #sorting>
                <el-table-column width="100" :label="t('cms.slide.sorting')" align="left">
                    <template #default="scope">
                        <el-input v-model="scope.row.sorting" @change="sortingChange(scope.row.sorting, scope.row)" />
                    </template>
                </el-table-column>
            </template>
        </Table>

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

defineOptions({
    name: 'cms/slide',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/cms.Slide/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.slide.id'), prop: 'id', align: 'center', width: 80, operator: false, sortable: 'custom' },
            { label: t('cms.slide.gid'), prop: 'gid', align: 'center', width: 110, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('cms.slide.pic'),
                prop: 'pic',
                align: 'center',
                render: 'image',
                operatorPlaceholder: t('Fuzzy query'),
                operator: false,
                sortable: false,
            },
            { label: t('cms.slide.link'), prop: 'link', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('cms.slide.title'), prop: 'title', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: t('cms.slide.subtitle'),
                prop: 'subtitle',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.slide.sorting'),
                prop: 'sorting',
                align: 'center',
                render: 'slot',
                slotName: 'sorting',
                operator: 'RANGE',
                sortable: false,
            },
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {},
    }
)

provide('baTable', baTable)
/*列表排序修改*/
const sortingChange = (value: any, data: any) => {
    baTable.api
        .postData('edit', { [baTable.table.pk!]: data[baTable.table.pk!], sorting: value })
        .then(() => {
            data.loading = false
            baTable.table.data = []
            baTable.getIndex()
        })
        .catch(() => {
            data.loading = false
        })
}
onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.table.filter!.order = 'sorting,asc'
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss"></style>
