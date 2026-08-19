import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'
import i18n from '@/i18n/index.js'
import { formatNumber } from '@/utils/formatNumber.js'
import App from '@/App.vue'
import '../css/app.css'

import DashboardPage from '@/pages/DashboardPage.vue'
import ActivityPage from '@/pages/ActivityPage.vue'
import LibraryPage from '@/pages/LibraryPage.vue'
import GameDetailPage from '@/pages/GameDetailPage.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: DashboardPage,
            meta: { titleKey: 'layout.pageTitle.dashboard' },
        },
        {
            path: '/activity',
            component: ActivityPage,
            meta: { titleKey: 'layout.pageTitle.activity' },
        },
        {
            path: '/library',
            component: LibraryPage,
            meta: { titleKey: 'layout.pageTitle.gameLibrary' },
        },
        {
            path: '/game/:id',
            component: GameDetailPage,
            meta: { titleKey: 'layout.pageTitle.gameDetails' },
        },
    ],
})

const pinia = createPinia()
const app = createApp(App)

app.config.globalProperties.$formatNumber = formatNumber

if (typeof window !== 'undefined') {
    window.formatNumber = formatNumber
}

app.use(i18n)
app.use(router)
app.use(pinia)
app.mount('#app')
