<template>
	<view class="logistics-detail">
		<!-- 加载状态 -->
		<view v-if="loading" class="loading-section">
			<uni-load-more status="loading"
				:content-text="{ contentdown: '加载中...', contentrefresh: '加载中...', contentnomore: '加载中...' }"></uni-load-more>
		</view>

		<view v-else>
			<!-- 头部信息 -->
			<view class="header-section">
				<view class="logistics-header">
					<text class="company-name">{{ logisticsInfo.title }}</text>
					<text class="tracking-no">{{ logisticsInfo.no }}</text>
				</view>
				<view class="status-info">
					<text class="status-text">{{ logisticsInfo.status }}</text>
					<text class="take-time" v-if="logisticsInfo.take_time">用时：{{ logisticsInfo.take_time }}</text>
				</view>
			</view>

			<!-- 物流进度 -->
			<view class="progress-section">
				<view class="section-title">物流进度</view>
				<view class="progress-list">
					<view v-for="(item, index) in logisticsInfo.list" :key="index" class="progress-item"
						:class="{ first: index === 0 }">
						<view class="progress-dot"></view>
						<view class="progress-content">
							<view class="progress-time">{{ item.time }}</view>
							<view class="progress-desc">{{ item.title }}</view>
							<view class="progress-status">{{ item.status }}</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
var _this
export default {
	data() {
		return {
			logisticsInfo: {},
			loading: true,
			no: '',
			type: '',
			id: ''
		}
	},
	onLoad(options) {
		_this = this 
		this.no = options.no
		this.type = options.type
		this.id = options.id
		if (this.id) {
			this.load()
		} else {
			this.loadLogisticsDetail()
		}
		
	},
	methods: { 
		loadLogisticsDetail() {
			this.ajax(this.config.logistic, {
				no: this.no,
				type: this.type
			}).then(res => {
				this.loading = false
				this.logisticsInfo = res
			})
		},
		load() {
			this.ajax(this.config.order.view,{id:this.id}).then(res=>{
				this.no = res.data.logic_info.data.no || ''
				this.type = res.data.logic_info.data.type|| ''
				this.loadLogisticsDetail()
			})
		}
	}
}
</script>

<style>
.logistics-detail {
	background-color: #f5f5f5;
	min-height: 100vh;
	padding-bottom: env(safe-area-inset-bottom);
}

.loading-section {
	padding: 100rpx 0;
	text-align: center;
}

.header-section {
	background-color: #fff;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.logistics-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
}

.company-name {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
}

.tracking-no {
	font-size: 28rpx;
	color: #007aff;
	font-weight: 500;
}

.status-info {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.status-text {
	font-size: 28rpx;
	color: #ff6b35;
	font-weight: bold;
}

.take-time {
	font-size: 24rpx;
	color: #666;
}

.progress-section {
	background-color: #fff;
	padding: 30rpx;
}

.section-title {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
	margin-bottom: 30rpx;
}

.progress-list {
	position: relative;
}

.progress-item {
	display: flex;
	margin-bottom: 20rpx;
	position: relative;
}

.progress-item:last-child {
	margin-bottom: 0;
}

.progress-dot {
	width: 20rpx;
	height: 20rpx;
	background-color: #e0e0e0;
	border-radius: 50%;
	margin-right: 30rpx;
	margin-top: 8rpx;
	position: relative;
	z-index: 2;
}

.progress-item.first .progress-dot {
	background-color: #007aff;
}

.progress-item:not(:last-child)::after {
	content: '';
	position: absolute;
	left: 9rpx;
	top: 28rpx;
	width: 2rpx;
	height: calc(100% + 12rpx);
	background-color: #e0e0e0;
	z-index: 1;
}

.progress-content {
	flex: 1;
}

.progress-time {
	font-size: 26rpx;
	color: #666;
	margin-bottom: 8rpx;
}

.progress-desc {
	font-size: 28rpx;
	color: #333;
	line-height: 1.4;
	margin-bottom: 8rpx;
}

.progress-status {
	font-size: 24rpx;
	color: #999;
}

.progress-item.first .progress-time,
.progress-item.first .progress-desc {
	color: #007aff;
	font-weight: 500;
}
</style>
