import Vue from 'vue'
import AdminSettings from './views/AdminSettings'

Vue.prototype.t = t
Vue.prototype.n = n

export default new Vue({
    el: '#notifications-admin-settings',
    render: h => h(AdminSettings),
})