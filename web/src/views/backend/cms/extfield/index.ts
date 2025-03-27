import { i18n } from '/@/lang/index'
import { validatorType } from '/@/utils/validate'

const tableBaseAttr = {
    render: {
        type: 'select',
        value: 'none',
        options: {
            none: i18n.global.t('None'),
            icon: 'Icon',
            switch: i18n.global.t('utils.switch'),
            image: i18n.global.t('utils.image'),
            images: i18n.global.t('utils.multi image'),
            tag: 'Tag',
            tags: 'Tags',
            url: 'URL',
            datetime: i18n.global.t('utils.time date'),
            color: i18n.global.t('utils.color'),
        },
    },
    operator: {
        type: 'select',
        value: 'eq',
        options: {
            false: i18n.global.t('crud.state.Disable Search'),
            eq: 'eq =',
            ne: 'ne !=',
            gt: 'gt >',
            egt: 'egt >=',
            lt: 'lt <',
            elt: 'elt <=',
            LIKE: 'LIKE',
            'NOT LIKE': 'NOT LIKE',
            IN: 'IN',
            'NOT IN': 'NOT IN',
            RANGE: 'RANGE',
            'NOT RANGE': 'NOT RANGE',
            NULL: 'NULL',
            'NOT NULL': 'NOT NULL',
            FIND_IN_SET: 'FIND_IN_SET',
        },
    },
    sortable: {
        type: 'select',
        value: 'false',
        options: {
            false: i18n.global.t('Disable'),
            custom: i18n.global.t('Enable'),
        },
    },
}


export const getTableAttr = (type: keyof typeof tableBaseAttr, val: string) => {
    return {
        ...tableBaseAttr[type],
        value: val,
    }
}

const formBaseAttr = {
    validator: {
        type: 'selects',
        value: [],
        options: validatorType,
    },
    validatorMsg: {
        type: 'textarea',
        value: '',
        placeholder: i18n.global.t('crud.state.If left blank, the verifier title attribute will be filled in automatically'),
        attr: {
            rows: 3,
        },
    },
}

export const designTypes: anyObj = {
    string: {
        name: i18n.global.t('utils.string'),
        table: {
            ...tableBaseAttr,
            operator: getTableAttr('operator', 'LIKE'),
        },
        form: formBaseAttr,
    },
    radio: {
        name: i18n.global.t('utils.radio'),
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tag'),
        },
        form: formBaseAttr,
    },
    checkbox: {
        name: i18n.global.t('utils.checkbox'),
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tags'),
            operator: getTableAttr('operator', 'FIND_IN_SET'),
        },
        form: formBaseAttr,
    },
    select: {
        name: i18n.global.t('utils.select'),
        table: {
            ...tableBaseAttr,
            render: getTableAttr('render', 'tag'),
        },
        form: {
            ...formBaseAttr,
            'select-multi': {
                type: 'switch',
                value: false,
            },
        },
    },
    images: {
        name: i18n.global.t('utils.image') + i18n.global.t('Upload'),
        table: {
            render: getTableAttr('render', 'images'),
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            'image-multi': {
                type: 'switch',
                value: true,
            },
        },
    },
    files: {
        name: i18n.global.t('utils.file') + i18n.global.t('Upload'),
        table: {
            render: getTableAttr('render', 'none'),
            operator: getTableAttr('operator', 'false'),
        },
        form: {
            ...formBaseAttr,
            'file-multi': {
                type: 'switch',
                value: true,
            },
        },
    },
}
