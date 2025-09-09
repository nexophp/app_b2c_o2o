<template>
	<view class="service-stats-container">
		<!-- 顶部操作按钮 -->
		<view class="action-buttons">
			<button class="record-btn" @click="jump('/pages/service/order')">购买记录</button>
			<button class="record-btn" @click="jump('/pages/service/order_cost')">消费记录</button>
		</view>

		<!-- 统计卡片 -->
		<view class="stats-card" v-for="(v, index) in list" :key="index">
			<view class="store-header">
				<text class="store-name">{{v.title}}</text>
			</view>

			<!-- 统计表格 -->
			<view class="stats-table">
				<!-- 表头 -->
				<view class="table-header">
					<view class="header-cell service-name">服务项目</view>
					<view class="header-cell">购买</view>
					<view class="header-cell">使用</view>
					<view class="header-cell">剩余</view>
				</view>

				<!-- 表格内容 -->
				<view class="table-row" v-for="(item, i) in v.products" :key="i"
					:class="{last: i === v.products.length - 1}">
					<view class="row-cell service-name">
						<text class="service-title">{{item.title}}</text>
					</view>
					<view class="row-cell">{{item.total_num}}</view>
					<view class="row-cell">{{item.used_num}}</view>
					<view class="row-cell remaining">
						<text>{{item.less_num}}</text>
					</view>
				</view>
			</view>
		</view>

		<!-- 空状态 -->
		<view class="empty-state" v-if="list.length === 0">
			<image src="/static/images/empty-data.png" mode="aspectFit"></image>
			<text>暂无统计数据</text>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				list: []
			}
		},
		methods: {
			loadStatsData() {
				uni.showLoading({
					title: '加载中...'
				})
				this.ajax(this.config.service.order.stat, {}).then(res => {
					this.list = res.data || []
				}).finally(() => {
					uni.hideLoading()
				})
			}
		},
		onLoad() {
			this.loadStatsData()
		}
	}
</script>

<style lang="scss">
	.service-stats-container {
		padding: 20rpx;
		background-color: #f5f7fa;
		min-height: 100vh;

		.action-buttons {
			display: flex;
			justify-content: space-between;
			margin-bottom: 30rpx;
			gap: 20rpx;

			.record-btn {
				flex: 1;
				background-color: #fff;
				color: #4a6cf7;
				border-radius: 12rpx;
				font-size: 28rpx;
				height: 80rpx;
				line-height: 80rpx;
				box-shadow: 0 4rpx 12rpx rgba(74, 108, 247, 0.1);
				border: none;
				transition: all 0.3s;

				&:active {
					opacity: 0.8;
					transform: scale(0.98);
				}

				&:after {
					border: none;
				}
			}
		}

		.stats-card {
			background-color: #fff;
			border-radius: 16rpx;
			margin-bottom: 24rpx;
			overflow: hidden;
			box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.04);

			.store-header {
				padding: 28rpx 24rpx;
				border-bottom: 1rpx solid #f0f2f5;

				.store-name {
					font-size: 32rpx;
					font-weight: 600;
					color: #1a1a1a;
				}
			}

			.stats-table {
				.table-header {
					display: flex;
					background-color: #f8fafc;
					padding: 20rpx 0;

					.header-cell {
						flex: 1;
						text-align: center;
						font-size: 26rpx;
						color: #666;
						font-weight: 500;

						&.service-name {
							flex: 2;
							text-align: left;
							padding-left: 24rpx;
						}
					}
				}

				.table-row {
					display: flex;
					padding: 28rpx 0;
					border-bottom: 1rpx solid #f0f2f5;

					&.last {
						border-bottom: none;
					}

					.row-cell {
						flex: 1;
						text-align: center;
						font-size: 28rpx;
						color: #333;
						display: flex;
						justify-content: center;
						align-items: center;

						&.service-name {
							flex: 2;
							text-align: left;
							padding-left: 24rpx;
							justify-content: flex-start;

							.service-title {
								font-weight: 500;
							}
						}

						&.remaining {
							color: #4a6cf7;
							font-weight: 600;
						}
					}
				}
			}
		}

		.empty-state {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 120rpx 0;

			image {
				width: 200rpx;
				height: 200rpx;
				margin-bottom: 30rpx;
				opacity: 0.5;
			}

			text {
				font-size: 28rpx;
				color: #999;
			}
		}
	}
</style>