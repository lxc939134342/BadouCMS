<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.form.quick Search Fields') })"
        >
        </TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <!-- 自定义表单-字段-插槽组件 作用域插槽 -->
            <template #formData>
                <el-table-column :label="t('cms.form.form_data')" width="160" align="center">
                    <template #default="scope">
                        <!--                        根据 id=1 bd_cms_message 判断 走指定 路由地址 ，而其他走固定路由地址-->
                        <!--                        <router-link class="form" :to="{ path: 'formData', query: { fcode: scope.row.fcode } }"> 编辑数据 </router-link>-->
                        <router-link v-if="scope.row.fcode == 1" class="form" :to="{ path: 'message' }"> 编辑数据 </router-link>
                        <router-link v-else class="form" :to="{ path: 'formData', query: { fcode: scope.row.fcode } }"> 编辑数据 </router-link>
                    </template>
                </el-table-column>
            </template>

            <!-- 自定义表单-字段-插槽组件 -->
            <template #formField>
                <el-table-column :label="t('cms.form.form_field')" width="160" align="center">
                    <template #default="scope">
                        <router-link class="form" :to="{ path: 'formField', query: { fcode: scope.row.fcode } }"> 编辑字段 </router-link>
                    </template>
                </el-table-column>
            </template>
        </Table>

        <!-- 表单 子组件 -->
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
import { getArrayKey, getCurrentRoutePath } from '/@/utils/common'

defineOptions({
    name: 'cms/form',
})

const { t } = useI18n()
const tableRef = ref()
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/cms.Form/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.form.id'), prop: 'id', align: 'center', width: 80, operator: 'RANGE', sortable: 'custom' },
            { label: t('cms.form.fcode'), prop: 'fcode', align: 'center', width: 120, operator: false, sortable: 'custom' },
            {
                label: t('cms.form.form_name'),
                prop: 'form_name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('cms.form.table_name'),
                prop: 'table_name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            // 插槽组件
            { render: 'slot', slotName: 'formData', operator: false },
            // 插槽组件
            { render: 'slot', slotName: 'formField', operator: false },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {},
    }
)

/*禁止删除*/
let btnIndex = getArrayKey(baTable.table.column, 'render', 'buttons') // 找到 render=buttons 的列
//找到表格表头 第 9 列
// console.log(btnIndex)
baTable.table.column[btnIndex].buttons![1].display = (row: TableRow) => {
    // console.log(row)
    // console.log(row.issystem != 1)
    return row.id != 1
}

// console.info(baTable.table)
// console.info(getArrayKey(this.table.column, 'prop', 'formData'))

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    //todo 需要隐藏 id=1 bd_cms_message 中 不能有 编辑和删除 按钮，由于是组件封装 需要单独做判断
    //todo 关于非内置表单，需要增加一个 添加到菜单 按钮，点击能增加到菜单栏目位置
    // console.log(baTable.table)
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss">
.form {
    color: var(--el-text-color-primary);
    text-decoration: none;
}

.form:hover {
    color: var(--el-text-color-secondary);
}
</style>
