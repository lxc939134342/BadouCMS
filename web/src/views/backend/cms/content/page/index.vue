<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.content.title') })"
        >
            <template #refreshAppend>
                <DataLang @change-data-lang="changeDataLang"></DataLang>

                <el-tooltip
                    v-if="auth({ name: '/admin/cms/content', subNodeName: '/admin/cms/content/edit' })"
                    :content="t('Edit selected row')"
                    placement="top"
                >
                    <el-button
                        v-blur
                        @click="baTable.onTableHeaderAction('edit', {})"
                        :disabled="!enableBatchOpt"
                        class="table-header-operate"
                        type="primary"
                    >
                        <Icon name="fa fa-pencil" />
                        <span class="table-header-operate-text">{{ t('Edit') }}</span>
                    </el-button>
                </el-tooltip>
            </template>
            <template #scode>
                <RemoteSelect
                    v-model="baTable.comSearch.form.scode"
                    pk="content_sort.scode"
                    field="name"
                    remoteUrl="/admin/cms.ContentSort/index"
                    :params="scodeParams"
                    ref="scodeRef"
                    class="el-select"
                ></RemoteSelect>
            </template>
        </TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #sorting>
                <el-table-column :label="t('cms.content.sorting')" width="100" align="left">
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
import { onMounted, provide, ref, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import { getCurrentRoutePath, auth } from '/@/utils/common'
import DataLang from '/@/components/datalang/index.vue'
import RemoteSelect from '/@/components/baInput/components/remoteSelect.vue'
import { useCmsLang } from '/@/stores/cms/lang'

defineOptions({
    name: 'cms/content',
})

const { t } = useI18n()
const tableRef = ref()
const scodeRef = ref()
const optButtons: OptButton[] = [
    {
        render: 'tipButton',
        name: 'custom-edit',
        title: '编辑',
        text: '',
        type: 'primary',
        icon: 'fa fa-pencil',
        class: 'ba-table-render-buttons-item',
        disabledTip: false,
        display: (row: TableRow, field: TableColumn) => {
            return auth({ name: '/admin/cms/content', subNodeName: '/admin/cms/content/edit' })
        },
        click: (row: TableRow, field: TableColumn) => {
            baTable.onTableAction('edit', { row })
        },
    },
]

const path = getCurrentRoutePath()
const patharr = path.split('/')
const mcode: string = patharr[patharr.length - 1]
const scodeParams = reactive({
    select: true,
    order: 'sorting,asc',
    istop: 0,
    search: [
        { field: 'mcode', operator: '=', val: mcode },
        { field: 'acode', operator: '=', val: useCmsLang().getDataLang() },
    ],
})

/* 当前时间 */
const now = new Date()
const year = now.getFullYear()
const month = String(now.getMonth() + 1).padStart(2, '0') // 月份从0开始，所以加1
const day = String(now.getDate()).padStart(2, '0')
const hours = String(now.getHours()).padStart(2, '0')
const minutes = String(now.getMinutes()).padStart(2, '0')
const seconds = String(now.getSeconds()).padStart(2, '0')

const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/cms.Content/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.content.id'), prop: 'id', align: 'center', width: 80, operator: false, sortable: 'custom' },
            {
                label: t('cms.content.scode'),
                prop: 'scode',
                show: false,
                align: 'left',
                width: 180,
                comSearchRender: 'slot',
                comSearchSlotName: 'scode',
                enableColumnDisplayControl: false,
            },
            { label: t('cms.content.scode'), prop: 'contentsort.name', width: 180, align: 'left', operator: false, sortable: false },
            { label: t('cms.content.title'), prop: 'title', align: 'left', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            { label: t('cms.content.date'), prop: 'date', align: 'left', sortable: 'custom', width: 200, operator: false },
            {
                label: t('cms.content.status'),
                width: 100,
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: {},
            },
            { label: t('cms.content.visits'), width: 100, prop: 'visits', align: 'center', operator: false, sortable: false },
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
        filter: {
            mcode: mcode,
        },
        showComSearch: true,
    },
    {
        defaultItems: { sorting: 255, status: '1', visits: 0, likes: 0, oppose: 0, gtype: '4', mcode: mcode, date: formattedDate },
    }
)
const enableBatchOpt = computed(() => (baTable.table.selection!.length > 0 ? true : false))

baTable.requestEdit = (id: string) => {
    if (baTable.runBefore('requestEdit', { id }) === false) return
    baTable.form.loading = true
    baTable.form.items = {}
    return baTable.api
        .edit({
            [baTable.table.pk!]: id,
            mcode,
        })
        .then((res) => {
            baTable.form.items = res.data.row
            baTable.form.items!.mcode = mcode
            baTable.runAfter('requestEdit', { res })
        })
        .catch((err) => {
            baTable.toggleForm()
            baTable.runAfter('requestEdit', { err })
        })
        .finally(() => {
            baTable.form.loading = false
        })
}

provide('baTable', baTable)
provide('mcode', mcode)

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

/* 切换数据区域 */
const changeDataLang = () => {
    scodeParams.search[1].val = useCmsLang().getDataLang()

    scodeRef.value.getData()
}
</script>

<style scoped lang="scss"></style>
