<template>
	<view class="order-detail">
		<!-- 加载状态 -->
		<view v-if="loading" class="loading-section">
			<uni-load-more status="loading"
				:content-text="{ contentdown: '加载中...', contentrefresh: '加载中...', contentnomore: '加载中...' }"></uni-load-more>
		</view>

		<view v-else>  
			<!-- 商品信息 -->
			<view class="product-section">
				<view class="section-title">已购服务商品</view>
				<view class="product-list">
					<view v-for="product in orderInfo.products" class="product-item"> 
						<view class="product-info">
							<text class="product-name">{{ product.title }}</text>
							<text class="product-spec">{{ product.spec || '' }}</text>
							<view class="product-price-qty">
								<text class="product-price">¥{{ product.price || 0 }}</text>
								<text class="product-qty">x{{ product.num }}</text>
								 
							</view>
						</view>
					</view>
				</view>
			</view>

			<!-- 订单信息 -->
			<view class="order-info-section">
				<view class="section-title">订单信息</view>
				<view class="info-list">
					<view class="info-item">
						<text class="info-label">订单编号</text>
						<text class="info-value">{{ orderInfo.order_num }}</text>
					</view>
					<view class="info-item">
						<text class="info-label">下单时间</text>
						<text class="info-value">{{ orderInfo.created_at_format }}</text>
					</view>


					<view v-if="orderInfo.desc" class="info-item">
						<text class="info-label">订单备注</text>
						<text class="info-value">{{ orderInfo.desc || '' }}</text>
					</view>
				</view>
			</view>

			<!-- 费用明细 -->
			<view class="cost-section">
				<view class="section-title">费用明细</view>
				<view class="cost-list">
 
					<view class="cost-item total">
						<text class="cost-label">订单金额</text>
						<text class="cost-value ">¥{{ orderInfo.real_amount || 0 }}</text>
					</view>
 
					<view class="cost-item total">
						<text class="cost-label">实付金额</text>
						<text class="cost-value total-amount">¥{{ orderInfo.real_get_amount || 0 }}</text>
					</view>
				</view>
			</view>
 
		</view>

		<!-- 底部安全距离 -->
		<view class="bottom-safe-area"></view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			orderId: '',
			loading: true,
			orderInfo: {
				id: '',
				order_num: '',
				status: '',
				statusText: '',
				statusClass: '',
				statusDesc: '',
				created_at_format: '',
				updated_at_format: '',
				deliveryMethod: '快递配送',
				amount: 0,
				real_amount: 0,
				shipping_fee: 0,
				discount_amount: 0,
				address: {
					name: '',
					phone: '',
					detail: ''
				},
				logistics: null,
				items: [],
				payment_info: [],
				actions: []
			}
		}
	},
	onLoad(options) {
		if (options.id) {
			this.orderId = options.id
			this.load()
		} else {
			uni.showToast({
				title: '订单ID不能为空',
				icon: 'none'
			})
			setTimeout(() => {
				uni.navigateBack()
			}, 1500)
		}
	},
	methods: {
		// 加载订单详情
		load() {
			this.ajax(this.config.service.order.view, {
				id: this.orderId
			}).then(res => {
				this.loading = false
				if (res.code === 0 && res.data) {
					this.orderInfo = this.formatOrderData(res.data)
				} else {
					uni.showToast({
						title: res.msg || '获取订单详情失败',
						icon: 'none'
					})
				}
			})
		},

		// 格式化订单数据
		formatOrderData(data) {
			const statusMap = {
				'wait': {
					text: '待付款',
					class: 'status-pending',
					desc: '请尽快完成支付'
				},
				'paid': {
					text: '已付款',
					class: 'status-paid',
					desc: '商家正在准备发货'
				},
				'shipped': {
					text: '已发货',
					class: 'status-shipped',
					desc: '您的包裹正在运输途中'
				},
				'delivered': {
					text: '已送达',
					class: 'status-delivered',
					desc: '包裹已送达，请确认收货'
				},
				'completed': {
					text: '已完成',
					class: 'status-completed',
					desc: '订单已完成'
				},
				'cancelled': {
					text: '已取消',
					class: 'status-cancelled',
					desc: '订单已取消'
				},
				'refunded': {
					text: '已退款',
					class: 'status-refunded',
					desc: '订单已退款'
				}
			}

			const statusInfo = statusMap[data.status] || {
				text: '未知状态',
				class: 'status-unknown',
				desc: ''
			}

			// 生成操作按钮
			const actions = this.generateActions(data.status)
			return {
				...data,
				actions: actions
			}
		},

		// 生成操作按钮
		generateActions(status) {
			const actions = []

			// 联系客服按钮（所有状态都显示）
			actions.push({
				type: 'contact',
				text: '联系客服',
				uniType: 'default'
			}) 
			return actions
		},

		// 处理操作按钮点击
		async handleAction(type) {
			switch (type) {
				case 'contact':
					uni.showToast({
						title: '联系客服功能',
						icon: 'none'
					})
					break

				 
			}
		}, 

		 
	}
}
</script>

<style>
.order-detail {
	background-color: #f5f5f5;
	min-height: 100vh;
	padding-bottom: 120rpx;
}

.status-section {
	background-color: #fff;
	padding: 40rpx 30rpx;
	display: flex;
	align-items: center;
	margin-bottom: 20rpx;
}

