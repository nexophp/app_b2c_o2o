<template>
	<view class="refund-list">
		<!-- 状态筛选 -->
		<view class="status-tabs">
			<view 
				v-for="(tab, index) in statusTabs" 
				:key="index"
				:class="['tab-item', currentTab === index ? 'active' : '']"
				@click="switchTab(index)"
			>
				{{ tab.name }}
			</view>
		</view>
		
		<!-- 售后列表 -->
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">
			<view class="refund-container">
				<view 
					v-for="refund in refundList" 
					:key="refund.id"
					class="refund-item"
					@click="goToRefundDetail(refund.id)"
				>
					<!-- 售后头部 -->
					<view class="refund-header">
						<text class="refund-no">售后单号：{{ refund.order_num }}</text>
						<text :style="{ color: refund.status_color }">{{ refund.status_text }}</text>
					</view>
					
					<!-- 关联订单信息 -->
					<view class="order-info">
						<text class="order-no">关联订单：{{ refund.order.order_num || '' }}</text>
						<text class="refund-type">{{ refund.type_text }}</text>
					</view>
					
					<!-- 商品信息 -->
					<view class="product-list">
						<view 
							v-for="product in refund.items"  
							class="product-item"
						>
							<image :src="product.image " class="product-image"></image>
							<view class="product-info">
								<text class="product-name">{{ product.title  }}</text>
								<text class="product-spec">{{ product.spec || '' }}</text>
								<view class="product-price-qty">
									<text class="product-price">¥{{ product.price }}</text>
									<text class="product-qty">x{{ product.num }}</text>
								</view>
							</view>
						</view>
					</view>
					
					<!-- 售后原因 -->
					<view class="refund-reason">
						<text class="reason-label">售后原因：</text>
						<text class="reason-text">{{ refund.reason }}</text>
					</view>
					
					<!-- 售后底部 -->
					<view class="refund-footer">
						<view class="refund-info">
							<text class="apply-time">申请时间：{{ refund.created_at_format }}</text>
							<text v-if="refund.refund_amount" class="refund-amount">退款金额：¥{{ refund.amount }}</text>
						</view>
						<view class="action-buttons">
							<cl-button 
								v-for="action in refund.actions" 
								:key="action.type"
								:type="action.uniType"
								size="mini"
								@click.stop="handleAction(action.type, refund)"
							>
								{{ action.text }}
							</cl-button>
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
			currentTab: 0,
			statusTabs: [
				{ name: '全部', status: '' },
				{ name: '待审核', status: 'wait' },
				{ name: '已同意', status: 'approved' },
				{ name: '已拒绝', status: 'rejected' },
				{ name: '已完成', status: 'complete' },
				{ name: '已取消', status: 'cancel' }
			],
			where: {
				page: 1,
				page_size: 10,
				status: ''
			},
			upOption: {
				page: {
					size: 10 // 每页数据的数量,默认10
				},
				noMoreSize: 5, // 配置列表的总数量要大于等于5条才显示'-- END --'的提示
				empty: {
					tip: '暂无售后数据'
				},
				textNoMore: '-- 到底了 --'
			},
			downOption: {
				auto: false, //是否在初始化后,自动执行downCallback; 默认true 
			},
			refundList: []
		}
	},
	onLoad() {
		_this = this
	},
	onShow() {
		// 页面显示时重新加载数据
		if (this.mescroll) {
			this.mescroll.resetUpScroll()
		}
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
			_this.ajax(_this.config.refund.index, w).then(res => {
				if (res.current_page == 1) {
					_this.refundList = []
				}
				for (let i in res.data) {
					let refund = res.data[i] 
					refund.actions = _this.getRefundActions(refund)
					_this.refundList.push(refund)
				}
				_this.mescroll.endBySize(res.per_page, res.total);
			})
		}, 
		getRefundActions(refund) {
			const actions = []
			switch(refund.status) {
				case 'pending':
					actions.push({ type: 'cancel', text: '取消申请', uniType: 'default' })
					break
				case 'approved':
					if (refund.type === 'return' || refund.type === 'exchange') {
						actions.push({ type: 'logistics', text: '填写物流', uniType: 'primary' })
					}
					break
				case 'rejected':
					actions.push({ type: 'reapply', text: '重新申请', uniType: 'primary' })
					break
				case 'completed':
					actions.push({ type: 'review', text: '评价', uniType: 'default' })
					break
			}
			return actions
		},
		mescrollInit(mescroll) {
			this.mescroll = mescroll
		},
		switchTab(index) {
			this.currentTab = index
			this.where.status = this.statusTabs[index].status
			this.where.page = 1
			this.refundList = []
			if (this.mescroll) {
				this.mescroll.resetUpScroll()
			}
		},
		goToRefundDetail(refundId) {
			uni.navigateTo({
				url: `/pages/order/refund_view?id=${refundId}`
			})
		},
		handleAction(type, refund) {
			switch(type) {
				case 'cancel':
					this.cancelRefund(refund)
					break
				case 'logistics':
					this.fillLogistics(refund)
					break
				case 'reapply':
					this.reapplyRefund(refund)
					break
				case 'review':
					this.reviewRefund(refund)
					break
			}
		},
		cancelRefund(refund) {
			uni.showModal({
				title: '确认取消',
				content: '确定要取消这个售后申请吗？',
				success: (res) => {
					if (res.confirm) {
						_this.ajax(_this.config.refund.cancel, {id: refund.id}).then(res => {
							uni.showToast({
								title: res.msg || '取消成功'
							})
							_this.load()
						})
					}
				}
			})
		},
		fillLogistics(refund) {
			uni.navigateTo({
				url: `/pages/order/logistics?refundId=${refund.id}`
			})
		},
		reapplyRefund(refund) {
			uni.navigateTo({
				url: `/pages/order/apply?orderId=${refund.order_id}`
			})
		},
		reviewRefund(refund) {
			uni.showToast({
				title: '评价功能',
				icon: 'none'
			})
		}
	}
}
</script>

