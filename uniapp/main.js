import App from './App'

import Vue from 'vue'
import './uni.promisify.adaptor'
Vue.config.productionTip = false
App.mpType = 'app'

import config from './config'
import './mixins'
Vue.prototype.config = config

import uView from 'dry-uview-ui'
Vue.use(uView)
// 如此配置即可
uni.$u.config.unit = 'rpx'

const app = new Vue({
	...App
})
app.$mount()