.status-icon {
	font-size: 60rpx;
	margin-right: 30rpx;
}

.status-info {
	flex: 1;
}

.status-shipped {
	font-size: 32rpx;
	color: #ff9500;
	font-weight: bold;
	margin-bottom: 10rpx;
	display: block;
}

.status-desc {
	font-size: 26rpx;
	color: #666;
	display: block;
}

.logistics-section,
.address-section,
.product-section,
.order-info-section,
.cost-section {
	background-color: #fff;
	margin-bottom: 20rpx;
	padding: 30rpx;
}

.section-title {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
	margin-bottom: 30rpx;
}

.logistics-section {
	position: relative;
	cursor: pointer;
}

.logistics-info {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
	padding: 20rpx;
	background-color: #f8f9fa;
	border-radius: 12rpx;
}

.logistics-company {
	font-size: 30rpx;
	color: #333;
	font-weight: bold;
}

.logistics-no {
	font-size: 28rpx;
	color: #007aff;
	font-weight: 500;
}

.logistics-status {
	margin-bottom: 20rpx;
	padding: 15rpx 20rpx;
	background-color: #007aff;
	border-radius: 12rpx;
	text-align: center;
}

.status-text {
	font-size: 28rpx;
	color: #fff;
	font-weight: bold;
}

.logistics-progress {
	padding: 20rpx;
	background-color: #f5f5f5;
	border-radius: 12rpx;
}

.progress-item {
	padding: 20rpx;
	background-color: #fff;
	border-radius: 12rpx;
	border: 1px solid #e8e8e8;
}

.progress-time {
	font-size: 26rpx;
	color: #666;
	margin-bottom: 10rpx;
	display: block;
}

.progress-desc {
	font-size: 28rpx;
	color: #333;
	display: block;
	font-weight: 500;
	line-height: 1.4;
}

.logistics-arrow {
	position: absolute;
	right: 30rpx;
	top: 50%;
	transform: translateY(-50%);
	font-size: 32rpx;
	color: #ccc;
	font-weight: bold;
}

.address-info {
	line-height: 1.6;
}

.address-header {
	display: flex;
	justify-content: space-between;
	margin-bottom: 15rpx;
}

.receiver-name {
	font-size: 28rpx;
	color: #333;
	font-weight: bold;
}

.receiver-phone {
	font-size: 26rpx;
	color: #666;
}

.address-detail {
	font-size: 26rpx;
	color: #666;
	line-height: 1.5;
}

.product-list {
	/* 复用之前的商品列表样式 */
}

.product-item {
	display: flex;
	align-items: flex-start;
	margin-bottom: 30rpx;
	position: relative;
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
	position: relative;
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

.product-actions {
	margin-left: auto;
}

.refund-btn {
	background-color: #ff6b6b;
	color: white;
	border: none;
	border-radius: 6rpx;
	padding: 8rpx 12rpx;
	font-size: 20rpx;
	line-height: 1;
	min-width: auto;
}

.info-list,
.cost-list {
	/* 信息列表样式 */
}

.info-item,
.cost-item {
	display: flex;
	justify-content: space-between;
	padding: 20rpx 0;
	border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child,
.cost-item:last-child {
	border-bottom: none;
}

.info-label,
.cost-label {
	font-size: 28rpx;
	color: #666;
}

.info-value,
.cost-value {
	font-size: 28rpx;
	color: #333;
}

.cost-item.total {
	padding-top: 30rpx;
	margin-top: 20rpx;
}

.discount {
	color: #ff6b35 !important;
}

.total-amount {
	color: #ff6b35;
	font-weight: bold;
	font-size: 32rpx;
}

/* 加载状态样式 */
.loading-section {
	padding: 100rpx 0;
	text-align: center;
}

/* 支付信息样式 */
.payment-section {
	background-color: #fff;
	margin-bottom: 20rpx;
	padding: 30rpx;
}

.payment-item {
	padding: 20rpx 0;
	border-bottom: 1px solid #f0f0f0;
}

.payment-item:last-child {
	border-bottom: none;
}

.payment-info {
	display: flex;
	justify-content: space-between;
	margin-bottom: 10rpx;
}

.payment-type {
	font-size: 28rpx;
	color: #333;
}

.payment-amount {
	font-size: 28rpx;
	color: #ff6b35;
	font-weight: bold;
}

.payment-time {
	font-size: 24rpx;
	color: #999;
}

/* 底部操作按钮样式 */
.bottom-actions {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	background-color: #fff;
	padding: 20rpx 30rpx;
	border-top: 1px solid #f0f0f0;
	display: flex;
	gap: 20rpx;
	box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.1);
}

.action-btn {
	flex: 1;
	padding: 20rpx 40rpx;
	border-radius: 40rpx;
	font-size: 28rpx;
	border: 1px solid transparent;
	line-height: 1;
	box-sizing: border-box;
	text-align: center;
}

.btn-default {
	background-color: #f5f5f5;
	color: #666;
	border: 1px solid #e0e0e0;
}

.btn-primary {
	background-color: #007aff;
	color: #fff;
	border: 1px solid #007aff;
}



/* 页面容器 */
.order-detail {
	padding-bottom: env(safe-area-inset-bottom);
}
</style>