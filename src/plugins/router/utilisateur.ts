export default [
    {
        path: '/utilisateur',
        name: 'utilisateur',
        component: () => import('@/layouts/UserLayout.vue'),
        children: [
            {
                path: '/utilisateur',
                name: 'utilisateur.dashboard',
                component: () => import('@/pages/utilisateur/index.vue'),
                meta: {
                    requiresAuth: true,
                    requiresRole: 'user',
                },
            },
        ],
    },
]
