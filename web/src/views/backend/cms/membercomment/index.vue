<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.membercomment.quick Search Fields') })"
        >
            <template #default>
                <el-tooltip :content="t('cms.membercomment.review')" placement="top">
                    <el-button class="table-header-operate" style="margin-left: 12px" @click="onReview('review')" type="success">
                        <Icon color="#ffffff" name="el-icon-Switch" />
                        <span class="table-header-operate-text">{{ t('cms.membercomment.review') }}</span>
                    </el-button>
                </el-tooltip>

                <el-tooltip :content="t('cms.membercomment.disable')" placement="top">
                    <el-button class="table-header-operate" style="margin-left: 12px" @click="onReview('disable')" type="warning">
                        <Icon color="#ffffff" name="el-icon-RemoveFilled" />
                        <span class="table-header-operate-text">{{ t('cms.membercomment.disable') }}</span>
                    </el-button>
                </el-tooltip>
            </template>
        </TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"> </Table>

        <!-- 表单 -->
        <PopupForm />

        <!-- 详情 -->
        <Info />
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
import { ElMessage } from 'element-plus'
import { review } from '/@/api/backend/cms/MemberComment'
import Info from './info.vue'
defineOptions({
    name: 'cms/membercomment',
})

const { t } = useI18n()
const tableRef = ref()
let optButtons: OptButton[] = [
    {
        name: 'info',
        title: t('cms.membercomment.info'),
        text: t('cms.membercomment.info'),
        render: 'tipButton',
        type: 'primary',
        icon: 'el-icon-InfoFilled',
        click: (row: TableRow, field: TableColumn) => {
            baTable.onInfo(row, field)
        },
    },
]
optButtons = [...optButtons, ...defaultOptButtons(['delete'])]
/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
/* 继承baTableClass类 */
class MemberCommentBaTable extends baTableClass {
    onInfo(row: TableRow, field: TableColumn) {
        this.form.items = row
        this.form.operate = 'info'
    }
}

const baTable = new MemberCommentBaTable(
    new baTableApi('/admin/cms.memberComment/'),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('cms.membercomment.contentid'), prop: 'content.title', align: 'center', operator: 'RANGE', sortable: false },
            {
                label: t('cms.membercomment.comment'),
                prop: 'comment',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            { label: t('cms.membercomment.uid'), prop: 'user.nickname', align: 'center', operator: 'RANGE', sortable: false },
            {
                label: t('cms.membercomment.status'),
                prop: 'status',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: {},
            },

            {
                label: t('cms.membercomment.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },

            { label: t('Operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: {},
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

/**
 * 审核
 */
const onReview = (type: string) => {
    const ids = baTable.getSelectionIds()
    if (ids.length == 0) {
        /* 判断type */
        ElMessage.error(t(`cms.membercomment.select${type}`))
        return
    }

    let status = 1
    if (type == 'disable') {
        status = 0
    }

    review(ids, status).then((res) => {
        if (res.code == 1) {
            ElMessage.success(res.msg)
            baTable.getIndex()
        }
    })
}
</script>

<style scoped lang="scss"></style>
