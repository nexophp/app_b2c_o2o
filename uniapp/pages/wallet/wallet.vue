<template>
	<view class="wallet-page">
		<!-- 头部余额卡片 -->
		<view class="balance-card">
			<view class="balance-info">
				<text class="balance-label">总收益</text>
				<text class="balance-amount" @click="jump('/pages/wallet/view?type=all')">¥{{ wallet.total_amount || '0.00'
					}}</text>
			</view>
			<view class="balance-actions">
				<button class="action-btn withdraw" @click="goToSubmit">提现</button>
				<button class="action-btn detail" @click="goToWithdrawDetail">提现明细</button>
			</view>
		</view>

		<!-- 收益统计 -->
		<view class="stats-section">
			<view class="stats-grid">
				<view class="stats-item">
					<text class="stats-value">¥{{ wallet.can_out_amount || '0.00' }}</text>

					<text class="stats-label">可提现</text>
				</view>
				<view class="stats-item">
					<text class="stats-value">¥{{ wallet.out_amount || '0.00' }}</text>
					<text class="stats-label">已提现</text>
				</view>
				<view class="stats-item">
					<text class="stats-value">¥{{ wallet.wait_out_amount || '0.00' }}</text>
					<text class="stats-label">提现中</text>
				</view>
			</view>
		</view>


		<!-- 最近收益记录 -->
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">
			<view class="recent-section">
				<view class="section-header">
					<text class="section-title">最近收益</text>
					<text class="more-btn" @click="goToDetail">查看全部</text>
				</view>
				<view class="record-list">
					<view class="record-item" v-for="(item, index) in list" :key="index">
						<view class="record-info">
							<text class="record-title">金额：{{ item.order_amount }} 分成比： {{ item.rate_txt }}</text>

							<text class="record-time">订单号：{{ item.order_num }}</text>
						</view>
						<text class="record-amount  income ">
							¥{{ item.amount }}
						</text>
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
					tip: '暂无商品数据'
				},
				textNoMore: '-- 到底了 --'
			},
			downOption: {
				auto: false, //是否在初始化后,自动执行downCallback; 默认true 
			},
			list: [],
			wallet: {},
		}
	},
	onLoad() {
		_this = this
		this.load_stat()
	},
	onShow(){
		this.load_stat()
		this.reload()
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
			this.load_stat()
			this.ajax(_this.config.wallet.in, w).then(res => {
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
		load_stat() {
			this.ajax(this.config.wallet.index, {}).then(res => {
				this.wallet = res.data
			})
		},
		// 跳转到提现页面
		goToSubmit() {
			uni.navigateTo({
				url: '/pages/wallet/submit'
			})
		},
		// 跳转到收益明细
		goToDetail() {
			uni.navigateTo({
				url: '/pages/wallet/view?type=income'
			})
		},
		// 跳转到提现明细
		goToWithdrawDetail() {
			uni.navigateTo({
				url: '/pages/wallet/view?type=expense'
			})
		},
	},
}
</script>
<style scoped>
.wallet-page {
	background: #f5f5f5; 
}

.balance-card {
	background: linear-gradient(135deg, #4a6cf7 0%, #2541b2 100%);
	padding: 30px 20px;
	color: #fff;
	display: flex;
	justify-content: space-between;
	align-items: center;
	box-shadow: 0 4px 12px rgba(74, 108, 247, 0.2);
}

.balance-info {
	display: flex;
	flex-direction: column;
}

.balance-label {
	font-size: 14px;
	color: rgba(255, 255, 255, 0.8);
	margin-bottom: 8px;
}

.balance-amount {
	font-size: 32px;
	font-weight: bold;
	color: #fff;
	text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.balance-actions {
	display: flex;
	gap: 10px;
}

.action-btn {
	padding: 8px 16px;
	border-radius: 20px;
	font-size: 14px;
	border: none;
	font-weight: 500;
	transition: all 0.3s ease;
}

.action-btn.withdraw {
	background: #fff;
	color: #4a6cf7;
}

.action-btn.withdraw:active {
	transform: scale(0.95);
}

.action-btn.detail {
	background: rgba(255, 255, 255, 0.2);
	color: #fff;
	border: 1px solid rgba(255, 255, 255, 0.3);
}

.action-btn.detail:active {
	background: rgba(255, 255, 255, 0.3);
}

.stats-section {
	background: white;
	margin: 15px;
	border-radius: 12px;
	padding: 20px;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.stats-title {
	font-size: 16px;
	font-weight: bold;
	margin-bottom: 15px;
	color: #333;
}

.stats-grid {
	display: flex;
	justify-content: space-between;
}

.stats-item {
	display: flex;
	flex-direction: column;
	align-items: center;
	flex: 1;
}

.stats-value {
	font-size: 18px;
	font-weight: bold;
	margin-bottom: 5px;
}

/* 可提现金额 - 绿色表示可用 */
.stats-item:nth-child(1) .stats-value {
	color: #28a745;
}

/* 待确认金额 - 橙色表示待处理 */
.stats-item:nth-child(2) .stats-value {
	color: #fd7e14;
}

/* 提现中金额 - 蓝色表示处理中 */
.stats-item:nth-child(3) .stats-value {
	color: #17a2b8;
}

.stats-label {
	font-size: 12px;
	color: #666;
}

.recent-section {
	background: white;
	margin: 0 15px 15px;
	border-radius: 12px;
	padding: 20px;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 15px;
}

.section-title {
	font-size: 16px;
	font-weight: bold;
	color: #333;
}

.more-btn {
	font-size: 14px;
	color: #4a6cf7;
}

.record-list {
	display: flex;
	flex-direction: column;
	gap: 15px;
}

.record-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding-bottom: 15px;
	border-bottom: 1px solid #f0f0f0;
}

.record-item:last-child {
	border-bottom: none;
	padding-bottom: 0;
}

.record-info {
	display: flex;
	flex-direction: column;
}

.record-title {
	font-size: 14px;
	color: #333;
	margin-bottom: 5px;
}

.record-time {
	font-size: 12px;
	color: #999;
}

.record-amount {
	font-size: 16px;
	font-weight: bold;
}

.record-amount.income {
	color: #28a745;
}

.record-amount.expense {
	color: #dc3545;
}

.empty-state {
	text-align: center;
	padding: 40px 0;
}

.empty-text {
	font-size: 14px;
	color: #999;
}
</style>