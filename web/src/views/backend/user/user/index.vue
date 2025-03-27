<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('user.user.User name') + '/' + t('user.user.nickname') })"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" />

        <!-- 表单 -->
        <PopupForm />

        <!-- 详情 -->
        <Info />
    </div>
</template>

<script setup lang="ts">
import { ref, provide } from 'vue'
import baTableClass from '/@/utils/baTable'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import Info from './info.vue'

defineOptions({
    name: 'user/user',
})

const { t } = useI18n()
const tableRef = ref()
let optButtons: OptButton[] = [
    {
        name: 'info',
        title: t('user.user.info'),
        text: t('user.user.info'),
        render: 'tipButton',
        type: 'warning',
        icon: 'el-icon-InfoFilled',
        click: (row: TableRow, field: TableColumn) => {
            baTable.onInfo(row, field)
        },
    },
]
optButtons = [...optButtons, ...defaultOptButtons(['edit', 'delete'])]
/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
/* 继承baTableClass类 */
class UserBaTable extends baTableClass {
    onInfo(row: TableRow, field: TableColumn) {
        this.form.items = row
        this.form.operate = 'info'
    }
}
const baTable = new UserBaTable(
    new baTableApi('/admin/user.User/'),
    {
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('Id'), prop: 'id', align: 'center', operator: '=', operatorPlaceholder: t('Id'), width: 70 },
            { label: t('user.user.User name'), prop: 'username', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            { label: t('user.user.nickname'), prop: 'nickname', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('user.user.grouping'),
                prop: 'group.name',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tag',
            },
            { label: t('user.user.head portrait'), prop: 'avatar', align: 'center', render: 'image', operator: false },
            {
                label: t('user.user.Gender'),
                prop: 'gender',
                align: 'center',
                render: 'tag',
                custom: { '0': 'info', '1': '', '2': 'success' },
                replaceValue: { '0': t('Unknown'), '1': t('user.user.male'), '2': t('user.user.female') },
            },
            { label: t('user.user.mobile'), prop: 'mobile', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            { label: t('user.user.level'), prop: 'member_group.gname', align: 'center', operator: 'LIKE', operatorPlaceholder: t('Fuzzy query') },
            {
                label: t('user.user.Last login IP'),
                prop: 'last_login_ip',
                align: 'center',
                operator: 'LIKE',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tag',
            },
            {
                label: t('user.user.Last login'),
                prop: 'last_login_time',
                align: 'center',
                render: 'datetime',
                sortable: 'custom',
                operator: 'RANGE',
                width: 160,
            },
            { label: t('Create time'), prop: 'create_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            {
                label: t('State'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { disable: 'danger', enable: 'success' },
                replaceValue: { disable: t('Disable'), enable: t('Enable') },
            },
            {
                label: t('Operate'),
                align: 'center',
                width: '180',
                render: 'buttons',
                buttons: optButtons,
                operator: false,
            },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {
            gender: 0,
            money: '0',
            score: '0',
            status: 'enable',
        },
    }
)

baTable.mount()
baTable.getIndex()

provide('baTable', baTable)
</script>

<style scoped lang="scss"></style>
