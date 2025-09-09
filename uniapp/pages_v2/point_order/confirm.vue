<template>
	<view class="confirm-container">
		<!-- 收货地址 -->
		<view class="address-section" @click="selectAddress" v-if="product_type == 'point_1'"> 
			<view v-if="selectedAddress" class="address-info">
				<view class="address-header">
					<text class="receiver-name">{{ selectedAddress.name }}</text>
					<text class="receiver-phone">{{ selectedAddress.phone }}</text>
				</view>
				<text class="address-detail">{{ selectedAddress.address }}</text>
			</view>
			<view v-else class="no-address">
				<text class="add-address-text">请选择收货地址</text>
			</view>
			<text class="arrow">></text>
		</view>

		<!-- 商品列表 -->
		<view class="goods-section">
			<view class="section-title">商品信息</view>
			<view class="goods-item" v-for="(item, index) in orderGoods" :key="index">
				<image class="goods-image" :src="item.image" mode="aspectFill"></image>
				<view class="goods-info">
					<text class="goods-name">{{ item.name }}</text>
					<text class="goods-spec">{{ item.spec || '' }}</text>
					<view class="goods-bottom">
						<text class="goods-price">积分：{{ item.point }}</text>
						<text class="goods-quantity">x1</text>
					</view>
				</view>
			</view>
		</view>



		<!-- 订单备注 -->
		<view class="remark-section">
			<view class="section-title">订单备注</view>
			<textarea class="remark-input" v-model="orderRemark" placeholder="选填，请输入订单备注"></textarea>
		</view>


		<!-- 底部提交按钮 -->
		<view class="bottom-submit">
			<view class="submit-info">
				<text class="total-text">需要积分：{{ point || '' }}</text>
			</view>
			<view class="submit-btn" @click="submitOrder" v-if="user_point - point >= 0">确认兑换</view>
			<view class="submit-btn disabled" v-else>积分不足</view>

		</view>
	</view>
</template>

<script>

export default {
	data() {
		return {
			product_type: '', 
			selectedAddress: {},
			orderGoods: [],
			orderRemark: '',
			confirmData: {},
			loading: false,
			point: 0,
			user_point: 0,
		}
	},
	onLoad(options) {
		this.product_type = options.product_type

		this.get_point()
		// 从购物车传递过来的商品数据
		if (options.items) {
			try {
				this.orderGoods = JSON.parse(decodeURIComponent(options.items))
				// 计算总积分
				this.point = this.orderGoods.reduce((total, item) => total + (item.point || 0), 0)
			} catch (e) {
			}
		}

		// 加载默认地址
		if (this.product_type == 'point_1') {
			this.loadDefaultAddress()
		}
		// 监听地址选择事件
		uni.$on('selectAddress', (address) => {
			this.selectedAddress = address
		})
	},
	onUnload() {
		// 移除事件监听
		uni.$off('selectAddress')
	},
	computed: {
	},
	methods: {
		get_point() {
			this.ajax(this.config.point.total, {}).then(res => {
				this.user_point = res.data
			})
		},
		// 加载默认地址
		loadDefaultAddress() {
			this.ajax(this.config.address.default, {}).then(res => {
				if (res.code === 0 && res.data) {
					this.selectedAddress = res.data
				}
			}).catch(err => {
				console.error('加载默认地址失败:', err)
			})
		},

		// 选择地址
		selectAddress() {
			uni.navigateTo({
				url: '/pages/address/address?from=order'
			})
		},
		// 提交订单
		submitOrder() { 
			if (this.loading) {
				return
			}

			this.loading = true
			uni.showLoading({
				title: '创建订单中...'
			})

			const items = this.orderGoods

			const orderData = {
				items: items,
				type: 'point',
				payment_method: 'point',
				address_id: this.selectedAddress.id || '',
				address: this.selectedAddress.address|| '',
				phone: this.selectedAddress.phone|| '',
				name: this.selectedAddress.name|| '',
				desc: this.orderRemark, 
			}

			this.ajax(this.config.point.create_order, orderData).then(res => {
				uni.hideLoading()
				this.loading = false
				if (res.code === 0) {
					uni.showToast({
						title: '兑换成功',
						icon: 'success'
					})
					setTimeout(() => {
						this.jump('/pages/point/point') 
					}, 1000)

				} else {
					uni.showToast({
						title: res.msg,
						icon: 'none'
					})
				}
			}).catch(err => {
				uni.hideLoading()
				this.loading = false
				console.error('创建订单失败:', err)
				uni.showToast({
					title: '网络错误',
					icon: 'none'
				})
			})
		}
	}
}
</script>

