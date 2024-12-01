export default [
    {
        path: '/administration',
        name: 'administration',
        component: () => import('@/layouts/AdminLayout.vue'),
        children: [
            {
                path: '',
                name: 'administration.dashboard',
                component: () => import('@/pages/administration/index.vue'),
                meta: {
                    requiresAuth: true,
                    requiresRole: 'admin',
                },
            },
            // ARTICLES
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

            // USERS
            {
                path: '/administration/utilisateurs',
                name: 'administration.users',
                component: () => import('@/pages/administration/users/index.vue'),
                meta: {
                    requiresAuth: true,
                    requiresRole: 'admin',
                },
            },
            {
                path: '/administration/utilisateurs/:id',
                name: 'administration.users.id',
                component: () => import('@/pages/administration/users/[id].vue'),
                meta: {
                    requiresAuth: true,
                    requiresRole: 'admin',
                },
            },
        ],
    },
]
