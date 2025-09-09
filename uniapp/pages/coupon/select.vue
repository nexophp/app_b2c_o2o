<template>
	<view class="coupon-select-container">

		<!-- 优惠券列表 -->
		<view class="coupon-list">
			<!-- 不使用优惠券选项 -->
			<view class="coupon-item no-coupon" :class="{ 'selected': selectedCoupon === null }"
				@click="selectCoupon(null)">
				<view class="coupon-left">
					<text class="no-coupon-text">不使用</text>
				</view>
				<view class="coupon-right">
					<text class="coupon-name">不使用优惠券</text>
					<text class="coupon-desc">享受原价购买</text>
				</view>
				<view class="radio-icon">
					<text class="iconfont"
						:class="selectedCoupon === null ? 'icon-radio-checked' : 'icon-radio-unchecked'"></text>
				</view>
			</view>

			<!-- 可用优惠券 -->
			<view class="coupon-item" v-for="(coupon, index) in availableCoupons" :key="coupon.id"
				:class="{ 'selected': selectedCoupon && selectedCoupon.id === coupon.id }">
				<view class="coupon-left" @click="selectCoupon(coupon)">
					<text class="coupon-value">{{ coupon.value_text }}</text>
					<text class="coupon-condition">{{ coupon.condition_text }}</text>
				</view>
				<view class="coupon-right" >
					<text class="coupon-name" @click="selectCoupon(coupon)">【{{ coupon.type_text }}】{{ coupon.name }}</text>
					<text class="coupon-time" @click="selectCoupon(coupon)">有效期至: {{ coupon.expired_at_text }}</text>
					<text class="coupon-desc"  @click="showCouponDetail(index, 'available')">适用范围:{{ coupon.products_text }}</text>
				</view>
				<view class="radio-icon" @click="selectCoupon(coupon)">
					<text class="iconfont icon-radio-checked"
						v-if="selectedCoupon && selectedCoupon.id === coupon.id"></text>
					<text class="iconfont icon-radio-unchecked" v-else></text>
				</view>
			</view>

			<!-- 不可用优惠券 -->
			<view class="coupon-item disabled" v-for="(coupon, index) in unavailableCoupons"
				:key="'disabled-' + coupon.id">
				<view class="coupon-left">
					<text class="coupon-value">{{ coupon.value_text }}</text>
					<text class="coupon-condition">{{ coupon.condition_text }}</text>
					<text class="coupon-desc" @click="showCouponDetail(index, 'unavailable')">适用范围:{{ coupon.products_text }}</text>
				</view>
				<view class="coupon-right">
					<text class="coupon-name">【{{ coupon.type_text }}】{{ coupon.name }}</text>
					<text class="coupon-time">有效期至: {{ coupon.expired_at_text }}</text>
					<text class="unavailable-reason">不满足使用条件</text>
				</view>
				<view class="radio-icon">
					<text class="iconfont icon-radio-disabled"></text>
				</view>
			</view>
		</view>

		<!-- 空状态 -->
		<view class="empty-tip" v-if="allCoupons.length === 0 && !loading">
			<text class="empty-text">暂无可用优惠券</text>
		</view>

		<!-- 底部确认按钮 -->
		<view class="bottom-submit">
			<view class="submit-btn" @click="confirmSelection">确认选择</view>
		</view>

		<!-- 优惠券详情弹窗 -->
		<view class="coupon-modal" v-if="showDetail" @click="hideCouponDetail">
			<view class="modal-content" @click.stop>
				<view class="modal-header">
					<text class="modal-title">优惠券详情</text>
					<view class="modal-close" @click="hideCouponDetail">
						<text class="iconfont icon-close"></text>
					</view>
				</view>
				<view class="modal-body">
					<view class="detail-item">
						<text class="detail-label">优惠券名称:</text>
						<text class="detail-value">{{ currentCoupon.name }}</text>
					</view>
					<view class="detail-item">
						<text class="detail-label">优惠类型:</text>
						<text class="detail-value">{{ currentCoupon.type_text }}</text>
					</view>
					<view class="detail-item">
						<text class="detail-label">优惠内容:</text>
						<text class="detail-value">{{ currentCoupon.value_text }}</text>
					</view>
					<view class="detail-item">
						<text class="detail-label">使用条件:</text>
						<text class="detail-value">{{ currentCoupon.condition_text }}</text>
					</view>
					<view class="detail-item">
						<text class="detail-label">有效期:</text>
						<text class="detail-value">至 {{ currentCoupon.expired_at_text }}</text>
					</view>
					<view class="detail-item" v-if="currentCoupon.products_text">
						<text class="detail-label">适用范围:</text>
						<text class="detail-value">{{ currentCoupon.products_text }}</text>
					</view>
					<view class="detail-item" v-else>
						<text class="detail-label">适用范围:</text>
						<text class="detail-value">全平台通用</text>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			orderItems: [],
			allCoupons: [],
			selectedCoupon: null,
			orderAmount: 0, // 订单金额，用于判断优惠券是否可用
			loading: true,
			showDetail: false, // 控制详情弹窗显示
			currentCoupon: {} // 当前查看的优惠券
		}
	},
	onLoad(options) {
		// 获取传递的订单商品
		if (options.orderItems) {
			this.orderItems = JSON.parse(options.orderItems)
		}

		// 获取传递的订单金额
		if (options.amount) {
			this.orderAmount = parseFloat(options.amount)
		}

		// 获取当前选中的优惠券
		if (options.selectedCouponId) {
			this.selectedCouponId = parseInt(options.selectedCouponId)
		}

		this.loadCoupons()
	},
	computed: {
		// 可用优惠券
		availableCoupons() {
			return this.allCoupons.filter(coupon => {
				return coupon.status === 1 && this.orderAmount >= parseFloat(coupon.condition)
			})
		},
		// 不可用优惠券
		unavailableCoupons() {
			return this.allCoupons.filter(coupon => {
				return coupon.status === 1 && this.orderAmount < parseFloat(coupon.condition)
			})
		}
	},
	methods: {
		// 加载优惠券列表
		loadCoupons() {
			this.loading = true
			this.ajax(this.config.coupon.my_list, {
				status: 1,
				items: this.orderItems
			}).then(res => {
				this.loading = false
				this.allCoupons = res.data || []

				// 设置当前选中的优惠券
				if (this.selectedCouponId) {
					const coupon = this.allCoupons.find(c => c.id === this.selectedCouponId)
					if (coupon && this.orderAmount >= parseFloat(coupon.condition)) {
						this.selectedCoupon = coupon
					}
				}
			})
		},

		// 选择优惠券
		selectCoupon(coupon) {
			// 如果是不可用的优惠券，不允许选择
			if (coupon && this.orderAmount < parseFloat(coupon.condition)) {
				uni.showToast({
					title: `订单金额需满${coupon.condition}元`,
					icon: 'none'
				})
				return
			}

			this.selectedCoupon = coupon
		},

		// 确认选择
		confirmSelection() {
			// 通过事件总线传递选中的优惠券
			uni.$emit('selectCoupon', this.selectedCoupon)

			// 返回上一页
			uni.navigateBack()
		},

		// 显示优惠券详情
		showCouponDetail(index, type) {
		  const coupons = type === 'available' ? this.availableCoupons : this.unavailableCoupons;
		  this.currentCoupon = coupons[index];
		  this.showDetail = true;
		},

		// 隐藏优惠券详情
		hideCouponDetail() {
			this.showDetail = false
		}
	}
}
</script>

