<template>
    <div class="default-main ba-table-box">
        <!-- 表格顶部菜单 -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableHeader
            :buttons="['refresh', 'delete', 'comSearch', 'quickSearch', 'columnDisplay']"
            :quick-search-placeholder="t('Quick search placeholder', { fields: t('cms.formData.quick Search Fields') })"
        ></TableHeader>

        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"> </Table>
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table/index'
import TableHeader from '/@/components/table/header/index.vue'
import Table from '/@/components/table/index.vue'
import baTableClass from '/@/utils/baTable'

import { useRouter } from 'vue-router'

const $router = useRouter()
const query = $router.currentRoute.value.query

defineOptions({
    name: 'cms/formData',
})

const { t } = useI18n()
const tableRef = ref()

const optButtons: OptButton[] = defaultOptButtons(['delete'])

//根据URL fcode 表单编码 ID 进行传递给后端 index 方法进行查询
const fcode: string = query.fcode as string

interface FormField {
    id: number
    fcode: string
    name: string
    length: number
    required: string
    description: string
    sorting: number
}

let formFields: FormField[] = []

//根据获取自定义动态字段信息与数据 拼接JSON 数据结构

let column: TableColumn[] = [
    { type: 'selection', align: 'center', operator: false },
    { label: t('cms.formData.id'), prop: 'id', align: 'center', width: 80, operator: 'RANGE', sortable: 'custom' },
]

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */

const baTable = new baTableClass(
    new baTableApi('/admin/cms.FormData/'),
    {
        pk: 'id',
        column: column,
        filter: {
            fcode: fcode,
        },
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { required: '0', sorting: 255, fcode: fcode },
    },
    {},
    {
        getIndex: (res) => {
            /* 获取自定义的字段列 */
            if (res.res.code == 1 && res.res.data.formFields) {
                formFields = res.res.data.formFields
                /* 遍历formFields 如果表格列中已经存在该字段则跳过 */
                for (let formFieldsKey in formFields) {
                    if (baTable.table.column.find((item) => item.prop == formFields[formFieldsKey].name)) continue
                    column.push({
                        label: formFields[formFieldsKey].description,
                        prop: formFields[formFieldsKey].name,
                        align: 'center',
                        operator: 'LIKE',
                        sortable: false,
                    })
                }
                /* 如果表格列中不存在操作列则添加 */
                if (!baTable.table.column.find((item) => item.prop == 'operate')) {
                    column.push({
                        prop: 'operate',
                        label: t('Operate'),
                        align: 'center',
                        width: 140,
                        render: 'buttons',
                        buttons: optButtons,
                        operator: false,
                    })
                }
                baTable.table.column = column
            }
        },
    }
)

provide('baTable', baTable)

//挂载完毕
onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss">
.ml-25 {
    margin-left: 25px;
}
</style>
