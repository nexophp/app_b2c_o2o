<template>
	<view class="daka-page">

		<!-- 打卡区域 -->
		<view class="punch-section">
			<!-- 当前时间 -->
			<view class="time-display">
				<text class="current-time">{{ currentTime }}</text>
				<text class="current-date">{{ currentDate }}</text>
			</view>

			<!-- 位置信息 -->
			<view class="location-info">
				<view class="location-item">
					<uni-icons type="location" size="16" color="#666"></uni-icons>
					<text class="location-text">{{ locationInfo }}</text>
				</view>
				<view class="refresh-location" @click="getLocation">
					<uni-icons type="refresh" size="14" color="#007aff"></uni-icons>
					<text class="refresh-text">刷新</text>
				</view>
			</view>

			<!-- 打卡按钮 -->
			<view class="punch-buttons">
				<!-- 上班打卡按钮 -->
				<view class="punch-btn-container" v-if="!todayRecord.work_in">
					<view class="punch-btn work-in" @click="punchIn()">
						<view class="btn-icon">
							<uni-icons type="checkmarkempty" size="30" color="#fff"></uni-icons>
						</view>
						<text class="btn-text">上班打卡</text>
					</view>
				</view>
				
				<!-- 下班打卡按钮 -->
				<view class="punch-btn-container" v-if="todayRecord.work_in && !todayRecord.work_out">
					<view class="punch-btn work-out" @click="punchIn()">
						<view class="btn-icon">
							<uni-icons type="home" size="30" color="#fff"></uni-icons>
						</view>
						<text class="btn-text">下班打卡</text>
					</view>
				</view>
				
				<!-- 已完成打卡状态显示 -->
				<view class="punch-btn-container" v-if="todayRecord.work_out">
					<view class="punch-btn work-out " @click="punchIn()">
						<view class="btn-icon">
							<uni-icons type="checkmarkempty" size="30" color="#fff"></uni-icons>
						</view>
						<text class="btn-text">打卡完成</text> 
					</view>
				</view>
			</view>
		</view>

		<!-- 打卡记录 -->
		<view class="records-section">
			<view class="section-header">
				<text class="section-title">打卡记录</text>
				<view class="month-selector">
					<view class="month-btn" @click="changeMonth(-1)">
						<uni-icons type="left" size="16" color="#007aff"></uni-icons>
					</view>
					<text class="current-month">{{ currentMonth }}</text>
					<view class="month-btn" @click="changeMonth(1)">
						<uni-icons type="right" size="16" color="#007aff"></uni-icons>
					</view>
				</view>
			</view>

			<view class="records-list">
				<view class="record-item" v-for="(record, index) in punchRecords" :key="index">
					<view class="record-date">
						<text class="date-text">{{ record.date }}</text>
						<text class="weekday-text">{{ record.weekday }}</text>
					</view>
					<view class="record-times">
						<view class="time-item">
							<text class="time-label">上班</text>
							<text class="time-value" :class="{ late: record.work_in_late }">{{ record.work_in_time ||
								'--:--' }}</text>
							<text class="late-tag" v-if="record.work_in_late">迟到</text>
						</view>
						<view class="time-item">
							<text class="time-label">下班</text>
							<text class="time-value" :class="{ early: record.work_out_early }">{{ record.work_out_time ||
								'--:--' }}</text>
							<text class="early-tag" v-if="record.work_out_early">早退</text>
						</view>
					</view>
					<view class="record-status">
						<text class="status-text" :class="record.status">{{ getStatusText(record.status) }}</text>
					</view>
				</view>

				<!-- 空状态 -->
				<view class="empty-state" v-if="punchRecords.length === 0">
					<uni-icons type="calendar" size="60" color="#ccc"></uni-icons>
					<text class="empty-text">暂无打卡记录</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			status_height: 0,
			currentTime: '',
			currentDate: '',
			locationInfo: '正在获取位置...',
			currentMonth: '',
			selectedMonth: '',
			todayRecord: {
				work_in: false,
				work_out: false,
				work_in_time: '',
				work_out_time: ''
			},
			punchRecords: []
		}
	},
	onLoad() {
		this.initPage();
	},
	onShow() {
		this.updateTime();
		this.getLocation();
		this.getTodayRecord();
		this.loadPunchRecords();
	},
	methods: {
		// 初始化页面
		initPage() {
			// 获取状态栏高度
			const systemInfo = uni.getSystemInfoSync();
			this.status_height = systemInfo.statusBarHeight;

			// 初始化当前月份
			const now = new Date();
			const year = now.getFullYear();
			const month = String(now.getMonth() + 1).padStart(2, '0');
			this.selectedMonth = `${year}-${month}`;
			this.currentMonth = `${year}年${month}月`;

			// 开始时间更新定时器
			this.timeInterval = setInterval(() => {
				this.updateTime();
			}, 1000);
		},

		// 更新当前时间
		updateTime() {
			const now = new Date();
			const hours = String(now.getHours()).padStart(2, '0');
			const minutes = String(now.getMinutes()).padStart(2, '0');
			const seconds = String(now.getSeconds()).padStart(2, '0');

			this.currentTime = `${hours}:${minutes}:${seconds}`;

			const year = now.getFullYear();
			const month = String(now.getMonth() + 1).padStart(2, '0');
			const date = String(now.getDate()).padStart(2, '0');
			const weekdays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
			const weekday = weekdays[now.getDay()];

			this.currentDate = `${year}年${month}月${date}日 ${weekday}`;
		},

		// 获取位置信息
		getLocation() {
			this.locationInfo = '正在获取位置...';

			uni.getLocation({
				type: 'gcj02',
				success: (res) => {
					// 这里可以调用逆地理编码API获取详细地址
					this.locationInfo = `经度:${res.longitude.toFixed(6)} 纬度:${res.latitude.toFixed(6)}`;

					// 模拟地址信息
					setTimeout(() => {
						this.locationInfo = '北京市朝阳区建国门外大街1号';
					}, 1000);
				},
				fail: (err) => {
					this.locationInfo = '位置获取失败，请检查定位权限';
					console.error('获取位置失败:', err);
				}
			});
		},

		// 获取今日打卡记录
		getTodayRecord() {
			const today = new Date();
			const year = today.getFullYear();
			const month = String(today.getMonth() + 1).padStart(2, '0');

			this.ajax(this.config.daka.index, {
				month: `${year}-${month}`
			}).then(res => {
				if (res.code == 0) {
					const todayStr = String(today.getDate()).padStart(2, '0');
					const todayRecord = res.data.find(record =>
						record.date.endsWith('-' + todayStr)
					);

					if (todayRecord) {
						this.todayRecord = {
							work_in: !!todayRecord.work_in_time,
							work_out: !!todayRecord.work_out_time,
							work_in_time: todayRecord.work_in_time,
							work_out_time: todayRecord.work_out_time
						};
					} else {
						this.todayRecord = {
							work_in: false,
							work_out: false,
							work_in_time: '',
							work_out_time: ''
						};
					}
				}
			}) 
		},

		// 打卡操作
		punchIn() {
			uni.getLocation({
				type: 'gcj02',
				success: (res) => {
					uni.showLoading({
						title: '打卡中...'
					});

					this.ajax(this.config.daka.do, {
						lat: res.latitude,
						lng: res.longitude
					}).then(result => {
						uni.hideLoading();
						if (result.code == 0) {
							uni.showToast({
								title: result.msg || '打卡成功',
								icon: 'success'
							});

							// 刷新今日打卡记录和打卡记录列表
							this.getTodayRecord();
							this.loadPunchRecords();
						} else {
							uni.showToast({
								title: result.msg || '打卡失败',
								icon: 'none'
							});
						}
					})
				},
			});
		},

		// 切换月份
		changeMonth(direction) {
			const currentDate = new Date(this.selectedMonth + '-01');
			currentDate.setMonth(currentDate.getMonth() + direction);

			const year = currentDate.getFullYear();
			const month = String(currentDate.getMonth() + 1).padStart(2, '0');
			this.selectedMonth = `${year}-${month}`;
			this.currentMonth = `${year}年${month}月`;

			this.loadPunchRecords();
		},

		// 加载打卡记录
		loadPunchRecords() {
			uni.showLoading({
				title: '加载中...'
			});

			this.ajax(this.config.daka.index, {
				month: this.selectedMonth
			}).then(res => {
				uni.hideLoading();
				if (res.code == 0) {
					this.punchRecords = res.data || [];
				} else {
					uni.showToast({
						title: res.msg || '获取记录失败',
						icon: 'none'
					});
				}
			})
		},

		// 获取状态文本
		getStatusText(status) {
			const statusMap = {
				normal: '正常',
				late: '迟到',
				early: '早退',
				absent: '缺勤'
			};
			return statusMap[status] || '未知';
		},

		// 返回上一页
		goBack() {
			uni.navigateBack();
		}
	},

	onUnload() {
		// 清除定时器
		if (this.timeInterval) {
			clearInterval(this.timeInterval);
		}
	}
}
</script>

