<template>
	<view class="store-page">
		<!-- 门店提醒横幅 -->
		<view class="store-alert" v-if="store.notice">
			<uni-icons type="notification" size="16" color="#FF9500"></uni-icons>
			<text class="alert-text">{{ store.notice }}</text>
			<!-- <uni-icons type="close" size="14" color="#999" class="close-icon" @click="closeAlert"></uni-icons> -->
		</view>

		<!-- 门店信息 - 美团风格 -->
		<view class="store-card">
			<view class="store-header">
				<image class="store-image" :src="store.image" mode="aspectFill"></image>
				<view class="store-info-main">
					<view class="store-name">{{ store.title }}</view>
					<view class="store-status-row">
						<view class="store-status" :class="store.status == 1 ? 'open' : 'closed'">
							<text class="status-dot"></text>
							{{ store.status == 1 ? '营业中' : '已打烊' }}
						</view>
						<text class="divider-vertical">|</text>
						<text class="store-hours">{{ store.hours }}</text>
					</view>
				</view>
			</view> 
			
			<view class="divider"></view>
			
			<view class="store-details-compact">
				<view class="detail-row">
					<uni-icons type="location-filled" size="16" color="#666" class="detail-icon"></uni-icons>
					<text class="detail-text address-text">{{ store.address }}</text>
				</view>
				
				<view class="detail-row phone-row">
					<uni-icons type="phone" size="16" color="#007AFF" class="detail-icon"></uni-icons>
					<text class="detail-text callable" @click="makePhoneCall">{{ store.phone }}</text>
					<view class="" >
						 
					</view>
				</view>
			</view>
		</view>

		<!-- 搜索框 - 添加全部商品按钮 -->
		<view class="search-container">
			<view class="search-box" @click="goToSearch">
				<uni-icons type="search" size="18" color="#999"></uni-icons>
				<view class="search-input">搜索商品...</view>
			</view>
			<view class="all-products-btn" @click="goToProductList">
				<text>全部商品</text>
				<uni-icons type="arrowright" size="14" color="#666"></uni-icons>
			</view>
		</view>

		<!-- 商品列表 -->
		<t-product ref="product" :show-switch="false" :row="row" ></t-product>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				store: {
					notice: "",
					// 其他门店数据...
				},
				products: [ 
				],
				row:{
					product_type:'all'
				},
				showAlert: true
			}
		},
		onLoad() {
			this.load_info() 
		},
		onShow() {
			this.load_info() 
		},
		onReachBottom() {
			console.log('已经滚动到底部')
			if (this.$refs.product) {
				this.$refs.product.loadMore() 
			}
		},
		methods: {
			handleReachBottom() { 
					this.$refs.product.loadMore()
			},
			load_info() {
				this.ajax(this.config.o2o.home, {}).then(res => {
					this.store = res.data
					this.set_title(res.data.title)
				})
			},
			makePhoneCall() {
				uni.makePhoneCall({
					phoneNumber: this.store.phone,
					success: () => {
						console.log('拨打电话成功')
					},
					fail: (err) => {
						console.error('拨打电话失败', err)
					}
				})
			}, 
			goToProductList() {
				uni.navigateTo({
					url: '/pages/product/list'
				})
			},
			goToSearch() {
				uni.navigateTo({
					url: '/pages/search/search'
				})
			},
			closeAlert() {
				this.showAlert = false;
			},
			clickAlert() {
				uni.showToast({
					title: '查看活动详情',
					icon: 'none'
				})
				// 这里可以跳转到活动页面
			}
		}
	}
</script>

