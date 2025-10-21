<template>
	<view class="pay-container" v-if="order.real_amount > 0">
		<!-- 订单信息 -->
		<view class="order-info">
			<view class="order-amount">
				<text class="amount-label">支付金额</text>
				<text class="amount-value">¥{{order.real_amount}}</text>
			</view>
			<view class="order-detail">
				<text class="detail-text">订单号：{{order.order_num}}</text>
				<text class="detail-text">创建时间：{{order.created_at_format}}</text>
			</view>
			
			<!-- 支付按钮 -->
			
		</view>
		
		<view class="pay-btn-container">
			<button class="pay-btn" @click="confirmPay">确认支付 ¥{{order.real_amount}}</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				order: {}, 
				id: '', 
			}
		},
		onLoad(options) {
			if (options.id) {
				this.id = options.id
				this.load() 
			} 
		},
		methods: {
			 load(){
				this.ajax(this.config.order.view,{id:this.id}).then(res=>{
					this.order = res.data; 
				})
			 },
			 confirmPay(){
				uni.showLoading()
				this.weixin_pay(this.order.order_num, this.order.real_amount, '购物')
			 },
			 paid(){
				this.jump('/pages/order/order')
			 },
			 unpaid(){
				uni.hideLoading()
			 }
		}
	}
</script>

<style>
	.pay-container {
		background: #f5f5f5;
		min-height: 100vh;
		padding: 30rpx;
	}
	
	.order-info {
		background: #fff;
		border-radius: 16rpx;
		padding: 40rpx;
		box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
	}
	
	.order-amount {
		margin-bottom: 40rpx;
		text-align: center;
	}
	
	.amount-label {
		font-size: 28rpx;
		color: #666;
		display: block;
		margin-bottom: 15rpx;
	}
	
	.amount-value {
		font-size: 48rpx;
		color: #ff6b35;
		font-weight: bold;
		display: block;
	}
	
	.order-detail {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 15rpx;
		margin-bottom: 50rpx;
	}
	
	.detail-text {
		font-size: 26rpx;
		color: #999;
	}
	
	.pay-btn-container {
		margin-top: 50rpx;
	}
	
	.pay-btn {
		width: 100%;
		height: 90rpx;
		line-height: 90rpx;
		background: #ff6b35;
		color: #fff;
		border-radius: 45rpx;
		font-size: 32rpx;
		font-weight: bold;
		border: none;
	}
	
	.pay-btn:active {
		opacity: 0.9;
	}
</style>