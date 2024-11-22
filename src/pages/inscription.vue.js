import RegisterForm from '@/components/auth/RegisterForm.vue';
import RegisterOTP from '@/components/auth/RegisterOTP.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
const { defineProps, defineSlots, defineEmits, defineExpose, defineModel, defineOptions, withDefaults, } = await import('vue');
definePage({
    meta: {
        title: 'Inscription',
        requiresAuth: false,
    },
});
const router = useRouter();
const registerStatus = ref('form');
const registerEmail = ref('');
function handleSuccess() {
    router.push('/connexion');
}
; /* PartiallyEnd: #3632/scriptSetup.vue */
const __VLS_fnComponent = (await import('vue')).defineComponent({});
;
let __VLS_functionalComponentProps;
function __VLS_template() {
    const __VLS_ctx = {};
    const __VLS_localComponents = {
        ...{},
        ...{},
        ...__VLS_ctx,
    };
    let __VLS_components;
    const __VLS_localDirectives = {
        ...{},
        ...__VLS_ctx,
    };
    let __VLS_directives;
    let __VLS_styleScopedClasses;
    let __VLS_resolvedLocalAndGlobalComponents;
    // @ts-ignore
    [AuthLayout, AuthLayout,];
    // @ts-ignore
    const __VLS_0 = __VLS_asFunctionalComponent(AuthLayout, new AuthLayout({}));
    const __VLS_1 = __VLS_0({}, ...__VLS_functionalComponentArgsRest(__VLS_0));
    var __VLS_5 = {};
    const __VLS_6 = __VLS_resolvedLocalAndGlobalComponents.VTabsWindow;
    /** @type { [typeof __VLS_components.VTabsWindow, typeof __VLS_components.VTabsWindow, ] } */
    // @ts-ignore
    const __VLS_7 = __VLS_asFunctionalComponent(__VLS_6, new __VLS_6({ modelValue: ((__VLS_ctx.registerStatus)), }));
    const __VLS_8 = __VLS_7({ modelValue: ((__VLS_ctx.registerStatus)), }, ...__VLS_functionalComponentArgsRest(__VLS_7));
    const __VLS_12 = __VLS_resolvedLocalAndGlobalComponents.VTabsWindowItem;
    /** @type { [typeof __VLS_components.VTabsWindowItem, typeof __VLS_components.VTabsWindowItem, ] } */
    // @ts-ignore
    const __VLS_13 = __VLS_asFunctionalComponent(__VLS_12, new __VLS_12({ value: ("form"), }));
    const __VLS_14 = __VLS_13({ value: ("form"), }, ...__VLS_functionalComponentArgsRest(__VLS_13));
    // @ts-ignore
    [RegisterForm,];
    // @ts-ignore
    const __VLS_18 = __VLS_asFunctionalComponent(RegisterForm, new RegisterForm({ ...{ 'onSuccess': {} }, modelValue: ((__VLS_ctx.registerEmail)), }));
    const __VLS_19 = __VLS_18({ ...{ 'onSuccess': {} }, modelValue: ((__VLS_ctx.registerEmail)), }, ...__VLS_functionalComponentArgsRest(__VLS_18));
    let __VLS_23;
    const __VLS_24 = {
        onSuccess: (...[$event]) => {
            __VLS_ctx.registerStatus = 'otp';
        }
    };
    let __VLS_20;
    let __VLS_21;
    var __VLS_22;
    __VLS_nonNullable(__VLS_17.slots).default;
    var __VLS_17;
    const __VLS_25 = __VLS_resolvedLocalAndGlobalComponents.VTabsWindowItem;
    /** @type { [typeof __VLS_components.VTabsWindowItem, typeof __VLS_components.VTabsWindowItem, ] } */
    // @ts-ignore
    const __VLS_26 = __VLS_asFunctionalComponent(__VLS_25, new __VLS_25({ value: ("otp"), }));
    const __VLS_27 = __VLS_26({ value: ("otp"), }, ...__VLS_functionalComponentArgsRest(__VLS_26));
    // @ts-ignore
    [RegisterOTP,];
    // @ts-ignore
    const __VLS_31 = __VLS_asFunctionalComponent(RegisterOTP, new RegisterOTP({ ...{ 'onSuccess': {} }, email: ((__VLS_ctx.registerEmail)), }));
    const __VLS_32 = __VLS_31({ ...{ 'onSuccess': {} }, email: ((__VLS_ctx.registerEmail)), }, ...__VLS_functionalComponentArgsRest(__VLS_31));
    let __VLS_36;
    const __VLS_37 = {
        onSuccess: (__VLS_ctx.handleSuccess)
    };
    let __VLS_33;
    let __VLS_34;
    var __VLS_35;
    __VLS_nonNullable(__VLS_30.slots).default;
    var __VLS_30;
    __VLS_nonNullable(__VLS_11.slots).default;
    var __VLS_11;
    __VLS_nonNullable(__VLS_4.slots).default;
    var __VLS_4;
    var __VLS_slots;
    var __VLS_inheritedAttrs;
    const __VLS_refs = {};
    var $refs;
    var $el;
    return {
        attrs: {},
        slots: __VLS_slots,
        refs: $refs,
        rootEl: $el,
    };
}
;
const __VLS_self = (await import('vue')).defineComponent({
    setup() {
        return {
            RegisterForm: RegisterForm,
            RegisterOTP: RegisterOTP,
            AuthLayout: AuthLayout,
            registerStatus: registerStatus,
            registerEmail: registerEmail,
            handleSuccess: handleSuccess,
        };
    },
});
export default (await import('vue')).defineComponent({
    setup() {
        return {};
    },
    __typeEl: {},
});
; /* PartiallyEnd: #4569/main.vue */
