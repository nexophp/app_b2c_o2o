<template>
	<view class="order-list">
		<!-- 状态筛选 -->
		<view class="status-tabs">
			<view v-for="(tab, index) in statusTabs" :key="index"
				:class="['tab-item', currentTab === index ? 'active' : '']" @click="switchTab(index)">
				{{ tab.name }}
			</view>
		</view>

		<!-- 订单列表 -->
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">
			<view class="order-container">
				<view v-for="order in orderList" class="order-item" @click="goToOrderDetail(order.id)">
					<!-- 订单头部 -->
					<view class="order-header">
						<text class="order-no">订单号：{{ order.order_num }}</text>
						<text :class="'status-' + order.status">{{ order.status_text }}</text>
					</view>

					<!-- 商品信息 -->
					<view class="product-list">
						<view v-for="product in order.products" :key="product.id" class="product-item">
							<image :src="product.image" class="product-image"></image>
							<view class="product-info">
								<text class="product-name">{{ product.title }}</text>
								<text class="product-spec">{{ product.spec || '' }} {{ product.attr || '' }}</text>
								<view class="product-price-qty">
									<text class="product-price">¥{{ product.price }}</text>
									<text class="product-qty">x{{ product.num }}</text>
								</view>
							</view>
						</view>
					</view>

					<!-- 订单底部 -->
					<view class="order-footer">
						<text class="total-amount">共{{ order.num }}件</text>
						<view class="action-buttons">
							<cl-button v-for="action in order.actions" :key="action.type" :type="action.uniType"
								size="mini" @click.stop="handleAction(action.type, order)">
								{{ action.text }}
							</cl-button>
						</view>
					</view>
				</view>
			</view>
 

		</mescroll-body>
	</view>
</template>

<script>
var _this
import MescrollMixin from "@/uni_modules/mescroll-uni/components/mescroll-uni/mescroll-mixins.js";

export default {
	mixins: [MescrollMixin],
	data() {
		return {
			currentTab: 0,
			statusTabs: [
				{ name: '全部', status: '' },
				{ name: '待付款', status: 'wait' },
				{ name: '待发货', status: 'paid' },
				{ name: '待收货', status: 'shipped' },
				{ name: '已完成', status: 'complete' }
			],
			where: {
				page: 1,
				page_size: 10,
				status: ''
			},
			upOption: {
				page: {
					size: 10 // 每页数据的数量,默认10
				},
				noMoreSize: 5, // 配置列表的总数量要大于等于5条才显示'-- END --'的提示
				empty: {
					tip: '暂无订单数据'
				},
				textNoMore: '-- 到底了 --'
			},
			downOption: {
				auto: false, //是否在初始化后,自动执行downCallback; 默认true 
			},
			orderList: []
		}
	},
	onLoad() {
		_this = this
	},
	onShow() {
		// 页面显示时重新加载数据
		if (this.mescroll) {
			this.mescroll.resetUpScroll()
		}
	},
	methods: {
		goShopping() {
			this.jump('/pages/index/index')
		},
		/*下拉刷新的回调 */
		downCallback() {
			this.where.page = 1
			this.load()
			this.mescroll.resetUpScroll();
		},
		upCallback(page) {
			this.where.page = page.num
			this.load()
		},
		reload() {
			this.where.page = 1
			this.load()
		},
		load() {
			let w = this.where
			w.type = 'product_o2o'
			_this.ajax(_this.config.order.index, w).then(res => {
				if (res.current_page == 1) {
					_this.orderList = []
				}
				for (let i in res.data) {
					let order = res.data[i]
					order.actions = _this.getOrderActions(order)
					_this.orderList.push(order)
				}
				_this.mescroll.endBySize(res.per_page, res.total);
			})
		},
		getOrderActions(order) {
			const actions = []
			switch (order.status) {
				case 'wait':
					actions.push({ type: 'cancel', text: '取消订单', uniType: 'default' })
					actions.push({ type: 'pay', text: '立即付款', uniType: 'primary' })
					break
				case 'paid':
					actions.push({ type: 'contact', text: '联系客服', uniType: 'default' })
					break
				case 'shipped':
					actions.push({ type: 'logistics', text: '查看物流', uniType: 'default' })
					actions.push({ type: 'confirm', text: '确认收货', uniType: 'primary' })
					break
				case 'completed':
					actions.push({ type: 'review', text: '评价', uniType: 'default' })
					actions.push({ type: 'rebuy', text: '再次购买', uniType: 'primary' })
					break
			}
			return actions
		},
		mescrollInit(mescroll) {
			this.mescroll = mescroll
		},
		switchTab(index) {
			this.currentTab = index
			this.where.status = this.statusTabs[index].status
			this.where.page = 1
			this.orderList = []
			if (this.mescroll) {
				this.mescroll.resetUpScroll()
			}
		},
		goToOrderDetail(orderId) {
			uni.navigateTo({
				url: `/pages/order/view?id=${orderId}`
			})
		},
		handleAction(type, order) {
			switch (type) {
				case 'cancel':
					this.cancelOrder(order)
					break
				case 'pay':
					this.payOrder(order)
					break
				case 'contact':
					this.contactService()
					break
				case 'logistics':
					this.viewLogistics(order)
					break
				case 'confirm':
					this.confirmReceive(order)
					break
				case 'review':
					this.reviewOrder(order)
					break
				case 'rebuy':
					this.rebuyOrder(order)
					break
			}
		},
		cancelOrder(order) {
			uni.showModal({
				title: '取消订单',
				content: '确定要取消这个订单吗？',
				success: async (res) => {
					if (res.confirm) {
						this.ajax(this.config.order.update, {
							id: order.id,
							status: 'cancel'
						}).then(res => {
							uni.showToast({
								title: res.msg,
								icon: 'none'
							})
							this.back()
						})
					}
				}
			})
		},
		payOrder(order) {
			uni.navigateTo({
				url: `/pages/order/pay?id=${order.id}`
			})
		},
		contactService() {
			uni.showToast({
				title: '联系客服功能',
				icon: 'none'
			})
		},
		viewLogistics(order) {
			this.jump('/pages/order/logic?id=' + order.id)
		},
		confirmReceive(order) {
			uni.showModal({
				title: '确认收货',
				content: '确定已收到商品吗？',
				success: (res) => {
					if (res.confirm) {
						_this.ajax(_this.config.order.confirm, { id: order.id }).then(res => {
							uni.showToast({
								title: res.msg
							})
							_this.reload()
						})
					}
				}
			})
		},
		reviewOrder(order) {
			uni.showToast({
				title: '评价功能',
				icon: 'none'
			})
		},
		rebuyOrder(order) {
			uni.showToast({
				title: '再次购买功能',
				icon: 'none'
			})
		}
	}
}
</script>

