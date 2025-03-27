<template>
    <div class="ba-editor ueditorplus">
        <vue-ueditor-wrap v-if="state.mounted" v-model="state.value" :editor-id="`editor-${uuid()}`" :config="editorConfig" style="width: 100%" :editorDependencies="['ueditor.config.js', 'ueditor.all.js']" />
        <div v-else class="editor-loading">
            <i class="el-icon-loading"></i>
            编辑器初始化中...
        </div>
    </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, reactive, watch, onMounted } from "vue";
import { useSiteConfig } from "/@/stores/siteConfig";
import { useAdminInfo } from "/@/stores/adminInfo";
import adminBaseRoute from "/@/router/static/adminBase";
import { isAdminApp } from "/@/utils/common";
import { uuid } from "/@/utils/random";

const adminInfo = useAdminInfo();
const siteConfig = useSiteConfig();

interface Props {
    height?: string;
    mode?: "default" | "simple";
    placeholder?: string;
    modelValue: string | null;
    fileForceLocal?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    height: "320px",
    mode: "default",
    placeholder: "请输入内容...",
    modelValue: "",
    fileForceLocal: false,
});

const emits = defineEmits<{
    (e: "update:modelValue", value: string): void;
}>();

const state = reactive({
    mounted: false,
    value: !props.modelValue ? "<p></p>" : props.modelValue,
});

let serverUrl = "/admin/ueditor/init?ba-token=" + adminInfo.getToken("auth");
// 自定义后台入口
if (adminBaseRoute.path !== "/admin" && isAdminApp() && /^\/admin\//.test(serverUrl)) {
    serverUrl = serverUrl.replace(/^\/admin\//, adminBaseRoute.path + ".php/");
} else {
    serverUrl = "/index.php" + serverUrl;
}

serverUrl = siteConfig.cdnUrl + serverUrl;

const editorConfig = {
    serverUrl: serverUrl,
    UEDITOR_HOME_URL: "/static/UEditorPlus/",
    UEDITOR_CORS_URL: "/static/UEditorPlus/",
    initialFrameWidth: "100%",
    initialFrameHeight: 300,
    zIndex: 9999,
};

// 异步组件定义
const VueUeditorWrap = defineAsyncComponent({
    loader: () => import("vue-ueditor-wrap").then((m) => m.VueUeditorWrap),
    loadingComponent: () => '<div class="loading">编辑器加载中...</div>',
    delay: 200,
    onError(error, retry, fail, attempts) {
        console.error("加载编辑器失败:", error);
        if (attempts < 3) {
            retry();
        } else {
            fail();
        }
    },
});

// 监听编辑器内容变化
watch(
    () => state.value,
    (newVal) => {
        // 将修改后的值通过 emit 发送给父组件
        emits("update:modelValue", newVal);
    }
);

// 监听父组件传入的值变化
watch(
    () => props.modelValue,
    (newVal) => {
        state.value = !newVal ? "<p></p>" : newVal;
    }
);

onMounted(async () => {
    try {
        await import("vue-ueditor-wrap");
        state.mounted = true;
    } catch (error) {
        console.error("加载编辑器失败:", error);
    }
});
</script>

<style lang="scss" scoped>
.editor-loading {
    text-align: center;
    padding: 20px;
    .el-icon-loading {
        font-size: 24px;
        margin-right: 8px;
    }
}
</style>