<style scoped>
	.store-page { 
		background-color: #f8f8f8;
		min-height: 100vh;
	}

	/* 门店提醒横幅 */
	.store-alert {
		background: linear-gradient(135deg, #FFF9E6 0%, #FFEFD2 100%);
		border: 1rpx solid #FFE6B0;
		border-radius: 12rpx;
		padding: 20rpx 24rpx;
		margin: 20rpx;
		display: flex;
		align-items: center;
		box-shadow: 0 4rpx 12rpx rgba(255, 149, 0, 0.15);
		position: relative;
	}

	.alert-text {
		flex: 1;
		font-size: 26rpx;
		color: #FF9500;
		font-weight: 500;
		margin: 0 16rpx;
		line-height: 1.4;
	}

	.close-icon {
		opacity: 0.7;
	}

	.close-icon:active {
		opacity: 1;
	}

	/* 搜索框 - 带全部商品按钮 */
	.search-container {
		background-color: #fff; 
		padding: 20rpx;
		margin: 0 20rpx 20rpx 20rpx;
		box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
		display: flex;
		align-items: center;
		justify-content: space-between;
		border-radius: 12rpx;
	}

	.search-box {
		display: flex;
		align-items: center;
		background-color: #f7f7f7;
		border-radius: 50rpx;
		padding: 16rpx 24rpx;
		flex: 1;
		margin-right: 20rpx;
	}

	.search-input {
		flex: 1;
		font-size: 28rpx;
		color: #999;
		margin-left: 12rpx;
	}

	.all-products-btn {
		display: flex;
		align-items: center;
		padding: 16rpx 0;
		color: #666;
		font-size: 28rpx;
		white-space: nowrap;
	}

	.all-products-btn:active {
		opacity: 0.7;
	}

	.all-products-btn text {
		margin-right: 6rpx;
	}

	/* 门店卡片 - 美团风格 */
	.store-card {
		background-color: #fff;
		border-radius: 12rpx;
		padding: 24rpx;
		margin: 0 20rpx 20rpx 20rpx;
		box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.06);
	}

	.store-header {
		display: flex;
		align-items: center;
		margin-bottom: 20rpx;
	}

	.store-image {
		width: 80rpx;
		height: 80rpx;
		borrow-radius: 8rpx;
		margin-right: 16rpx;
		background-color: #f5f5f5;
	}

	.store-info-main {
		flex: 1;
	}

	.store-name {
		font-size: 32rpx;
		font-weight: 700;
		color: #333;
		margin-bottom: 8rpx;
		line-height: 1.2;
	}

	.store-status-row {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 12rpx;
	}

	.store-status {
		display: flex;
		align-items: center;
		font-size: 22rpx;
		padding: 4rpx 8rpx;
		border-radius: 4rpx;
	}

	.store-status.open {
		background-color: rgba(7, 193, 96, 0.1);
		color: #07c160;
	}

	.store-status.closed {
		background-color: rgba(255, 77, 79, 0.1);
		color: #ff4d4f;
	}

	.status-dot {
		width: 8rpx;
		height: 8rpx;
		border-radius: 50%;
		margin-right: 6rpx;
	}

	.store-status.open .status-dot {
		background-color: #07c160;
	}

	.store-status.closed .status-dot {
		background-color: #ff4d4f;
	}

	.divider-vertical {
		color: #ddd;
		font-size: 20rpx;
	}

	.store-hours {
		font-size: 22rpx;
		color: #666;
	}

	.divider {
		height: 1rpx;
		background-color: #f0f0f0;
		margin: 0 -24rpx 20rpx -24rpx;
	}

	/* 紧凑型详情信息 */
	.store-details-compact {
		display: flex;
		flex-direction: column;
		gap: 16rpx;
	}

	.detail-row {
		display: flex;
		align-items: center;
		font-size: 24rpx;
	}

	.detail-icon {
		margin-right: 8rpx;
		flex-shrink: 0;
	}

	.detail-text {
		color: #666;
		flex: 1;
		line-height: 1.4;
	}

	.address-text {
		font-size: 24rpx;
	}

	.phone-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.callable {
		color: #007AFF;
	}

	.call-button {
		display: flex;
		align-items: center;
		padding: 6rpx 16rpx;
		border: 1rpx solid #007AFF;
		border-radius: 20rpx;
		color: #007AFF;
		font-size: 22rpx;
		background-color: rgba(0, 122, 255, 0.1);
	}

	.call-button:active {
		background-color: rgba(0, 122, 255, 0.2);
	}

	/* 点击效果 */
	.detail-text.callable:active {
		opacity: 0.7;
	}
</style>