<style>
.confirm-container {
	background: #f5f5f5;
	min-height: 100vh;
	padding-bottom: 120rpx;
}

.address-section {
	background: #fff;
	padding: 30rpx;
	margin-bottom: 20rpx;
	display: flex;
	align-items: center;
	justify-content: space-between;
}

.address-info {
	flex: 1;
}

.address-header {
	display: flex;
	align-items: center;
	margin-bottom: 10rpx;
}

.receiver-name {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
	margin-right: 20rpx;
}

.receiver-phone {
	font-size: 28rpx;
	color: #666;
}

.address-detail {
	font-size: 28rpx;
	color: #666;
	line-height: 1.4;
}

.no-address {
	flex: 1;
}

.add-address-text {
	font-size: 28rpx;
	color: #999;
}

.arrow {
	font-size: 32rpx;
	color: #ccc;
}

.goods-section,
.delivery-section,
.remark-section,
.cost-section {
	background: #fff;
	margin-bottom: 20rpx;
	padding: 30rpx;
}

.section-title {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
	margin-bottom: 20rpx;
}

.goods-item {
	display: flex;
	align-items: center;
	margin-bottom: 20rpx;
}

.goods-item:last-child {
	margin-bottom: 0;
}

.goods-image {
	width: 100rpx;
	height: 100rpx;
	border-radius: 10rpx;
	margin-right: 20rpx;
}

.goods-info {
	flex: 1;
}

.goods-name {
	font-size: 28rpx;
	color: #333;
	display: block;
	margin-bottom: 10rpx;
}

.goods-spec {
	font-size: 24rpx;
	color: #999;
	display: block;
	margin-bottom: 10rpx;
}

.goods-bottom {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.goods-price {
	font-size: 28rpx;
	color: #ff6b35;
	font-weight: bold;
}

.goods-quantity {
	font-size: 24rpx;
	color: #999;
}

.delivery-item {
	display: flex;
	align-items: center;
	padding: 20rpx 0;
	border-bottom: 2rpx solid #f5f5f5;
}

.delivery-item:last-child {
	border-bottom: none;
}

.delivery-radio {
	font-size: 32rpx;
	color: #ddd;
	margin-right: 20rpx;
}

.delivery-radio.checked {
	color: #ff6b35;
}

.delivery-info {
	flex: 1;
}

.delivery-name {
	font-size: 28rpx;
	color: #333;
	display: block;
	margin-bottom: 5rpx;
}

.delivery-desc {
	font-size: 24rpx;
	color: #999;
	display: block;
}

.delivery-fee {
	font-size: 28rpx;
	color: #ff6b35;
	font-weight: bold;
}

.remark-input {
	width: 100%;
	height: 120rpx;
	border: 2rpx solid #eee;
	border-radius: 10rpx;
	padding: 20rpx;
	font-size: 28rpx;
	color: #333;
	background: #f9f9f9;
}

.cost-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 15rpx 0;
}

.cost-item.total {
	border-top: 2rpx solid #eee;
	padding-top: 20rpx;
	margin-top: 10rpx;
}

.cost-label {
	font-size: 28rpx;
	color: #666;
}

.cost-value {
	font-size: 28rpx;
	color: #333;
}

.total-price {
	font-size: 32rpx;
	color: #ff6b35;
	font-weight: bold;
}

.bottom-submit {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	height: 100rpx;
	background: #fff;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0 30rpx;
	border-top: 2rpx solid #eee;
}

.total-text {
	font-size: 32rpx;
	color: #ff6b35;
	font-weight: bold;
}

.submit-btn {
	padding: 20rpx 60rpx;
	background: #ff6b35;
	color: #fff;
	border-radius: 50rpx;
	font-size: 28rpx;
	font-weight: bold;
}

.submit-btn.disabled {
	background: #ccc;
}
</style>
