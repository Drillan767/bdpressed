export default [
    {
        path: '/',
        name: 'visitors',
        component: () => import('@/layouts/VisitorLayout.vue'),
        children: [
            {
                path: '/',
                name: 'visitors.landing',
                component: () => import('@/pages/index.vue'),
                meta: {
                    requiresAuth: false,
                },
            },
            {
                path: '/boutique',
                name: 'visitors.boutique',
                component: () => import('@/pages/boutique/index.vue'),
                meta: {
                    requiresAuth: false,
                },
            },
            {
                path: '/boutique[slug]',
                name: 'visitors.article_detail',
                component: () => import('@/pages/boutique/[slug].vue'),
                meta: {
                    requiresAuth: false,
                },
            },
            {
                path: '/contact',
                name: 'visitors.contact',
                component: () => import('@/pages/contact.vue'),
                meta: {
                    requiresAuth: false,
                },
            },

            // AUTH ROUTES
            {
                path: '/connexion',
                name: 'Connexion',
                component: () => import('@/pages/connexion.vue'),
                meta: {
                    requiresAuth: false,
                },
            },
            {
                path: '/inscription',
                name: 'Inscription',
                component: () => import('@/pages/inscription.vue'),
                meta: {
                    requiresAuth: false,
                },
            },
            {
                path: '/oubli-mot-de-passe',
                name: 'Mot de passe oublié',
                component: () => import('@/pages/oubli-mot-de-passe.vue'),
                meta: {
                    requiresAuth: false,
                },
            },
        ],
    },
]