<style>
.refund-list {
	background-color: #f5f5f5;
	min-height: 100vh;
}

/* 状态筛选标签 */
.status-tabs {
	display: flex;
	background-color: #fff;
	border-bottom: 1px solid #f0f0f0;
	position: sticky;
	top: 0;
	z-index: 10;
}

.tab-item {
	flex: 1;
	text-align: center;
	padding: 30rpx 0;
	font-size: 28rpx;
	color: #666;
	position: relative;
}

.tab-item.active {
	color: #007aff;
	font-weight: bold;
}

.tab-item.active::after {
	content: '';
	position: absolute;
	bottom: 0;
	left: 50%;
	transform: translateX(-50%);
	width: 60rpx;
	height: 4rpx;
	background-color: #007aff;
	border-radius: 2rpx;
}

/* 售后容器 */
.refund-container {
	padding: 20rpx;
}

.refund-item {
	background-color: #fff;
	border-radius: 16rpx;
	padding: 30rpx;
	margin-bottom: 20rpx;
	box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.1);
}

/* 售后头部 */
.refund-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
	padding-bottom: 20rpx;
	border-bottom: 1px solid #f0f0f0;
}

.refund-no {
	font-size: 28rpx;
	color: #333;
	font-weight: 500;
}

/* 关联订单信息 */
.order-info {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
	padding: 20rpx;
	background-color: #f8f9fa;
	border-radius: 8rpx;
}

.order-no {
	font-size: 26rpx;
	color: #666;
}

.refund-type {
	font-size: 26rpx;
	color: #007aff;
	font-weight: 500;
}

/* 商品信息 */
.product-list {
	margin-bottom: 20rpx;
}

.product-item {
	display: flex;
	align-items: center;
	padding: 20rpx 0;
	border-bottom: 1px solid #f0f0f0;
}

.product-item:last-child {
	border-bottom: none;
}

.product-image {
	width: 120rpx;
	height: 120rpx;
	border-radius: 8rpx;
	margin-right: 20rpx;
}

.product-info {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 8rpx;
}

.product-name {
	font-size: 28rpx;
	color: #333;
	font-weight: 500;
	line-height: 1.4;
}

.product-spec {
	font-size: 24rpx;
	color: #999;
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

/* 售后原因 */
.refund-reason {
	display: flex;
	margin-bottom: 20rpx;
	padding: 20rpx;
	background-color: #f8f9fa;
	border-radius: 8rpx;
}

.reason-label {
	font-size: 26rpx;
	color: #666;
	margin-right: 10rpx;
	flex-shrink: 0;
}

.reason-text {
	font-size: 26rpx;
	color: #333;
	flex: 1;
}

/* 售后底部 */
.refund-footer {
	display: flex;
	justify-content: space-between;
	align-items: flex-end;
	padding-top: 20rpx;
	border-top: 1px solid #f0f0f0;
}

.refund-info {
	display: flex;
	flex-direction: column;
	gap: 8rpx;
}

.apply-time {
	font-size: 24rpx;
	color: #999;
}

.refund-amount {
	font-size: 28rpx;
	color: #ff6b35;
	font-weight: bold;
}

.action-buttons {
	display: flex;
	gap: 20rpx;
}

.action-buttons button {
	padding: 16rpx 32rpx;
	border-radius: 40rpx;
	font-size: 24rpx;
	border: 1px solid transparent;
	line-height: 1;
	box-sizing: border-box;
}

/* 状态样式 */
.status-pending {
	font-size: 26rpx;
	color: #ff9500;
	font-weight: bold;
}

.status-approved {
	font-size: 26rpx;
	color: #007aff;
	font-weight: bold;
}

.status-rejected {
	font-size: 26rpx;
	color: #ff4d4f;
	font-weight: bold;
}

.status-completed {
	font-size: 26rpx;
	color: #52c41a;
	font-weight: bold;
}

.status-cancelled {
	font-size: 26rpx;
	color: #999;
	font-weight: bold;
}

.empty-state {
	text-align: center;
	padding: 100rpx 0;
}

.empty-text {
	font-size: 28rpx;
	color: #999;
}
</style>
