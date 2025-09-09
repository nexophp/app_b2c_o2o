<script>
export default {
	onLaunch: function () {
		console.log('App Launch')
	},
	onShow: function () {
		console.log('App Show')
	},
	onHide: function () {
		console.log('App Hide')
	}
}
</script>

<style lang="scss">
@import "dry-uview-ui/index.scss";
@import "cl-uni-2/index.scss";

@font-face {
  font-family: 'iconfont';  /* Project id 5007443 */
  src: url('//at.alicdn.com/t/c/font_5007443_1he2t4mlfap.woff2?t=1756281886821') format('woff2'),
       url('//at.alicdn.com/t/c/font_5007443_1he2t4mlfap.woff?t=1756281886821') format('woff'),
       url('//at.alicdn.com/t/c/font_5007443_1he2t4mlfap.ttf?t=1756281886821') format('truetype');
}

page {
	background: var(--background, #F7F7F7);
	color: var(--text, #333);
}

.iconfont {
	font-family: 'iconfont';
	font-size: 26px;
}

/* 全局按钮样式修复 */
button {
	border: none !important;
	outline: none !important;
	box-sizing: border-box !important;
}

button::after {
	border: none !important;
}

.bottom-actions {
	display: flex;
	justify-content: space-between;
}

.form-actions,
.bottom-action {
	display: flex;
	justify-content: center;
	align-items: center;
}

.total-amount {
	color: $uni-color-error !important;
}

.required::after {
	content: '*';
	color: $uni-color-error !important;
}

/* 状态样式 */
.status-pending {
	font-size: 32rpx;
	color: #ff9500;
	font-weight: bold;
	margin-bottom: 10rpx;
	display: block;
}

.status-paid {
	font-size: 32rpx;
	color: #007aff;
	font-weight: bold;
	margin-bottom: 10rpx;
	display: block;
}

.status-delivered {
	font-size: 32rpx;
	color: #ff6b35;
	font-weight: bold;
	margin-bottom: 10rpx;
	display: block;
}

.status-complete {
	font-size: 32rpx;
	color: #52c41a;
	font-weight: bold;
	margin-bottom: 10rpx;
	display: block;
}

.status-cancel {
	font-size: 32rpx;
	color: #999;
	font-weight: bold;
	margin-bottom: 10rpx;
	display: block;
}

.status-refund {
	font-size: 32rpx;
	color: #f5222d !important;
	font-weight: bold;
	margin-bottom: 10rpx;
	display: block;
}

/* 底部安全距离 */
.bottom-safe-area {
	height: 120rpx;
}
</style>