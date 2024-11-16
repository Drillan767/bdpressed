export default [
    {
        path: '/connexion',
        name: 'auth.login',
        component: () => import('@/pages/auth/Login.vue'),
    },
    {
        path: '/inscription',
        name: 'auth.register',
        component: () => import('@/pages/auth/Register.vue'),
    }
]