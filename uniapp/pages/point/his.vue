<template>
	<view>
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">

			<view class="point-list" v-if="list && list.length > 0">
				<!-- 积分记录列表 -->
				<view class="point-container">
					<view v-for="(item, index) in list" :key="index" class="point-item">
						<!-- 积分变动信息 -->
						<view class="point-info">
							<text class="point-title">{{ item.title }}</text>
							<text class="point-time">{{ item.created_at_text }}</text>
						</view>

						<!-- 积分变动值 -->
						<view class="point-value" :class="{ 'income': item.point > 0, 'expense': item.point < 0 }">
							{{ item.point > 0 ? '+' : '' }}{{ item.point }}
						</view>
					</view>
				</view>
			</view>
			<view v-else>
				<view class="no-point">
					<image src="/static/empty-point.png" class="empty-img"></image>
					<text class="empty-text">暂无积分记录</text>
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
					tip: ' '
				},
				textNoMore: ' '
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

			_this.ajax(_this.config.point.index, w).then(res => {
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
	}
}
</script>

<style lang="scss">
.point-list {
	background-color: #f5f5f5;
	padding-bottom: 30rpx;
}

.point-container {
	padding: 20rpx;
}

.point-item {
	background-color: #fff;
	border-radius: 16rpx;
	margin-bottom: 20rpx;
	padding: 30rpx;
	display: flex;
	justify-content: space-between;
	align-items: center;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.point-info {
	flex: 1;
	display: flex;
	flex-direction: column;
}

.point-title {
	font-size: 32rpx;
	color: #333;
	font-weight: 500;
	margin-bottom: 10rpx;
}

.point-time {
	font-size: 26rpx;
	color: #999;
}

.point-value {
	font-size: 36rpx;
	font-weight: bold;

	&.income {
		color: #ff6b35;
	}

	&.expense {
		color: #666;
	}
}

.no-point {
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
</style>