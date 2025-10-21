<template>
	<view class="user-page">
		<!-- 顶部背景区域 -->
		<view class="header-bg">
			<!-- 状态栏占位 -->
			<view class="status-bar" :style="{ height: status_height + 'px' }"></view>
			<view class="user-info">
				<view class="avatar-section">
					<image class="avatar" :src="user_info.avatar || '/static/avatar.png'" mode="aspectFill"></image>
					<view class="user-details">
						<text class="username" v-if="user_info.phone">{{ user_info.phone }}</text>

						<view v-else>
							<!-- #ifdef MP-WEIXIN -->
							<button  class="login-btn" open-type="getPhoneNumber"
								@getphonenumber="getPhoneNumber">点击授权登录</button>
							<!-- #endif -->
							<!-- #ifdef H5 || APP -->
							<view @click="jump('/pages/login/login')"  style="color:#FFFFFF;">登录</view>
							<!-- #endif -->
							
						</view>

						<text class="user-level" v-if="user_info.level">{{ user_info.level || '' }}</text>
					</view>
				</view>
			</view>
		</view>

		<!-- 会员统计信息 -->
		<!-- <view class="stats-section">
			<view class="stat-item" @click="jump_user('/pages/coupon/my')">
				<text class="stat-number">{{coupon_count||0}}</text>
				<text class="stat-label">优惠券</text>
			</view>
			<view class="stat-item" @click="jump_user('/pages/point/point')">
				<text class="stat-number">{{point||0}}</text>
				<text class="stat-label">积分</text>
			</view>

		</view> -->

		<!-- 功能菜单 -->
		<view class="menu-section">
			<view class="menu-group">
				<view class="menu-item" @click="jump_user('/pages/order/order?type=all')">

					<text class="menu-text">我的订单</text>
					<view class="menu-right">
						<text class="menu-subtext">全部</text>
						<uni-icons type="right" size="16" color="#999"></uni-icons>
					</view>
				</view>

				<!-- 订单状态快捷入口 -->
				<view class="order-status-grid">
					<view class="status-item" @click="jump_user('/pages/order/order?type=unpaid')">
						<view class="status-icon unpaid">
							<u-icon name="rmb-circle" color="#FF4D4F" size="37"></u-icon>
						</view>
						<text class="status-text">待付款</text>
						<text class="status-badge" v-if="stat.wait > 0">{{ stat.wait }}</text>
					</view>
					<view class="status-item" @click="jump_user('/pages/order/order?type=unshipped')">
						<view class="status-icon unshipped">
							<u-icon name="car" color="#FA8C16" size="37"></u-icon>
						</view>
						<text class="status-text">待发货</text>
						<text class="status-badge" v-if="stat.paid > 0">{{ stat.paid }}</text>
					</view>
					<view class="status-item" @click="jump_user('/pages/order/order?type=shipped')">
						<view class="status-icon shipped">
							<u-icon name="coupon" color="#1890FF" size="37"></u-icon>
						</view>
						<text class="status-text">待收货</text>
						<text class="status-badge" v-if="stat.shipped > 0">{{ stat.shipped }}</text>
					</view>
					<view class="status-item" @click="jump_user('/pages/order/order?type=completed')">
						<view class="status-icon completed">
							<u-icon name="checkmark-circle" color="#52C41A" size="37"></u-icon>
						</view>
						<text class="status-text">已完成</text>
					</view>
					<view class="status-item" @click="jump_user('/pages/order/refund')">
						<view class="status-icon refund">
							<u-icon name="reload" color="#722ED1" size="37"></u-icon>
						</view>
						<text class="status-text">售后</text>
					</view>
				</view>

				 
			</view>

			<view class="menu-group">

				 

				<view class="menu-item" @click="jump_user('/pages/address/address')">
					<view class="menu-icon-box">
						<uni-icons type="location" size="22" ></uni-icons>
					</view>
					<text class="menu-text">收货地址</text>
					<view class="menu-right">
						<uni-icons type="right" size="16" color="#999"></uni-icons>
					</view>
				</view>
				<view class="menu-item" @click="contactCustomerService">
					<view class="menu-icon-box">
						<uni-icons type="headphones" size="22" ></uni-icons>
					</view>
					<text class="menu-text">联系客服</text> 
				</view>
				<view class="menu-item" @click="jump_user('/pages/blog/view?title=隐私政策')">
					<view class="menu-icon-box">
						<uni-icons type="info" size="22" ></uni-icons>

					</view>
					<text class="menu-text">隐私政策</text>
					<view class="menu-right">
						<uni-icons type="right" size="16" color="#999"></uni-icons>
					</view>
				</view>

			</view>
		</view>

		<!-- 退出登录按钮 -->
		<view class="logout-section" v-if="user_info.phone">
			<button class="logout-btn" @click="logout">退出登录</button>
		</view>
		
		<xh-privacy title="隐私保护指引" theme="direction" background="rgba(0, 0, 0, .5)" color="#2979ff"></xh-privacy>
		
	</view>