<style>
.daka-page {
	background-color: #f5f5f5;
	min-height: 100vh;
}

/* 导航栏 */
.navbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	height: 88rpx;
	padding: 0 30rpx;
	background-color: #fff;
	border-bottom: 1rpx solid #eee;
}

.nav-left,
.nav-right {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.nav-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #333;
}

/* 打卡区域 */
.punch-section {
	background-color: #fff;
	margin: 20rpx 30rpx;
	border-radius: 20rpx;
	padding: 40rpx 30rpx;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

/* 时间显示 */
.time-display {
	text-align: center;
	margin-bottom: 30rpx;
}

.current-time {
	font-size: 60rpx;
	font-weight: bold;
	color: #333;
	display: block;
	margin-bottom: 10rpx;
}

.current-date {
	font-size: 28rpx;
	color: #666;
	display: block;
}

/* 位置信息 */
.location-info {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 20rpx;
	background-color: #f8f9fa;
	border-radius: 12rpx;
	margin-bottom: 40rpx;
}

.location-item {
	display: flex;
	align-items: center;
	flex: 1;
}

.location-text {
	font-size: 26rpx;
	color: #666;
	margin-left: 8rpx;
}

.refresh-location {
	display: flex;
	align-items: center;
	padding: 8rpx 16rpx;
	border-radius: 20rpx;
	background-color: #e3f2fd;
}

.refresh-text {
	font-size: 24rpx;
	color: #007aff;
	margin-left: 4rpx;
}

/* 打卡按钮 */
.punch-buttons {
	display: flex;
	justify-content: space-around;
	gap: 30rpx;
}

.punch-btn-container {
	flex: 1;
}

.punch-btn {
	width: 100%;
	border-radius: 20rpx;
	display: flex; 
	border: none;
	color: #fff;
	font-size: 28rpx;
	display: flex;
	height:100rpx;
	 
	align-items: center;
	justify-content: center;
	position: relative;
	box-shadow: 0 8rpx 20rpx rgba(0, 0, 0, 0.1);
}

.punch-btn.work-in {
	background: linear-gradient(135deg, #4CAF50, #45a049);
}

.punch-btn.work-out {
	background: linear-gradient(135deg, #FF9800, #f57c00);
}

.punch-btn.disabled {
	opacity: 0.6;
	pointer-events: none;
}

.punch-btn:disabled {
	background: #ccc !important;
	color: #999 !important;
}

.punch-btn::after {
	border: none;
}

.btn-icon {
	margin-bottom: 8rpx;
}

.btn-text {
	font-size: 28rpx;
	font-weight: bold;
	color: #fff;
	margin-bottom: 4rpx;
}

.btn-time {
	font-size: 24rpx;
	color: rgba(255, 255, 255, 0.9);
	font-weight: normal;
}

.btn-time {
	font-size: 24rpx;
	opacity: 0.9;
	margin-top: 4rpx;
}

/* 记录区域 */
.records-section {
	background-color: #fff;
	margin: 20rpx 30rpx;
	border-radius: 20rpx;
	padding: 30rpx;
	box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

.section-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 30rpx;
}

.section-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #333;
}

.month-selector {
	display: flex;
	align-items: center;
	background-color: #f8f9fa;
	border-radius: 25rpx;
	padding: 8rpx 16rpx;
}

.month-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 50%;
	transition: all 0.3s ease;
}

.month-btn:active {
	background-color: #e3f2fd;
	transform: scale(0.95);
}

.current-month {
	font-size: 28rpx;
	font-weight: 500;
	color: #333;
	margin: 0 20rpx;
	min-width: 120rpx;
	text-align: center;
}



/* 记录列表 */
.records-list {
	min-height: 400rpx;
}

.record-item {
	display: flex;
	align-items: center;
	padding: 25rpx 0;
	border-bottom: 1rpx solid #f5f5f5;
}

.record-item:last-child {
	border-bottom: none;
}

.record-date {
	width: 120rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
}

.date-text {
	font-size: 28rpx;
	font-weight: bold;
	color: #333;
	margin-bottom: 4rpx;
}

.weekday-text {
	font-size: 22rpx;
	color: #999;
}

.record-times {
	flex: 1;
	display: flex;
	justify-content: space-around;
	margin: 0 20rpx;
}

.time-item {
	display: flex;
	flex-direction: column;
	align-items: center;
	position: relative;
}

.time-label {
	font-size: 22rpx;
	color: #999;
	margin-bottom: 8rpx;
}

.time-value {
	font-size: 28rpx;
	color: #333;
	font-weight: bold;
}

.time-value.late {
	color: #ff4757;
}

.time-value.early {
	color: #ffa502;
}

.late-tag,
.early-tag {
	position: absolute;
	top: -8rpx;
	right: -20rpx;
	font-size: 18rpx;
	color: #fff;
	padding: 2rpx 8rpx;
	border-radius: 8rpx;
}

.late-tag {
	background-color: #ff4757;
}

.early-tag {
	background-color: #ffa502;
}

.record-status {
	width: 80rpx;
	display: flex;
	justify-content: center;
}

.status-text {
	font-size: 22rpx;
	padding: 4rpx 12rpx;
	border-radius: 12rpx;
	text-align: center;
}

.status-text.normal {
	color: #52c41a;
	background-color: #f6ffed;
}

.status-text.late {
	color: #ff4757;
	background-color: #fff5f5;
}

.status-text.early {
	color: #ffa502;
	background-color: #fff9f0;
}

.status-text.absent {
	color: #999;
	background-color: #f5f5f5;
}

/* 空状态 */
.empty-state {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 300rpx;
}

.empty-text {
	font-size: 28rpx;
	color: #999;
	margin-top: 20rpx;
}
</style>
