<template>
	<view class="coupon-container">
		<!-- Tab切换 -->
		<view class="tab-container">
			<view class="tab-item" v-for="(tab, index) in tabs" :key="index" :class="{ 'active': activeTab === index }"
				@click="switchTab(index)">
				<text>{{ tab.name }}</text>
				<view class="tab-line" v-if="activeTab === index"></view>
			</view>
		</view> 
		<!-- 优惠券列表 -->
		<mescroll-body :top="20" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">
			<view class="coupon-item" :class="coupon.class" v-for="(coupon, index) in list">
				<view class="coupon-left">
					<text class="coupon-value">{{ coupon.value_text }}</text>
					<text class="coupon-condition">{{ coupon.condition_text }}</text>
				</view>
				<view class="coupon-right">
					<text class="coupon-name">【{{ coupon.type_text }}】{{ coupon.name }}</text>
					<text class="coupon-time">有效期至: {{ coupon.expired_at_text }}</text>
					<text class="coupon-desc" @click="showCouponDetail(coupon)" v-if="coupon.products_text">适用范围:{{ coupon.products_text }}</text> 
				</view>
			</view>
		</mescroll-body>
		<view class="coupon-btn" @click="jump('/pages/coupon/coupon')">
			领取优惠券
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
				</view>
			</view>
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
				</view>
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
			where: {
				page: 1,
				page_size: 10
			},
			activeTab: 0, // 当前选中的tab
			tabs: [{
				name: '可用'
			},
			{
				name: '已用'
			},
			{
				name: '过期'
			}
			],
			upOption: {
				page: {
					size: 10 // 每页数据的数量
				},
				noMoreSize: 5, // 配置列表的总数量要大于等于5条才显示'-- END --'的提示
				empty: {
					tip: '暂无相关数据'
				},
				textNoMore: '没有更多了'

			},
			downOption: {
				auto: false // 是否在初始化后自动执行下拉刷新
			},
			list: [],
			coupons: [], // 所有优惠券数据
			showDetail: false, // 是否显示优惠券详情
			currentCoupon: {} // 当前查看的优惠券
		}
	},
	computed: {
		// 可用优惠券
		availableCoupons() {
			return this.coupons.filter(item => item.status === 'available')
		},
		// 已用优惠券
		usedCoupons() {
			return this.coupons.filter(item => item.status === 'used')
		},
		// 过期优惠券
		expiredCoupons() {
			return this.coupons.filter(item => item.status === 'expired')
		}
	},
	onLoad() {
		_this = this
	},
	onShow() {
		this.load()
	},
	methods: {
		// 切换tab
		switchTab(index) {
			if (this.activeTab === index) return;
			this.activeTab = index;
			this.coupons = []; // 清空数据
			this.mescroll.resetUpScroll(); // 重置分页
		},

		/*下拉刷新的回调 */
		downCallback() {
			this.load();
		},

		/*上拉加载的回调 */
		upCallback(page) {
			this.where.page = page.num
			this.load();
		},
		load() {
			const statusMap = ['available', 'used', 'expired'];
			const status = statusMap[this.activeTab];
			let where = this.where
			where.status = status
			this.ajax(this.config.coupon.my_list, where).then(res => {
				if (res.current_page == 1) {
					_this.list = []
				}
				for (let i in res.data) {
					_this.list.push(res.data[i])
				}
				_this.mescroll.endBySize(res.per_page, res.total);
			})
		},
		
		
		// 显示优惠券详情
		showCouponDetail(coupon) {
			this.currentCoupon = coupon
			this.showDetail = true
		},
		
		// 隐藏优惠券详情
		hideCouponDetail() {
			this.showDetail = false
		}
	},

}
</script>

<style lang="scss">
.coupon-btn {
	height: 88rpx;
	line-height: 88rpx;
	text-align: center;
	font-size: 32rpx;
	color: #fff;
	background-color: #ff5500;
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
}

.coupon-container {
	background-color: #f5f5f5;
	padding-bottom: 50px;
}

.tab-container {
	display: flex;
	background-color: #fff;
	height: 88rpx;
	position: sticky;
	top: 0;
	z-index: 10;
}

.tab-item {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	font-size: 28rpx;
	color: #666;
	position: relative;

	&.active {
		color: #ff5500;
		font-weight: 500;
	}
}

.tab-line {
	width: 80rpx;
	height: 4rpx;
	background-color: #ff5500;
	border-radius: 2rpx;
	position: absolute;
	bottom: 0;
}

.coupon-list {
	padding: 20rpx;
	box-sizing: border-box;
}

.coupon-item {
	display: flex;
	height: 180rpx;
	margin-left: 10px;
	margin-right: 10px;
	margin-bottom: 20rpx;
	border-radius: 12rpx;
	overflow: hidden;
	background: linear-gradient(to right, #ff944b, #ff5500);
	color: #fff;

	&.used {
		background: linear-gradient(to right, #cccccc, #999999);
		opacity: 0.8;
	}

	&.expired {
		background: linear-gradient(to right, #cccccc, #999999);
		opacity: 0.6;
	}
}

.coupon-left {
	width: 200rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	border-right: 2rpx dashed rgba(255, 255, 255, 0.3);

	.coupon-value {
		font-size: 40rpx;
		font-weight: bold;
		line-height: 1;
	}

	.coupon-condition {
		font-size: 24rpx;
		margin-top: 10rpx;
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
		font-weight: 500;
	}

	.coupon-time {
		font-size: 24rpx;
		color: rgba(255, 255, 255, 0.8);
	}

	.coupon-desc {
		font-size: 24rpx;
		color: rgba(255, 255, 255, 0.9); 
	}

	.coupon-status {
		align-self: flex-end;
		font-size: 24rpx;
		padding: 4rpx 12rpx;
		border-radius: 12rpx;
		background-color: rgba(255, 255, 255, 0.3);

		&.available {
			color: #fff;
		}

		&.used {
			color: #999;
			background-color: rgba(255, 255, 255, 0.5);
		}

		&.expired {
			color: #999;
			background-color: rgba(255, 255, 255, 0.5);
		}
	}
}

.empty-tip {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding-top: 100rpx;

	.empty-img {
		width: 200rpx;
		height: 200rpx;
		margin-bottom: 30rpx;
		opacity: 0.5;
	}

	.empty-text {
		font-size: 28rpx;
		color: #999;
	}
}

// 优惠券详情弹窗样式
.coupon-modal {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0, 0, 0, 0.5);
	z-index: 999;
	display: flex;
	justify-content: center;
	align-items: center;
}

.modal-content {
	width: 80%;
	background-color: #fff;
	border-radius: 12rpx;
	overflow: hidden;
}

.modal-header {
	padding: 20rpx;
	border-bottom: 1px solid #eee;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.modal-title {
	font-size: 32rpx;
	font-weight: bold;
}

.modal-close {
	width: 40rpx;
	height: 40rpx;
	display: flex;
	justify-content: center;
	align-items: center;
}

.icon-close::before {
	content: '×';
	font-size: 40rpx;
	color: #999;
}

.modal-body {
	padding: 30rpx 20rpx;
}

.detail-item {
	margin-bottom: 20rpx;
	display: flex;
}

.detail-label {
	font-size: 28rpx;
	color: #666;
	margin-right: 10rpx;
}

.detail-value {
	font-size: 28rpx;
	color: #333;
	flex: 1;
}
</style>