</template>

<script>
export default {
	data() {
		return {
			store:{},
			info: {},
			user_info: {},
			userInfo: {
				username: '用户昵称',
				level: 'VIP会员',
				balance: '0.00'
			},
			stat: {
			},
			wallet: {},
			is_staff: false,
			coupon_count: 0,
			point:0,
		}
	},
	onShow() {
		this.get_user_info()
		this.get_config()
		this.getOrderStats()
		this.get_wallet()
		this.get_staff() 
		this.get_coupon_count()
		this.get_point()
		this.load_info()
	},
	methods: {
		load_info() {
			this.ajax(this.config.o2o.home, {}).then(res => {
				this.store = res.data 
			})
		},
		get_point(){
			this.ajax(this.config.point.total,{}).then(res=>{
				this.point = res.data 
			})
		},
		get_coupon_count() {
			this.ajax(this.config.coupon.count, {}).then(res => {
				if (res.code == 0) {
					this.coupon_count = res.data
				}
			})

		},
		get_wallet() {
			this.ajax(this.config.wallet.index, {}).then(res => {
				if (res.code == 0) {
					this.wallet = res.data
				}
			})
		},
		get_config() {
			this.ajax(this.config.config, {}).then(res => {
				if (res.code == 0) {
					this.info = res.data
				}
			})
		},
		get_staff() {
			this.ajax(this.config.staff.is, {}).then(res => {
				if (res.code == 0) {
					this.is_staff = true 
				}else {
					this.is_staff = false
				}
			})
		},
		// 获取用户订单统计数据
		getOrderStats() {
			this.ajax(this.config.order.stat, {type:'product_o2o'}).then(res => {
				this.stat = res.data
			})
		},

		// 联系客服
		contactCustomerService() {
			uni.makePhoneCall({
				phoneNumber: this.store.phone
			})
		},

		// 退出登录
		logout() {
			uni.showModal({
				title: '提示',
				content: '确定要退出登录吗？',
				success: (res) => {
					if (res.confirm) {
						// 清除用户信息
						this.logout_account();
						// 重新获取用户信息
						this.get_user_info();
					}
				}
			});
		}
	},
	onLoad() { 
	}
}
</script>

<style>
.user-page {
	background-color: #f5f5f5;
	min-height: 100vh;
	padding-bottom: 40rpx;
}

