<template>
	<view class="service-record-container">
		<!-- 消费记录列表 -->
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">
			<view class="record-list">
				<view v-for="(record, index) in recordList" :key="index" class="record-item">
					<!-- 记录头部 -->
					<view class="record-header">
						<text class="record-time">{{record.created_at_text}}</text>
						<text class="record-status">{{record.reason}}</text>
					</view>

					<!-- 记录内容 -->
					<view class="record-content">
						<view class="content-left">
							<text class="record-desc">{{record.item.title||''}}</text> 
						</view>
						<view class="content-right">
							<view class=""  v-if="record.count > 0">
								<text class="record-count-green">{{record.count}}次</text> 
							</view>
							<view class="" v-else>
								<text class="record-count" >{{record.count}}次</text>
							</view>
						</view>
					</view>
				</view>
			</view>
		</mescroll-body>

		<!-- 空状态 -->
		<view class="empty-state" v-if="recordList.length === 0 && !isLoading">
			<image src="/static/images/empty-record.png" mode="aspectFit"></image>
			<text>暂无消费记录</text>
		</view>
	</view>
</template>

<script>
	import MescrollMixin from "@/uni_modules/mescroll-uni/components/mescroll-uni/mescroll-mixins.js";
	var _this
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
						size: 10
					},
					noMoreSize: 1,
					empty: {
						tip: '暂无消费记录',
						btnText: '刷新试试'
					},
					textNoMore: '-- 没有更多了 --'
				},
				downOption: {
					auto: false
				},
				recordList: [],
				isLoading: false
			}
		},
		methods: {
			/*下拉刷新的回调 */
			downCallback() {
				this.where.page = 1
				this.load()
				this.mescroll.resetUpScroll();
			},
			/*上拉加载的回调 */
			upCallback(page) {
				this.where.page = page.num
				this.load()
			},
			/*加载消费记录 */
			load() {
				let w = this.where
				_this.ajax(_this.config.service.cost.index, w).then(res => {
					if (res.current_page == 1) {
						_this.recordList = []
					}
					for (let i in res.data) {
						let order = res.data[i]
						_this.recordList.push(order)
					}
					_this.mescroll.endBySize(res.per_page, res.total);
				})
			},
		},
		onLoad() {
			_this = this
		}
	}
</script>

<style lang="scss">
	.service-record-container {
		padding: 20rpx;
		background-color: #f8f8f8;
		min-height: 100vh;

		.record-list {
			.record-item {
				background-color: #fff;
				border-radius: 16rpx;
				margin-bottom: 20rpx;
				padding: 24rpx;
				box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);

				.record-header {
					display: flex;
					justify-content: space-between;
					align-items: center;
					margin-bottom: 20rpx;

					.record-time {
						font-size: 26rpx;
						color: #666;
					}

					.record-status {
						font-size: 26rpx;
						color: #999;
						font-weight: bold;
					}
				}

				.record-content {
					display: flex;
					justify-content: space-between;
					align-items: center;

					.content-left {
						flex: 1;

						.record-desc {
							font-size: 28rpx;
							color: #333;
						}
					}

					.content-right {
						.record-count {
							font-size: 28rpx;
							color: #ff6b35;
							font-weight: bold;
						}
						.record-count-green {
							font-size: 28rpx;
							color: #4cd964;
							font-weight: bold;
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
			padding-top: 100rpx;

			image {
				width: 240rpx;
				height: 240rpx;
				margin-bottom: 30rpx;
				opacity: 0.8;
			}

			text {
				font-size: 28rpx;
				color: #999;
			}
		}
	}
</style>