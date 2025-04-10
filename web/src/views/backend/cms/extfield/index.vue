<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.extfield.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #sorting>
                <el-table-column :label="t('cms.extfield.sorting')" width="100" align="left">
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
import { url, models, typeText } from '/@/api/backend/cms/Extfield'
import { sorting } from '/@/api/backend/cms/Common'

defineOptions({
    name: 'cms/extfield',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])
const mcodeMap = ref()
const typeTextMap = ref()

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi(url),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.extfield.id'), prop: 'id', align: 'center', width: 100, operator: 'RANGE', sortable: 'custom' },
            {
                label: t('cms.extfield.mcode'),
                prop: 'mcode',
                align: 'center',
                render: 'tag',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
                replaceValue: mcodeMap,
            },
            {
                label: t('cms.extfield.description'),
                prop: 'description',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.extfield.name'),
                prop: 'name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.extfield.type'),
                prop: 'type',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                replaceValue: typeTextMap,
            },
            {
                label: t('cms.extfield.sorting'),
                prop: 'sorting',
                render: 'slot',
                slotName: 'sorting',
                align: 'center',
                operator: 'RANGE',
                sortable: false,
            },
            { label: t('Operate'), align: 'center', width: 180, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { type: '', name: 'ext_', value: '', sorting: 0 },
    }
)
models().then((res) => {
    if (res.code == 1) {
        mcodeMap.value = res.data.list
    }
})

typeText().then((res) => {
    if (res.code == 1) {
        typeTextMap.value = res.data.list
    }
})
provide('baTable', baTable)
provide('mcodeMap', mcodeMap)
provide('typeTextMap', typeTextMap)

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
    sorting(url + 'sorting', data.id, value)
        .then(() => {
            data.loading = false
            baTable.table.data = []
            baTable.getIndex()
        })
        .catch(() => {
            data.loading = false
        })
}
</script>

<style scoped lang="scss"></style>
