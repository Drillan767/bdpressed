export default [
    {
        path: '/administration',
        name: 'administration',
        component: () => import('@/layouts/AdminLayout.vue'),
        children: [
            {
                path: '/administration',
                name: 'administration.dashboard',
                component: () => import('@/pages/administration/index.vue'),
                meta: {
                    requiresAuth: true,
                    requiresRole: 'admin',
                },
            },
            {
                path: '/administration/articles',
                name: 'administration.articles',
                component: () => import('@/pages/administration/articles/index.vue'),
                meta: {
                    requiresAuth: true,
                    requiresRole: 'admin',
                },
            },
            {
                path: '/administration/articles/:id',
                name: 'administration.articles.id',
                component: () => import('@/pages/administration/articles/[id].vue'),
                meta: {
                    requiresAuth: true,
                    requiresRole: 'admin',
                },
            },
        ],
    },
]
