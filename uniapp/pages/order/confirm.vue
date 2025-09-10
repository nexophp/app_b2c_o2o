<template>
	<view class="confirm-container">
		<!-- 收货地址 -->
		<view class="address-section" @click="selectAddress">
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
					<text class="goods-spec">{{ item.spec || '' }} {{ item.attr || '' }}</text>
					<view class="goods-bottom">
						<text class="goods-price">¥{{ item.price }}</text>
						<text class="goods-quantity">x{{ item.num }}</text>


					</view>
					<text class="goods-err" v-if="item.has_err"> {{ item.err }}</text>
				</view>
			</view>
		</view>



		<!-- 订单备注 -->
		<view class="remark-section">
			<view class="section-title">订单备注</view>
			<textarea class="remark-input" v-model="orderRemark" placeholder="选填，请输入订单备注"></textarea>
		</view>

		<!-- 费用明细 -->
		<view class="cost-section">
			<view class="cost-item">
				<text class="cost-label">商品金额</text>
				<text class="cost-value">¥{{ confirmData.amount || '' }}</text>
			</view>
			<view class="cost-item" @click="open_coupon()">
				<text class="cost-label">优惠券</text>
				<view class="coupon-info" v-if="confirmData.coupon && confirmData.coupon.id > 0">
					<text class="cost-value total-price">-¥{{ confirmData.coupon.amount }}</text>
				</view>
				<view class="no-coupon" v-else>
					<text class="cost-value">选择优惠券</text>
					<uni-icons type="right" size="16"></uni-icons>
				</view>
			</view>
			<view class="cost-item">
				<text class="cost-label">配送费</text>
				<text class="cost-value total-price" v-if="confirmData.shipping > 0">¥{{ confirmData.shipping }}</text>
				<text class="cost-value" v-else>免运费</text>
			</view>
			<view class="cost-item total">
				<text class="cost-label">实付款</text>
				<text class="cost-value total-price">¥{{ confirmData.real_amount || '' }}</text>
			</view>
		</view>

		<!-- 底部提交按钮 -->
		<view class="bottom-submit" v-if="confirmData.real_amount>0">
			<view class="submit-info">
				<text class="total-text">实付：¥{{ confirmData.real_amount || '' }}</text>
			</view>
			<view class="submit-btn" v-if="!confirmData.has_err" @click="submitOrder">提交订单</view>
			<view class="submit-btn disabled" v-else>提交订单</view>


		</view>
	</view>
</template>

<script>

