<template>
	<view class="refund-detail">
		<!-- 加载状态 -->
		<view v-if="loading" class="loading-section">
			<text>加载中...</text>
		</view>
		
		<view v-else>
			<!-- 售后状态 -->
			<view class="status-section">
				<view class="status-info">
					<text class="status-text" :class="'status-' + refundInfo.status">{{ getStatusDesc(refundInfo.status) }}</text>
					<text class="status-desc">{{ refundInfo.status_desc || '' }}</text>
				</view>
			</view>
			
			<!-- 售后信息 -->
			<view class="refund-section">
				<view class="section-title">售后信息</view>
				<view class="refund-item">
					<text class="refund-label">售后编号</text>
					<text class="refund-value">{{ refundInfo.order_num }}</text>
				</view>
				<view class="refund-item">
					<text class="refund-label">售后类型</text>
					<text class="refund-value">{{ getTypeText(refundInfo.type) }}</text>
				</view>
				<view class="refund-item">
					<text class="refund-label">申请时间</text>
					<text class="refund-value">{{ refundInfo.updated_at_format }}</text>
				</view>
				<view class="refund-item" v-if="refundInfo.amount > 0">
					<text class="refund-label">售后金额</text>
					<text class="refund-value amount">¥{{ refundInfo.amount }}</text>
				</view>
				<view v-if="refundInfo.reason" class="refund-item">
					<text class="refund-label">申请原因</text>
					<text class="refund-value">{{ refundInfo.reason }}</text>
				</view>
				<view v-if="refundInfo.description" class="refund-item">
					<text class="refund-label">问题描述</text>
					<text class="refund-value">{{ refundInfo.description }}</text>
				</view>
			</view>
			
			<!-- 商品信息 -->
			<view class="product-section">
				<view class="section-title">商品信息</view>
				<view class="product-item" v-for="item in refundInfo.items">
					<image class="product-image" :src="item.image" mode="aspectFill"></image>
					<view class="product-info">
						<text class="product-name">{{ item.title }}</text> 
						<view class="product-price-qty">
							<text class="product-price">¥{{ item.price }}</text>
							<text class="product-qty">x{{ item.num }}</text>
						</view>
					</view>
				</view>
			</view>
			
			<!-- 物流信息表单 (仅当type为exchange或return时显示) -->
			<view v-if="needLogistics" class="logistics-section">
				<view class="section-title">物流信息</view>
				
				<!-- 收货地址 -->
				<view v-if="refundInfo.return_address" class="address-info">
					<view class="address-title">退货地址</view>
					<view class="address-detail">
						<text class="address-name">{{ refundInfo.return_address.name }}</text>
						<text class="address-phone">{{ refundInfo.return_address.phone }}</text>
					</view>
					<text class="address-text">{{ refundInfo.return_address.address }}</text>
				</view>
				
				<!-- 物流公司选择 -->
				<view class="form-item">
					<text class="form-label">物流公司</text>
					<picker :range="logisticCompanies" range-key="label" @change="onLogisticChange">
						<view class="picker-input">
							<text class="picker-text" :class="{placeholder: !selectedLogistic.label}">{{ selectedLogistic.label || '请选择物流公司' }}</text>
							<text class="picker-arrow">></text>
						</view>
					</picker>
				</view>
				
				<!-- 物流单号输入 -->
				<view class="form-item">
					<text class="form-label">物流单号</text>
					<input class="form-input" v-model="logisticNo" placeholder="请输入物流单号" />
				</view>
				
				<!-- 提交按钮 -->
				<view class="submit-section">
					<button class="submit-btn" :disabled="!canSubmit" @click="submitLogistics">提交物流信息</button>
				</view>
			</view>
			
			<!-- 物流跟踪信息 (如果已提交物流) -->
			<view v-if="refundInfo.logistics_info" class="tracking-section">
				<view class="section-title">物流跟踪</view>
				<view class="tracking-item">
					<text class="tracking-label">物流公司</text>
					<text class="tracking-value">{{ refundInfo.logistics_info.company_name }}</text>
				</view>
				<view class="tracking-item">
					<text class="tracking-label">物流单号</text>
					<text class="tracking-value">{{ refundInfo.logistics_info.tracking_no }}</text>
				</view>
				<view class="tracking-item">
					<text class="tracking-label">发货时间</text>
					<text class="tracking-value">{{ refundInfo.logistics_info.ship_time }}</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script> 
	export default { 
		data() {
			return {
				loading: true,
				refundId: '',
				refundInfo: {},
				logisticCompanies: [],
				selectedLogistic: {},
				logisticNo: ''
			}
		},
		computed: {
			// 是否需要填写物流信息
			needLogistics() {
				return (this.refundInfo.type === 'exchange' || this.refundInfo.type === 'return') && 
					   this.refundInfo.status === 'approved' && 
					   !this.refundInfo.logistics_info
			},
			// 是否可以提交
			canSubmit() {
				return this.selectedLogistic.value && this.logisticNo.trim()
			}
		},
		onLoad(options) {
			this.refundId = options.id
			this.loadRefundDetail()
			this.loadLogisticCompanies()
		},
		methods: {
			// 加载售后详情
			loadRefundDetail() {
				this.ajax(this.config.refund.detail, {
					id: this.refundId
				}).then(res => {
					if (res.code === 0) {
						this.refundInfo = res.data
					} else {
						uni.showToast({
							title: res.msg || '获取售后详情失败',
							icon: 'none'
						})
					}
					this.loading = false
				}).catch(err => {
					console.error('获取售后详情失败:', err)
					uni.showToast({
						title: '网络错误',
						icon: 'none'
					})
					this.loading = false
				})
			},
			
			// 加载物流公司列表
			loadLogisticCompanies() {
				this.ajax(this.config.logistic_support, {}).then(res => {
					if (res.code === 0) {
						this.logisticCompanies = res.data || []
					}
				}).catch(err => {
					console.error('获取物流公司失败:', err)
				})
			},
			
			// 物流公司选择
			onLogisticChange(e) {
				const index = e.detail.value
				this.selectedLogistic = this.logisticCompanies[index] || {}
			},
			
			// 提交物流信息
			submitLogistics() {
				if (!this.canSubmit) {
					uni.showToast({
						title: '请完善物流信息',
						icon: 'none'
					})
					return
				}
				
				uni.showLoading({
					title: '提交中...'
				})
				
				this.ajax(this.config.refund.logic, {
					id: this.refundId,
					logistic_company: this.selectedLogistic.value,
					logistic_no: this.logisticNo.trim()
				}).then(res => {
					uni.hideLoading()
					if (res.code === 0) {
						uni.showToast({
							title: '提交成功',
							icon: 'success'
						})
						// 重新加载详情
						this.loadRefundDetail()
					} else {
						uni.showToast({
							title: res.msg || '提交失败',
							icon: 'none'
						})
					}
				}).catch(err => {
					uni.hideLoading()
					console.error('提交物流信息失败:', err)
					uni.showToast({
						title: '网络错误',
						icon: 'none'
					})
				})
			},
			
			// 获取状态文本
			getStatusText(status) {
				const statusMap = {
					pending: '待审核',
					approved: '已同意',
					rejected: '已拒绝',
					returning: '退货中',
					returned: '已退货',
					refunded: '已退款',
					exchanging: '换货中',
					exchanged: '已换货',
					completed: '已完成',
					cancelled: '已取消'
				}
				return statusMap[status] || status
			},
			
			// 获取状态描述
			getStatusDesc(status) {
				const descMap = {
					wait: '等待处理',
					approved: '已通过',
					rejected: '拒绝', 
				}
				return descMap[status] || ''
			},
			
			// 获取类型文本
			getTypeText(type) {
				const typeMap = {
					refund: '仅退款',
					return: '退货退款',
					exchange: '换货'
				}
				return typeMap[type] || type
			}
		}
	}
