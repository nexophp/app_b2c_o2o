<template>
	<view class="detail-page">
		<!-- 筛选栏 -->
		<view class="filter-bar">
			<view class="filter-tabs">
				<text class="filter-tab" :class="{ active: currentFilter === tab.value }" v-for="tab in filterTabs"
					:key="tab.value" @click="switchFilter(tab.value)">
					{{ tab.label }}
				</text>
			</view>
		</view>

		<!-- 记录列表 -->
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">
			<view class="record-list">
				<view class="record-item" v-for="(item, index) in list" :key="index">
					<view class="record-left">
						<view class="record-icon" v-if="item.type_icon">
							<image :src="item.type_icon" mode="aspectFit" class="icon-image" />
						</view>
						<view class="record-info">
							<text class="record-title" @click="cp(item.order_num)">{{ item.order_num }}</text>
							<text class="record-desc">{{ '金额：¥' + item.amount }}

								<text class="record-desc" style="margin-left: 20px;" v-if="item.flag == 'in'">
									分成比例：{{ item.rate_txt }}
								</text>


								<text class="record-desc" style="margin-left: 20px;" v-if="item.flag == 'out'">
									手续费：¥{{ item.rate_amount }}
								</text>
							</text>
							<text class="record-time">{{ item.created_at_format }}</text>
						</view>
					</view>
					<view class="record-right">
						<text class="record-amount wait" v-if="item.flag == 'out'">
							¥{{ item.real_amount }}
						</text>
						<text class="record-amount success" v-else>
							¥{{ item.amount }}
						</text>
						<text class="record-status" :class="item.status">{{ item.status_text }}</text>
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
			currentFilter: 'all',
			filterTabs: [
				{ label: '全部', value: 'all' },
				{ label: '收入', value: 'income' },
				{ label: '提现', value: 'expense' }
			],
			where: {
				page: 1,
				page_size: 10,
				type: 'all'
			},
			upOption: {
				page: {
					size: 10 // 每页数据的数量,默认10
				},
				noMoreSize: 5, // 配置列表的总数量要大于等于5条才显示'-- END --'的提示
				empty: {
					tip: '暂无记录数据'
				},
				textNoMore: '-- 到底了 --'
			},
			downOption: {
				auto: false, //是否在初始化后,自动执行downCallback; 默认true 
			},
			list: []
		}
	},
	onLoad(options) {
		_this = this
		// 获取页面参数
		if (options.type) {
			this.currentFilter = options.type
			this.where.type = options.type
			this.where.page = 1
			this.load()
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
			// 根据筛选类型选择接口
			let apiUrl = _this.config.wallet.out // 默认提现接口
			if (this.currentFilter === 'income') {
				apiUrl = _this.config.wallet.in // 收入接口
			} else if (this.currentFilter === 'expense') {
				apiUrl = _this.config.wallet.out // 支出接口
			} else if (this.currentFilter === 'all') {
				// 全部时调用综合接口或收入接口，通过flag字段过滤
				apiUrl = _this.config.wallet.in_out
			}

			this.ajax(apiUrl, w).then(res => {
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
		// 切换筛选
		switchFilter(value) {
			this.currentFilter = value
			this.where.type = value
			this.where.page = 1
			this.load()
		},
	}
}
</script>

<style scoped>
.detail-page {
	background: #f5f5f5;
}

.filter-bar {
	background: white;
	padding: 0;
	border-bottom: 1px solid #f0f0f0;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filter-tabs {
	display: flex;
	height: 50px;
}

.filter-tab {
	flex: 1;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 15px;
	color: #666;
	position: relative;
	transition: all 0.3s ease;
	background: white;
}

.filter-tab:active {
	background: #f8f9fa;
	transform: scale(0.98);
}

.filter-tab.active {
	color: #4a6cf7;
	font-weight: 600;
	background: linear-gradient(135deg, rgba(74, 108, 247, 0.05) 0%, rgba(37, 65, 178, 0.05) 100%);
}

.filter-tab.active::after {
	content: '';
	position: absolute;
	bottom: 0;
	left: 20%;
	right: 20%;
	height: 3px;
	background: linear-gradient(135deg, #4a6cf7 0%, #2541b2 100%);
	border-radius: 2px 2px 0 0;
	box-shadow: 0 -2px 8px rgba(74, 108, 247, 0.3);
}

.record-list {
	padding: 15px;
}

.record-item {
	background: white;
	border-radius: 10px;
	padding: 15px;
	margin-bottom: 10px;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.record-left {
	display: flex;
	align-items: center;
	flex: 1;
}

.record-icon {
	width: 40px;
	height: 40px;
	border-radius: 20px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-right: 12px;
	font-size: 18px;
}

.record-icon.income {
	background: #e8f5e8;
}

.record-icon.expense {
	background: #fef0f0;
}

.record-info {
	display: flex;
	flex-direction: column;
	flex: 1;
}

.record-title {
	font-size: 14px;
	color: #333;
	margin-bottom: 4px;
	font-weight: 500;
}

.record-desc {
	font-size: 12px;
	color: #999;
	margin-bottom: 4px;
}

.record-time {
	font-size: 12px;
	color: #ccc;
}

.record-right {
	display: flex;
	flex-direction: column;
	align-items: flex-end;
}

.record-amount {
	font-size: 16px;
	font-weight: bold;
	margin-bottom: 4px;
}

.record-amount.wait {
	color: #ff6b35;
}

.record-amount.success {
	color: #52c41a;

}

.record-status {
	font-size: 12px;
	padding: 2px 8px;
	border-radius: 10px;
}

.record-status.success {
	background: #e8f5e8;
	color: #52c41a;
}

.record-status.wait {
	background: #fff7e6;
	color: #fa8c16;
}

.record-status.fail {
	background: #fff2f0;
	color: #ff4d4f;
}

.empty-state {
	text-align: center;
	padding: 60px 20px;
}

.empty-image {
	width: 120px;
	height: 120px;
	margin-bottom: 20px;
}

.empty-text {
	font-size: 14px;
	color: #999;
}

.load-more {
	text-align: center;
	padding: 20px;
}

.load-text {
	font-size: 14px;
	color: #999;
}
</style>