export default {
	data() {
		return {
			selectedAddress: null,
			orderGoods: [],
			orderRemark: '',
			confirmData: {},
			loading: false,
			select_coupon: 1,
			type: 'direct'
		}
	},
	onLoad(options) {
		this.type = options.type || 'direct'
		// 从购物车传递过来的商品数据
		if (options.items) {
			try {
				this.orderGoods = JSON.parse(decodeURIComponent(options.items))
				// 加载默认地址
				this.loadDefaultAddress()
			} catch (e) {
				console.error('解析商品数据失败:', e)
			}
		}
		this.load()

		// 监听地址选择事件
		uni.$on('selectAddress', (address) => {
			if (address) {
				this.selectedAddress = address
				// 重新计算订单确认信息
				this.confirmOrder()
			}

		})

		// 监听优惠券选择事件
		uni.$on('selectCoupon', (coupon) => {
			this.confirmData.coupon = coupon
			this.select_coupon = 0
			// 重新计算订单确认信息
			this.confirmOrder()
		})
	},
	onUnload() {
		// 移除事件监听
		uni.$off('selectAddress')
		uni.$off('selectCoupon')
	},
	computed: {
	},
	methods: {
		load() {
			if (this.type == 'cart') {
				this.ajax(this.config.cart.index_selected, {}).then(res => {
					this.orderGoods = res.data
					// 加载默认地址
					this.loadDefaultAddress() 
				}) 
			}
		},
		open_coupon() {
			// 跳转到优惠券选择页面
			uni.navigateTo({
				url: `/pages/coupon/select?orderItems=${JSON.stringify(this.orderGoods)}&amount=${this.confirmData.amount || 0}&selectedCouponId=${this.confirmData.coupon && this.confirmData.coupon.id ? this.confirmData.coupon.id : ''}`

			})
		},
		// 加载默认地址
		loadDefaultAddress() {
			this.ajax(this.config.address.default, {}).then(res => {
				if (res.code === 0 && res.data) {
					this.selectedAddress = res.data
					this.confirmOrder()
				}
			})
		},

		// 选择地址
		selectAddress() {
			uni.navigateTo({
				url: '/pages/address/address?from=order'
			})
		},

		// 选择配送方式
		selectDelivery(index) {
			this.selectedDelivery = index
			// 重新计算订单确认信息
			this.confirmOrder()
		},

		// 订单确认
		confirmOrder() {
			const items = this.orderGoods.map(item => ({
				product_id: item.product_id,
				title: item.title,
				price: parseFloat(item.price),
				num: parseInt(item.quantity || item.num),
				image: item.image,
				spec: item.spec,
				attr: item.attr,
			}))
			const params = {
				items: items,
				address_id: this.selectedAddress ? this.selectedAddress.id : 0,
				coupon: this.confirmData.coupon || null,
				select_coupon: this.select_coupon,
				shipping: this.confirmData.shipping || 0,

			}

			this.ajax(this.config.order.confirm, params).then(res => {
				if (res.code === 0) {
					this.confirmData = res.data
					// 更新商品列表为服务器返回的数据
					if (res.data.items) {
						this.orderGoods = res.data.items.map(item => ({
							product_id: item.product_id,
							title: item.title,
							spec: item.spec,
							attr: item.attr,
							price: item.real_price,
							num: item.num,
							image: item.image,
							err: item.err || '',
							has_err: item.has_err || false,
						}))
						console.log(this.orderGoods)
					}
				} else {

				}
			})
		},

		// 提交订单
		submitOrder() {
			if (!this.selectedAddress) {
				uni.showToast({
					title: '请选择收货地址',
					icon: 'none'
				})
				return
			}

			if (this.loading) {
				return
			}

			this.loading = true
			uni.showLoading({
				title: '正在提交订单'
			})

			const items = this.orderGoods.map(item => ({
				product_id: item.product_id,
				title: item.title,
				price: parseFloat(item.price),
				num: parseInt(item.quantity || item.num),
				image: item.image,
				spec: item.spec,
				attr: item.attr,
			}))

			const orderData = {
				items: items,
				type: 'product_o2o',
				sys_tag: 'product',
				amount: this.confirmData ? this.confirmData.total_amount : this.goodsTotal,
				real_amount: this.confirmData ? this.confirmData.final_amount : this.totalAmount,
				payment_method: 'weixin',
				address_id: this.selectedAddress.id,
				address: this.selectedAddress.address,
				phone: this.selectedAddress.phone,
				name: this.selectedAddress.name,
				desc: this.orderRemark,
				coupon: this.confirmData.coupon || "",
				shipping: this.confirmData.shipping || 0,

			}

			this.ajax(this.config.order.create, orderData).then(res => {
				uni.hideLoading()
				this.loading = false

				if (res.code === 0) { 
					// 跳转到支付页面
					setTimeout(() => {
						uni.navigateTo({
							url: `/pages/order/pay?id=${res.data.id}`
						})
					}, 1500)
				} else {
					uni.showToast({
						title: res.msg || '订单创建失败',
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
	cursor: pointer;
}

.coupon-info {
	display: flex;
	flex-direction: column;
	align-items: flex-end;
}

.coupon-name {
	font-size: 24rpx;
	color: #999;
	margin-top: 5rpx;
}

.no-coupon {
	display: flex;
	align-items: center;
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

.disabled {
	background: #ccc;

}

.goods-err {
	font-size: 24rpx;
	color: #ff6b35;
	font-weight: bold;
	/**
	* css 闪烁
	*/
	animation: blink 1s linear infinite;
}

@keyframes blink {
	0% {
		opacity: 1;
	}

	50% {
		opacity: 0;
	}

	100% {
		opacity: 1;
	}
}
</style>