<style>
.order-list {
	background-color: #f5f5f5;
	min-height: 100vh;
}

.status-tabs {
	display: flex;
	background-color: #fff;
	border-bottom: 1px solid #eee;
}

.tab-item {
	flex: 1;
	padding: 30rpx 0;
	text-align: center;
	font-size: 28rpx;
	color: #666;
	position: relative;
}

.tab-item.active {
	color: #007aff;
	font-weight: bold;
}

.tab-item.active::after {
	content: '';
	position: absolute;
	bottom: 0;
	left: 50%;
	transform: translateX(-50%);
	width: 60rpx;
	height: 4rpx;
	background-color: #007aff;
}

.order-container {
	padding: 20rpx;
}

.order-item {
	background-color: #fff;
	border-radius: 16rpx;
	margin-bottom: 20rpx;
	padding: 30rpx;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.1);
}

.order-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
	padding-bottom: 20rpx;
	border-bottom: 1px solid #f0f0f0;
}

.order-no {
	font-size: 28rpx;
	color: #333;
}

.status-wait {
	font-size: 28rpx;
	color: #ff6b35;
	font-weight: bold;
}

.status-paid {
	font-size: 28rpx;
	color: #007aff;
	font-weight: bold;
}

.status-shipped {
	font-size: 28rpx;
	color: #ff9500;
	font-weight: bold;
}

.status-complete {
	font-size: 28rpx;
	color: #34c759;
	font-weight: bold;
}
.status-cancel,.status-close {
	font-size: 28rpx;
	color: #999;
	font-weight: bold;
}

.product-list {
	margin-bottom: 20rpx;
}

.product-item {
	display: flex;
	margin-bottom: 20rpx;
}

.product-item:last-child {
	margin-bottom: 0;
}

.product-image {
	width: 120rpx;
	height: 120rpx;
	border-radius: 12rpx;
	margin-right: 20rpx;
}

.product-info {
	flex: 1;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
}

.product-name {
	font-size: 28rpx;
	color: #333;
	margin-bottom: 10rpx;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.product-spec {
	font-size: 24rpx;
	color: #999;
	margin-bottom: 10rpx;
}

.product-price-qty {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.product-price {
	font-size: 28rpx;
	color: #ff6b35;
	font-weight: bold;
}

.product-qty {
	font-size: 24rpx;
	color: #999;
}

.order-footer {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding-top: 20rpx;
	border-top: 1px solid #f0f0f0;
}

.total-amount {
	font-size: 28rpx;
	color: #333;
}

.action-buttons {
	display: flex;
	gap: 20rpx;
}

.action-buttons button {
	padding: 16rpx 32rpx;
	border-radius: 40rpx;
	font-size: 24rpx;
	border: 1px solid transparent;
	line-height: 1;
	box-sizing: border-box;
}

.btn-cancel {
	background-color: #f5f5f5;
	color: #666;
	border: 1px solid #e0e0e0;
}

.btn-pay {
	background-color: #ff6b35;
	color: #fff;
	border: 1px solid #ff6b35;
}

.btn-contact {
	background-color: #f5f5f5;
	color: #666;
	border: 1px solid #e0e0e0;
}

.btn-logistics {
	background-color: #f5f5f5;
	color: #666;
	border: 1px solid #e0e0e0;
}

.btn-confirm {
	background-color: #007aff;
	color: #fff;
	border: 1px solid #007aff;
}

.btn-review {
	background-color: #f5f5f5;
	color: #666;
	border: 1px solid #e0e0e0;
}

.btn-rebuy {
	background-color: #ff6b35;
	color: #fff;
	border: 1px solid #ff6b35;
}

.empty-state {
	text-align: center;
	padding: 100rpx 0;
}

.empty-text {
	font-size: 28rpx;
	color: #999;
}
</style>
