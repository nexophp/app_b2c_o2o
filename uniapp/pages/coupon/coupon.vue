<template>
	<view>
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">

			<view class="coupon-page">
				<!-- 优惠券列表 -->
				<view class="coupon-list">
					<view class="coupon-item" v-for="v in list" :class="{ 'received': v.is_received }">
						<view class="coupon-left">
							<text class="coupon-value">{{ v.value_text }}</text>
							<text class="coupon-condition"> {{ v.condition_text }} </text>
						</view>
						<view class="coupon-right">
							<text class="coupon-name">【{{v.type_text}}】{{ v.name }} </text>
							<text class="coupon-time">领取后立即可用，{{ v.days }}天后过期</text>
							<button class="receive-btn" :class="{ 'received': v.is_received }" @click="receiveCoupon(v)">
								{{ v.is_received ? '已领取' : '立即领取' }}
							</button>
						</view>
						<!-- 已领取标记 -->
						<view class="received-tag" v-if="v.is_received">
							<text>已领取</text>
						</view>
					</view>
				</view>
			</view>
		</mescroll-body>
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
				upOption: {
					page: {
						size: 10 // 每页数据的数量,默认10
					},
					noMoreSize: 5, // 配置列表的总数量要大于等于5条才显示'-- END --'的提示
					empty: {
						tip: '暂无相关数据'
					},
					textNoMore: '-- 到底了 --'
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
				this.ajax(_this.config.coupon.guest_list, w).then(res => {
					if (res.current_page == 1) {
						_this.list = []
					}
					for (let i in res.data) {
						_this.list.push(res.data[i])
					}
					_this.mescroll.endBySize(res.per_page, res.total);
				})
			},
			// 领取优惠券
			receiveCoupon(v) {
				if(v.is_received) return;
				
				this.ajax(_this.config.coupon.receive, {
					id: v.id
				}).then(res => {
					uni.showToast({
						title: res.msg
					})
					_this.reload()
				})
			},
		},
	}
</script>

<style lang="scss">
	.coupon-page {
		background-color: #f5f5f5;
		padding-bottom: 30rpx;
	}

	.coupon-list {
		padding: 20rpx;
	}

	.coupon-item {
		display: flex;
		height: 180rpx;
		margin-bottom: 20rpx;
		border-radius: 12rpx;
		overflow: hidden;
		background: linear-gradient(to right, #ff5e3a, #ff2a2a);
		color: #fff;
		position: relative;
		
		/* 已领取样式 */
		&.received {
			background: linear-gradient(to right, #e0e0e0, #b0b0b0);
			opacity: 0.9;
			
			.coupon-value, .coupon-name {
				color: #666;
			}
			
			.coupon-condition, .coupon-time {
				color: #999;
			}
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
	}

	.receive-btn {
		align-self: flex-end;
		width: 150rpx;
		height: 50rpx;
		line-height: 50rpx;
		font-size: 24rpx;
		color: #ff2a2a;
		background-color: #fff;
		border-radius: 25rpx;
		border: none;
		padding: 0;
		
		&.received {
			color: #fff;
			background-color: rgba(255, 255, 255, 0.3);
			border: 1rpx solid rgba(255, 255, 255, 0.5);
		}
	}
	
	/* 已领取标签 */
	.received-tag {
		position: absolute;
		right: 0;
		top: 0;
		width: 120rpx;
		height: 120rpx;
		overflow: hidden;
		
		text {
			position: absolute;
			right: -40rpx;
			top: 10rpx;
			width: 120rpx;
			height: 30rpx;
			line-height: 30rpx;
			background-color: #999;
			color: #fff;
			font-size: 22rpx;
			text-align: center;
			transform: rotate(45deg);
			transform-origin: left top;
		}
	}
</style>