<style lang="scss">
.coupon-select-container {
	background: #f5f5f5;
	min-height: 100vh;
	padding-bottom: 120rpx;
}

.header {
	background: #fff;
	height: 88rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	border-bottom: 2rpx solid #eee;
	position: sticky;
	top: 0;
	z-index: 10;

	.title {
		font-size: 32rpx;
		color: #333;
		font-weight: bold;
	}
}

.coupon-list {
	padding: 20rpx;
}

.coupon-item {
	display: flex;
	align-items: center;
	height: 180rpx;
	margin-bottom: 20rpx;
	border-radius: 12rpx;
	overflow: hidden;
	background: #fff;
	border: 2rpx solid #eee;
	position: relative;

	&.selected {
		border-color: #ff6b35;
		background: linear-gradient(to right, #fff5f0, #fff);
	}

	&.no-coupon {
		background: #f9f9f9;

		&.selected {
			background: linear-gradient(to right, #fff5f0, #f9f9f9);
		}
	}

	&.disabled {
		opacity: 0.5;
		background: #f5f5f5;
	}
}

.coupon-left {
	width: 200rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	border-right: 2rpx dashed #eee;

	.coupon-value {
		font-size: 40rpx;
		color: #ff6b35;
		font-weight: bold;
		line-height: 1;
	}

	.coupon-condition {
		font-size: 24rpx;
		color: #999;
		margin-top: 10rpx;
	}

	.no-coupon-text {
		font-size: 28rpx;
		color: #666;
		font-weight: bold;
	}
}

.coupon-right {
	flex: 1;
	padding: 20rpx;
	display: flex;
	flex-direction: column;
	justify-content: space-between;

	.coupon-name {
		font-size: 28rpx;
		color: #333;
		font-weight: 500;
		margin-bottom: 10rpx;
	}

	.coupon-desc {
		font-size: 24rpx;
		color: #999;
	}

	.coupon-time {
		font-size: 24rpx;
		color: #999;
		margin-bottom: 5rpx;
	}

	.unavailable-reason {
		font-size: 22rpx;
		color: #ff6b35;
	}
}

.radio-icon {
	width: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;

	.iconfont {
		font-size: 36rpx;
	}

	.icon-radio-checked {
		color: #ff6b35;
	}

	.icon-radio-unchecked {
		color: #ddd;
	}

	.icon-radio-disabled {
		color: #ccc;
	}
}

// 详情图标
.detail-icon {
	width: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;

	.icon-detail {
		font-size: 36rpx;
		color: #999;

		&:before {
			content: 'ℹ';
		}
	}

	.icon-detail:hover {
		color: #ff6b35;
	}
}

// 使用Unicode字符代替图标字体
.icon-radio-checked::before {
	content: '●';
}

.icon-radio-unchecked::before {
	content: '○';
}

.icon-radio-disabled::before {
	content: '○';
}

.empty-tip {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding-top: 100rpx;

	.empty-text {
		font-size: 28rpx;
		color: #999;
	}
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
	justify-content: center;
	padding: 0 30rpx;
	border-top: 2rpx solid #eee;

	.submit-btn {
		flex: 1;
		height: 80rpx;
		line-height: 80rpx;
		text-align: center;
		background: #ff6b35;
		color: #fff;
		border-radius: 40rpx;
		font-size: 28rpx;
		font-weight: bold;
	}
}

// 优惠券详情弹窗样式
.coupon-modal {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 999;
	animation: fadeIn 0.3s ease;
}

.modal-content {
	width: 600rpx;
	background: #fff;
	border-radius: 16rpx;
	overflow: hidden;
	animation: slideUp 0.3s ease;
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 30rpx;
	border-bottom: 2rpx solid #eee;

	.modal-title {
		font-size: 32rpx;
		font-weight: bold;
		color: #333;
	}

	.modal-close {
		.icon-close {
			font-size: 36rpx;
			color: #999;

			&:before {
				content: '×';
			}
		}
	}
}

.modal-body {
	padding: 30rpx;

	.detail-item {
		display: flex;
		margin-bottom: 20rpx;

		&:last-child {
			margin-bottom: 0;
		}

		.detail-label {
			width: 150rpx;
			font-size: 28rpx;
			color: #666;
			font-weight: bold;
		}

		.detail-value {
			flex: 1;
			font-size: 28rpx;
			color: #333;
			word-break: break-all;
		}
	}
}

// 动画效果
@keyframes fadeIn {
	from {
		opacity: 0;
	}

	to {
		opacity: 1;
	}
}

@keyframes slideUp {
	from {
		opacity: 0;
		transform: translateY(100rpx);
	}

	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.coupon-desc {
	font-size: 14px;
	color: #7f8c8d;
	line-height: 1.5;
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
	transition: all 0.3s;
}
</style>