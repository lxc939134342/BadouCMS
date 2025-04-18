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

                <el-tooltip v-if="auth({ name: '/admin/cms/content', subNodeName: '/admin/cms/content/add' })" :content="t('Add')" placement="top">
                    <el-button v-blur @click="baTable.onTableHeaderAction('add', {})" class="table-header-operate" type="primary">
                        <Icon name="fa fa-plus" />
                        <span class="table-header-operate-text">{{ t('Add') }}</span>
                    </el-button>
                </el-tooltip>
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
                <el-popconfirm
                    v-if="auth({ name: '/admin/cms/content', subNodeName: '/admin/cms/content/del' })"
                    @confirm="baTable.onTableHeaderAction('delete', {})"
                    :confirm-button-text="t('Delete')"
                    :cancel-button-text="t('Cancel')"
                    confirmButtonType="danger"
                    :title="t('Are you sure to delete the selected record?')"
                    :disabled="!enableBatchOpt"
                >
                    <template #reference>
                        <div class="mlr-12">
                            <el-tooltip :content="t('Delete selected row')" placement="top">
                                <el-button v-blur :disabled="!enableBatchOpt" class="table-header-operate" type="danger">
                                    <Icon name="fa fa-trash" />
                                    <span class="table-header-operate-text">{{ t('Delete') }}</span>
                                </el-button>
                            </el-tooltip>
                        </div>
                    </template>
                </el-popconfirm>
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
            <template #default>
                <el-tooltip
                    v-if="auth({ name: '/admin/cms/content', subNodeName: '/admin/cms/content/copy' })"
                    :content="t('cms.content.Copy selected row')"
                    placement="top"
                >
                    <el-button
                        class="table-header-operate"
                        type="primary"
                        @click="openSelectCategory('copy')"
                        :disabled="!enableBatchOpt"
                        style="margin-left: 12px"
                    >
                        <Icon color="#ffffff" name="el-icon-CopyDocument" />
                        <span class="table-header-operate-text">{{ t('cms.content.copy') }}</span>
                    </el-button>
                </el-tooltip>
                <el-tooltip
                    v-if="auth({ name: '/admin/cms/content', subNodeName: '/admin/cms/content/move' })"
                    :content="t('cms.content.Move selected row')"
                    placement="top"
                >
                    <el-button class="table-header-operate" type="primary" @click="openSelectCategory('move')" :disabled="!enableBatchOpt">
                        <Icon color="#ffffff" name="el-icon-Magnet" />
                        <span class="table-header-operate-text">{{ t('cms.content.move') }}</span>
                    </el-button>
                </el-tooltip>
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

        <!-- 选择栏目弹窗 -->
        <el-dialog v-model="selectCategory.show" :title="t('cms.content.Please Select a Category')" width="400">
            <RemoteSelect
                v-model="selectCategory.id"
                pk="content_sort.scode"
                field="name"
                remoteUrl="/admin/cms.ContentSort/index"
                :params="scodeParams"
                class="el-select"
            ></RemoteSelect>
            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="selectCategory.show = false">{{ t('Cancel') }}</el-button>
                    <el-button type="primary" @click="saveSelectCateogry"> {{ t('Save') }} </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, ref, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import { baTableApi } from '/@/api/common'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'
import { getCurrentRoutePath, auth } from '/@/utils/common'
import { useCmsLang } from '/@/stores/cms/lang'
import RemoteSelect from '/@/components/baInput/components/remoteSelect.vue'
import DataLang from '/@/components/datalang/index.vue'
import { copy_move, edit_sorting } from '/@/api/backend/cms/Content'
import { ElNotification } from 'element-plus'
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
    {
        render: 'confirmButton',
        name: 'custom-delete',
        title: '删除',
        text: '',
        type: 'danger',
        icon: 'fa fa-trash',
        class: 'table-row-delete',
        disabledTip: false,
        popconfirm: {
            confirmButtonText: t('Delete'),
            cancelButtonText: t('Cancel'),
            confirmButtonType: 'danger',
            title: t('Are you sure to delete the selected record?'),
        },
        display: (row: TableRow, field: TableColumn) => {
            return auth({ name: '/admin/cms/content', subNodeName: '/admin/cms/content/edit' })
        },
        click: (row: TableRow, field: TableColumn) => {
            baTable.onTableAction('delete', { row })
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
    acode: useCmsLang().getDataLang(),
    search: [{ field: 'mcode', operator: '=', val: mcode }],
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

const selectCategory = reactive({
    id: '',
    show: false,
    type: 'copy',
})

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
            {
                label: t('cms.content.date'),
                prop: 'date',
                align: 'left',
                render: 'datetime',
                operator: 'RANGE',
                comSearchRender: 'date',
                sortable: 'custom',
                width: 200,
                show: false,
                enableColumnDisplayControl: false,
            },
            { label: t('cms.content.date'), prop: 'date', align: 'left', sortable: 'custom', width: 200, operator: false },
            {
                label: t('cms.content.sorting'),
                prop: 'sorting',
                align: 'center',
                render: 'slot',
                slotName: 'sorting',
                operator: false,
                sortable: false,
            },
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
            { label: t('cms.content.istop'), width: 100, prop: 'istop', align: 'center', render: 'switch', operator: 'eq', sortable: false },
            {
                label: t('cms.content.isrecommend'),
                width: 100,
                prop: 'isrecommend',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
            },
            { label: t('cms.content.visits'), width: 100, prop: 'visits', align: 'center', operator: false, sortable: false },
            { label: t('Operate'), align: 'center', width: 150, render: 'buttons', buttons: optButtons, operator: false },
        ],
        filter: {
            mcode: mcode,
        },
        dblClickNotEditColumn: [undefined],
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
/* 打开选择分类弹窗 */
const openSelectCategory = (type: string) => {
    selectCategory.show = true
    selectCategory.type = type
}
/* 保存选择分类 */
const saveSelectCateogry = () => {
    const ids = baTable.getSelectionIds()
    if (ids.length == 0) {
        return
    }
    copy_move(selectCategory.type, selectCategory.id, ids).then((res) => {
        selectCategory.show = false
        selectCategory.id = ''
        if (res.code == 1) {
            baTable.getIndex()
        }
    })
}
</script>

<style scoped lang="scss">
.mlr-12 {
    margin-left: 12px;
}
.mlr-12 + .el-button {
    margin-left: 12px;
}
</style>