</script>

<style>
	.refund-detail {
		background-color: #f5f5f5;
		min-height: 100vh;
	}
	
	/* 加载状态 */
	.loading-section {
		padding: 100rpx 0;
		text-align: center;
		color: #999;
	}
	
	/* 状态区域 */
	.status-section {
		background-color: #fff;
		padding: 40rpx 30rpx;
		margin-bottom: 20rpx;
		text-align: center;
	}
	
	.status-text {
		font-size: 36rpx;
		font-weight: bold;
		display: block;
		margin-bottom: 20rpx;
	}
	
	.status-pending {
		color: #ff9500;
	}
	
	.status-approved {
		color: #007aff;
	}
	
	.status-rejected {
		color: #f5222d;
	}
	
	.status-returning,
	.status-exchanging {
		color: #ff6b35;
	}
	
	.status-returned,
	.status-exchanged,
	.status-refunded,
	.status-completed {
		color: #52c41a;
	}
	
	.status-cancelled {
		color: #999;
	}
	
	.status-desc {
		font-size: 28rpx;
		color: #666;
		line-height: 1.5;
	}
	
	/* 通用区域样式 */
	.refund-section,
	.product-section,
	.logistics-section,
	.tracking-section {
		background-color: #fff;
		margin-bottom: 20rpx;
		padding: 30rpx;
	}
	
	.section-title {
		font-size: 32rpx;
		font-weight: bold;
		color: #333;
		margin-bottom: 30rpx;
		padding-bottom: 20rpx;
		border-bottom: 1px solid #f0f0f0;
	}
	
	/* 售后信息 */
	.refund-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20rpx 0;
		border-bottom: 1px solid #f8f8f8;
	}
	
	.refund-item:last-child {
		border-bottom: none;
	}
	
	.refund-label {
		font-size: 28rpx;
		color: #666;
		flex-shrink: 0;
		width: 160rpx;
	}
	
	.refund-value {
		font-size: 28rpx;
		color: #333;
		flex: 1;
		text-align: right;
	}
	
	.refund-value.amount {
		color: #ff6b35;
		font-weight: bold;
	}
	
	/* 商品信息 */
	.product-item {
		display: flex;
		align-items: center;
	}
	
	.product-image {
		width: 120rpx;
		height: 120rpx;
		border-radius: 8rpx;
		margin-right: 20rpx;
		flex-shrink: 0;
	}
	
	.product-info {
		flex: 1;
	}
	
	.product-name {
		font-size: 28rpx;
		color: #333;
		display: block;
		margin-bottom: 10rpx;
		line-height: 1.4;
	}
	
	.product-spec {
		font-size: 24rpx;
		color: #999;
		display: block;
		margin-bottom: 10rpx;
	}
	
	.product-price-qty {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	
	.product-price {
		font-size: 28rpx;
		color: #ff6b35;
		font-weight: bold;
	}
	
	.product-qty {
		font-size: 24rpx;
		color: #999;
	}
	
	/* 地址信息 */
	.address-info {
		background-color: #f8f9fa;
		padding: 30rpx;
		border-radius: 12rpx;
		margin-bottom: 30rpx;
	}
	
	.address-title {
		font-size: 28rpx;
		color: #333;
		font-weight: bold;
		margin-bottom: 20rpx;
		display: block;
	}
	
	.address-detail {
		display: flex;
		justify-content: space-between;
		margin-bottom: 10rpx;
	}
	
	.address-name {
		font-size: 28rpx;
		color: #333;
	}
	
	.address-phone {
		font-size: 28rpx;
		color: #666;
	}
	
	.address-text {
		font-size: 26rpx;
		color: #666;
		line-height: 1.4;
	}
	
	/* 表单项 */
	.form-item {
		margin-bottom: 30rpx;
	}
	
	.form-label {
		font-size: 28rpx;
		color: #333;
		display: block;
		margin-bottom: 20rpx;
	}
	
	.picker-input {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 24rpx 30rpx;
		background-color: #f8f9fa;
		border-radius: 12rpx;
		border: 1px solid #e9ecef;
	}
	
	.picker-text {
		font-size: 28rpx;
		color: #333;
	}
	
	.picker-text.placeholder {
		color: #999;
	}
	
	.picker-arrow {
		font-size: 24rpx;
		color: #999;
	}
	
	.form-input {
		padding: 24rpx 30rpx;
		background-color: #f8f9fa;
		border-radius: 12rpx;
		border: 1px solid #e9ecef;
		font-size: 28rpx;
		color: #333;
	}
	
	/* 提交区域 */
	.submit-section {
		margin-top: 40rpx;
	}
	
	.submit-btn {
		width: 100%;
		padding: 30rpx;
		background-color: #007aff;
		color: #fff;
		border-radius: 12rpx;
		font-size: 32rpx;
		border: none;
	}
	
	.submit-btn[disabled] {
		background-color: #ccc;
		color: #999;
	}
	
	/* 物流跟踪 */
	.tracking-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20rpx 0;
		border-bottom: 1px solid #f8f8f8;
	}
	
	.tracking-item:last-child {
		border-bottom: none;
	}
	
	.tracking-label {
		font-size: 28rpx;
		color: #666;
		flex-shrink: 0;
		width: 160rpx;
	}
	
	.tracking-value {
		font-size: 28rpx;
		color: #333;
		flex: 1;
		text-align: right;
	}
</style>