/* 顶部背景区域 */
.header-bg {
	background: linear-gradient(135deg, #007aff, #00a1ff);
	padding: 0 30rpx 30rpx;
	position: relative;
	margin-bottom: 20rpx; 
	box-shadow: 0 10rpx 20rpx rgba(0, 122, 255, 0.1);
}

/* 状态栏占位 */
.status-bar {
	width: 100%;
}

.user-info {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding-top: 40rpx;
	margin-bottom: 30rpx;
}

.avatar-section {
	display: flex;
	align-items: center;
}

.avatar {
	width: 120rpx;
	height: 120rpx;
	border-radius: 60rpx;
	border: 4rpx solid rgba(255, 255, 255, 0.3);
	margin-right: 30rpx;
	background-color: #fff;
}

.user-details {
	display: flex;
	flex-direction: column;
}

.username {
	color: white;
	font-size: 36rpx;
	font-weight: bold;
	margin-bottom: 10rpx;
}

.user-level {
	color: rgba(255, 255, 255, 0.8);
	font-size: 24rpx;
	background: rgba(255, 255, 255, 0.2);
	padding: 4rpx 16rpx;
	border-radius: 20rpx;
	align-self: flex-start;
}

/* 登录按钮区域 */
.login-btn {
	width: 200rpx;
	height: 60rpx;
	line-height: 60rpx;
	background: rgba(255, 255, 255, 0.2);
	color: white;
	border: none;
	border-radius: 30rpx;
	font-size: 28rpx;
	padding: 0;
	margin: 0;
}

.login-btn::after {
	border: none;
}

/* 统计信息区域 */
.stats-section {
	background-color: white;
	margin: 0 30rpx 30rpx;
	border-radius: 20rpx;
	padding: 30rpx 0;
	display: flex;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

.stat-item {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	position: relative;
}

.stat-item:not(:last-child)::after {
	content: '';
	position: absolute;
	right: 0;
	top: 20%;
	height: 60%;
	width: 1rpx;
	background-color: #eee;
}

.stat-number {
	font-size: 36rpx;
	font-weight: bold;
	color: #333;
	margin-bottom: 10rpx;
}

.stat-label {
	font-size: 24rpx;
	color: #666;
}

/* 菜单区域 */
.menu-section {
	padding: 0 30rpx;
}

.menu-group {
	background-color: white;
	border-radius: 20rpx;
	margin-bottom: 30rpx;
	overflow: hidden;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

.menu-item {
	display: flex;
	align-items: center;
	padding: 25rpx 30rpx;
	border-bottom: 1rpx solid #f5f5f5;
	position: relative;
}

.menu-item:last-child {
	border-bottom: none;
}

.menu-icon-box {
	width: 50rpx;
	height: 50rpx;
	margin-right: 20rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.menu-text {
	flex: 1;
	font-size: 30rpx;
	color: #333;
}

.menu-right {
	display: flex;
	align-items: center;
}

.menu-subtext {
	font-size: 24rpx;
	color: #999;
	margin-right: 10rpx;
}

/* 订单状态快捷入口 */
.order-status-grid {
	display: flex;
	flex-wrap: wrap;
	padding: 20rpx 0;
	border-bottom: 1rpx solid #f5f5f5;
}

.status-item {
	width: 20%;
	display: flex;
	flex-direction: column;
	align-items: center;
	position: relative;
	padding: 15rpx 0;
}

.status-icon {
	width: 60rpx;
	height: 60rpx;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 10rpx;
}

.status-icon.unpaid {
	background-color: #FFF5F5;
}

.status-icon.unshipped {
	background-color: #FFF9F0;
}

.status-icon.shipped {
	background-color: #F0F9FF;
}

.status-icon.completed {
	background-color: #F6FFED;
}

.status-icon.refund {
	background-color: #F9F0FF;
}

.status-text {
	font-size: 24rpx;
	color: #666;
}

.status-badge {
	position: absolute;
	top: 10rpx;
	right: 20rpx;
	background-color: #FF4D4F;
	color: white;
	font-size: 20rpx;
	min-width: 30rpx;
	height: 30rpx;
	line-height: 30rpx;
	border-radius: 15rpx;
	text-align: center;
	padding: 0 8rpx;
}

/* 退出登录区域 */
.logout-section {
	padding: 0 30rpx;
	margin-top: 40rpx;
}

.logout-btn {
	width: 100%;
	height: 88rpx;
	background-color: white;
	color: #ff4757;
	border: 1rpx solid #ff4757;
	border-radius: 44rpx;
	font-size: 32rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.logout-btn:active {
	background-color: #fff0f0;
}
</style>