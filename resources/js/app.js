import { createApp } from 'vue'
import '../css/app.css'

import Dashboard from '@/pages/Dashboard.vue'
import FeatureDetail from '@/pages/FeatureDetail.vue'

const mounts = [
    { el: '#dashboard-app', component: Dashboard },
    { el: '#feature-app', component: FeatureDetail },
]

mounts.forEach(({ el, component }) => {
    const root = document.querySelector(el)
    if (!root) return

    const props = root.dataset.props ? JSON.parse(root.dataset.props) : {}
    createApp(component, props).mount(root)
})
