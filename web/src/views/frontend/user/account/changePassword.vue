<template>
    <div class="user-views">
        <el-card class="user-views-card" shadow="hover" :header="t('user.account.changePassword.Change Password')">
            <div class="change-password">
                <el-form :model="state.form" :rules="state.rules" label-position="top" ref="formRef" @keyup.enter="onSubmit()">
                    <FormItem
                        :label="t('user.account.changePassword.Old password')"
                        type="password"
                        v-model="state.form.oldPassword"
                        prop="oldPassword"
                        :input-attr="{ showPassword: true }"
                        :placeholder="t('user.account.changePassword.Please enter your current password')"
                    />
                    <FormItem
                        :label="t('user.account.changePassword.New password')"
                        type="password"
                        v-model="state.form.newPassword"
                        prop="newPassword"
                        :input-attr="{ showPassword: true }"
                        :placeholder="t('Please input field', { field: t('user.account.changePassword.New password') })"
                    />
                    <FormItem
                        :label="t('user.account.changePassword.Confirm new password')"
                        type="password"
                        v-model="state.form.confirmPassword"
                        prop="confirmPassword"
                        :input-attr="{
                            showPassword: true,
                        }"
                        :placeholder="t('Please input field', { field: t('user.account.changePassword.Confirm new password') })"
                    />
                    <el-form-item class="submit-buttons">
                        <el-button @click="onResetForm(formRef)">{{ $t('Reset') }}</el-button>
                        <el-button type="primary" :loading="state.formSubmitLoading" @click="onSubmit()">{{ $t('Save') }}</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>
    </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.change-password {
    width: 360px;
    max-width: 100%;
}
.submit-buttons :deep(.el-form-item__content) {
    justify-content: flex-end;
}
</style>
