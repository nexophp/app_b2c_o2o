<template>
	<view class="point-mall">
		<!-- 主要内容区 -->
		<view class="main-content">
			<!-- 积分余额展示 -->
			<view class="point-balance">
				<view class="balance-left">
					<text class="balance-label">我的积分</text>
					<text class="balance-value">{{ userPoints }}</text>
				</view>
				<view class="balance-right" style="flex-direction: row; gap: clamp(20rpx, 4vw, 30rpx);">
					<view @click="jump_user('/pages_v2/point_order/order')" class="action-item">
						<text class="view-record">我的订单</text>
						<uni-badge size="small" :text="unreadOrders > 0 ? unreadOrders : ''"></uni-badge>
						<uni-icons type="arrowright" size="18" color="#fff"></uni-icons>
					</view>
					<view @click="jump_user('/pages/point/his')" class="action-item">
						<text class="view-record">查看记录</text>
						<uni-icons type="arrowright" size="18" color="#fff"></uni-icons>
					</view>
				</view>
			</view>

			<!-- 商品列表 -->
			<view class="product-list">
				<view class="section-title">
					<text>积分兑换</text>
				</view>
				<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
					@up="upCallback">
					<view class="products">
						<view class="product-card" v-for="v in list">
							<image class="product-image" :src="v.image">
							</image>
							<view class="product-info">
								<text class="product-name">{{ v.title }}</text>
								<view class="product-footer">
									<text class="product-point">{{ v.point }}积分</text>
									<view>
										<button class="exchange-btn" @click="exchangeProduct(v)"
											v-if="v.stock > 0">兑换</button>
										<button class="exchange-btn disabled" v-else>兑换</button>
									</view>
								</view>
							</view>
						</view>
					</view>
				</mescroll-body>

			</view>
		</view>
	</view>
</template>

<script>
var _this
import MescrollMixin from "@/uni_modules/mescroll-uni/components/mescroll-uni/mescroll-mixins.js";
export default {
	mixins: [MescrollMixin],
	data() {
		return {
			userPoints: 0, // 用户积分
			where: {
				page: 1,
				page_size: 10
			},
			upOption: {
				page: {
					size: 10 // 每页数据的数量,默认10
				},
				noMoreSize: 5, // 配置列表的总数量要大于等于5条才显示'-- END --'的提示
				empty: {
					tip: ' '
				},
				textNoMore: '没有更多了'
			},
			downOption: {
				auto: false, //是否在初始化后,自动执行downCallback; 默认true 
			},
			list: [],
		}
	},
	onLoad() {
		_this = this
	},
	onShow() {
		this.get_point()
	},
	methods: {
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

			_this.ajax(_this.config.point.product, w).then(res => {
				_this.is_load = true
				if (res.current_page == 1) {
					_this.list = []
				}
				for (let i in res.data) {
					_this.list.push(res.data[i])
				}
				_this.mescroll.endBySize(res.per_page, res.total);
			})
		},
		get_point() {
			this.ajax(this.config.point.total, {}).then(res => {
				this.userPoints = res.data
			})
		},


		// 兑换商品
		exchangeProduct(product) {
			let items = [{
				product_id: product.id,
				num: 1,
				spec: '',
				image: product.image,
				name: product.title,
				point: product.point,
			}];

			let url = '/pages_v2/point_order/confirm?product_type=' + product.product_type + '&items=' + encodeURIComponent(JSON.stringify(items));

			this.jump_user(url)
		},

		// 执行兑换
		doExchange(productId) {
			uni.showLoading({
				title: '兑换中...'
			})

			// 这里替换为实际的API调用
			setTimeout(() => {
				uni.hideLoading()
				uni.showToast({
					title: '兑换成功',
					icon: 'success'
				})
				this.get_point() // 刷新积分
			}, 1500)
		}
	}
}
</script>

<style lang="scss">
.point-mall {
	background-color: #f5f5f5;
}

.main-content {
	padding-bottom: 30rpx;
}

.point-balance {
	background: linear-gradient(135deg, #ff944b, #ff5500);
	color: #fff;
	padding: 30rpx;
	margin: 20rpx;
	border-radius: 16rpx;
	box-shadow: 0 4rpx 12rpx rgba(255, 107, 53, 0.3);
	display: flex;
	justify-content: space-between;
	align-items: center;

	.balance-left {
		display: flex;
		flex-direction: column;
	}

	.balance-label {
		font-size: 28rpx;
		margin-bottom: 10rpx;
	}

	.balance-value {
		font-size: 48rpx;
		font-weight: bold;
		line-height: 1.2;
	}

	.balance-right {
		display: flex;

	}

	/* 新增样式 */
	.action-item {
		display: flex;
		align-items: center;
		padding: 8rpx 16rpx;
		border-radius: 40rpx;
		background-color: rgba(255, 255, 255, 0.2);
		transition: background-color 0.3s;

		&:active {
			background-color: rgba(255, 255, 255, 0.4);
		}
	}

	.view-record {
		margin-right: 6rpx;
		font-size: 26rpx;
		vertical-align: middle;
	}
}

.product-list {
	background-color: #fff;
	margin: 20rpx;
	border-radius: 16rpx;
	overflow: hidden;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

	.section-title {
		padding: 20rpx;
		font-size: 32rpx;
		font-weight: 500;
		color: #333;
		border-bottom: 1rpx solid #f0f0f0;
	}
}

.products {
	padding: 20rpx;
}

.product-card {
	display: flex;
	margin-bottom: 30rpx;
	background-color: #fff;
	border-radius: 12rpx;
	overflow: hidden;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.product-image {
	width: 200rpx;
	height: 200rpx;
	background-color: #f5f5f5;
}

.product-info {
	flex: 1;
	padding: 20rpx;
	display: flex;
	flex-direction: column;
	justify-content: space-between;

	.product-name {
		font-size: 30rpx;
		font-weight: 500;
		color: #333;
		margin-bottom: 10rpx;
	}

	.product-desc {
		font-size: 26rpx;
		color: #999;
		margin-bottom: 20rpx;
	}
}

.product-footer {
	display: flex;
	justify-content: space-between;
	align-items: center;

	.product-point {
		font-size: 28rpx;
		color: #ff6b35;
		font-weight: bold;
	}
}

.exchange-btn {
	background-color: #ff6b35;
	color: #fff;
	border-radius: 40rpx;
	font-size: 26rpx;
	padding: 0 30rpx;
	height: 60rpx;
	line-height: 60rpx;

	&:active {
		opacity: 0.8;
	}

	&.disabled {
		background-color: #ccc;
		color: #999;
	}
}
</style>