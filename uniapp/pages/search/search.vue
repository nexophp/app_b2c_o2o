<template>
	<view class="search-container">
		<!-- 搜索框 -->
		<view class="search-header">
			<view class="search-box">
				<uni-icons type="search" size="18" color="#999"></uni-icons>
				<input v-model="keyword" placeholder="搜索商品" @confirm="handleSearch" @input="handleInput"
					confirm-type="search" focus />
				<text v-if="keyword" @click="clearKeyword" class="clear-btn">×</text>
			</view>
			<text class="cancel-btn" @click="goBack">取消</text>
		</view>

		<!-- 内容区域 -->
		<view class="search-content">
			<!-- 搜索建议 -->
			<view class="suggestion-list" v-if="keyword && suggestions.length > 0 && !showResult">
				<view v-for="(item, index) in suggestions" :key="index" class="suggestion-item"
					@click="searchBySuggestion(item)">
					<text>{{ item }}</text>
					<uni-icons type="arrowright" size="14" color="#ccc"></uni-icons>
				</view>
			</view>

			<!-- 商品列表 -->
			<mescroll-body v-if="showResult" ref="mescrollRef" @init="mescrollInit" @down="downCallback"
				@up="upCallback" :up="upOption" :down="downOption" top="0">
				<view class="product-list">
					<view v-for="(item, index) in list" :key="index" class="product-item" @click="navToDetail(item.id)">
						<image :src="item.image" mode="aspectFill" class="product-image"></image>
						<view class="product-info">
							<text class="title">{{ item.title }}</text>
							<view class="price-section">
								<text class="price">¥{{ item.price }}</text>
								<text v-if="item.original_price" class="original-price">¥{{ item.original_price
									}}</text>
							</view>
							<view class="extra-info">
								<text>销量: {{ item.sales || 0 }}</text>
								<!-- <uni-rate :value="item.rating || 0" :size="12" readonly></uni-rate> -->
							</view>
						</view>
					</view>
				</view>
			</mescroll-body>

			<!-- 无结果提示 -->
			<view class="empty-tip" v-if="showResult && list.length === 0">
				<image src="/static/empty.png" mode="aspectFit"></image>
				<text>没有找到相关商品</text>
			</view>

			<!-- 推荐和历史记录 -->
			<template v-if="!keyword && !showResult">
				<view class="history-section" v-if="searchHistory.length > 0">
					<view class="section-header">
						<text>搜索历史</text>
						<uni-icons type="trash" size="18" @click="clearHistory"></uni-icons>
					</view>
					<view class="tag-list">
						<view v-for="(item, index) in searchHistory" :key="index" class="tag-item"
							@click="searchByTag(item)">
							{{ item }}
							<uni-icons type="close" size="12" color="#999"
								@click.stop="deleteHistory(index)"></uni-icons>
						</view>
					</view>
				</view>

				<view class="recommend-section">
					<view class="section-header">
						<text>热门搜索</text>
					</view>
					<view class="tag-list">
						<view v-for="(item, index) in hotKeywords" :key="index" class="tag-item"
							@click="searchByTag(item)">
							{{ item }}
						</view>
					</view>
				</view>
			</template>
		</view>
	</view>
</template>

<script>
import MescrollMixin from "@/uni_modules/mescroll-uni/components/mescroll-uni/mescroll-mixins.js";

export default {
	mixins: [MescrollMixin],
	data() {
		return {
			keyword: '',
			showResult: false,
			hotKeywords: [],
			suggestions: [],
			searchHistory: [],
			debounceTimer: null,

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

			// 商品数据
			list: [],
			where: {
				page: 1,
				page_size: 10,
				keyword: ''
			}
		}
	},
	onLoad() {
		this.loadHistory();
		this.get_config();
	},
	methods: {
		get_config() {
			this.ajax(this.config.config, {}).then(res => {
				if (res.code == 0) { 
					this.hotKeywords = res.data.search_keywords;
				}
			})
		},
		// 执行搜索
		handleSearch() {
			if (!this.keyword.trim()) return;

			this.addToHistory(this.keyword);
			this.showResult = true;
			this.where.title = this.keyword;
			this.mescroll.resetUpScroll();
		},

		// Mescroll回调
		downCallback() {
			this.where.page = 1;
			this.loadProducts();
		},

		upCallback(page) {
			this.where.page = page.num;
			this.loadProducts();
		},

		// 加载商品数据
		loadProducts() {
			uni.showLoading({ title: '加载中...' });
			this.ajax(this.config.product.index, this.where).then(res => {
				uni.hideLoading();

				if (this.where.page === 1) {
					this.list = [];
					this.mescroll.scrollTo(0, 0); // 回到顶部
				}

				this.list = this.list.concat(res.data || []);

				// 结束加载状态
				this.mescroll.endBySize(res.per_page, res.total);
			})
		},

		// 输入处理（带防抖）
		handleInput() {
			clearTimeout(this.debounceTimer);

			if (!this.keyword.trim()) {
				this.suggestions = [];
				this.showResult = false;
				return;
			}

			this.debounceTimer = setTimeout(() => {
				// 模拟搜索建议API
				const mockSuggestions = {
					'手': ['手机', '手机壳', '手表', '手环'],
					'电': ['电脑', '电视', '电饭煲', '电动车'],
					'耳': ['耳机', '耳麦', '耳环', '耳温枪']
				};

				this.suggestions = [];
				for (const key in mockSuggestions) {
					if (this.keyword.includes(key)) {
						this.suggestions = mockSuggestions[key];
						break;
					}
				}
			}, 300);
		},

		// 从推荐标签搜索
		searchByTag(tag) {
			this.keyword = tag;
			this.handleSearch();
		},

		// 从搜索建议搜索
		searchBySuggestion(suggestion) {
			this.keyword = suggestion;
			this.handleSearch();
		},

		// 清空输入
		clearKeyword() {
			this.keyword = '';
			this.suggestions = [];
			this.showResult = false;
		},

		// 返回上一页
		goBack() {
			uni.navigateBack();
		},

		// 加载历史记录
		loadHistory() {
			const history = uni.getStorageSync('searchHistory');
			if (history) {
				this.searchHistory = JSON.parse(history);
			}
		},

		// 添加到历史记录
		addToHistory(keyword) {
			// 去重处理
			const index = this.searchHistory.indexOf(keyword);
			if (index !== -1) {
				this.searchHistory.splice(index, 1);
			}

			// 添加到数组开头
			this.searchHistory.unshift(keyword);

			// 限制数量
			if (this.searchHistory.length > 10) {
				this.searchHistory.pop();
			}

			// 保存到本地
			uni.setStorageSync('searchHistory', JSON.stringify(this.searchHistory));
		},

		// 删除单条历史记录
		deleteHistory(index) {
			this.searchHistory.splice(index, 1);
			uni.setStorageSync('searchHistory', JSON.stringify(this.searchHistory));
		},

		// 清空历史记录
		clearHistory() {
			uni.showModal({
				title: '提示',
				content: '确定要清空搜索历史吗？',
				success: (res) => {
					if (res.confirm) {
						this.searchHistory = [];
						uni.removeStorageSync('searchHistory');
					}
				}
			});
		},

		// 跳转商品详情
		navToDetail(id) {
			uni.navigateTo({
				url: `/pages/product/view?id=${id}`
			});
		}
	}
}
</script>

<style lang="scss" scoped>
.search-container {
	display: flex;
	flex-direction: column;
	height: 100vh;
	background-color: #f8f8f8;
}

.search-header {
	display: flex;
	align-items: center;
	padding: 15rpx 25rpx;
	background-color: #fff;
	border-bottom: 1rpx solid #eee;

	.search-box {
		flex: 1;
		display: flex;
		align-items: center;
		height: 70rpx;
		background-color: #f5f5f5;
		border-radius: 35rpx;
		padding: 0 20rpx;

		input {
			flex: 1;
			height: 100%;
			margin: 0 15rpx;
			font-size: 28rpx;
			background-color: transparent;
		}

		.clear-btn {
			font-size: 40rpx;
			color: #999;
			padding: 5rpx 10rpx;
		}
	}

	.cancel-btn {
		margin-left: 20rpx;
		font-size: 30rpx;
		color: #333;
	}
}

.search-content {
	flex: 1;
	overflow: hidden;

	.suggestion-list {
		background-color: #fff;
		border-radius: 10rpx;
		margin: 20rpx;
		box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

		.suggestion-item {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 25rpx 30rpx;
			border-bottom: 1rpx solid #f5f5f5;

			&:last-child {
				border-bottom: none;
			}
		}
	}

	.section-header {
		display: flex;
		justify-content: space-between;
		padding: 20rpx 30rpx;
		font-size: 28rpx;
		color: #666;
	}

	.tag-list {
		display: flex;
		flex-wrap: wrap;
		padding: 0 20rpx 20rpx;

		.tag-item {
			position: relative;
			padding: 12rpx 25rpx;
			margin: 0 20rpx 20rpx 0;
			background-color: #fff;
			border-radius: 30rpx;
			font-size: 26rpx;
			color: #333;
			display: flex;
			align-items: center;
			box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);

			.uni-icons {
				margin-left: 10rpx;
			}
		}
	}

	.recommend-section {
		.tag-item {
			background-color: #fef0f0;
			color: #f56c6c;
		}
	}

	.product-list {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 20rpx;
		padding: 20rpx;

		.product-item {
			background: #fff;
			border-radius: 12rpx;
			overflow: hidden;
			box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);

			.product-image {
				width: 100%;
				height: 300rpx;
			}

			.product-info {
				padding: 16rpx;

				.title {
					font-size: 28rpx;
					color: #333;
					display: -webkit-box;
					-webkit-line-clamp: 2;
					-webkit-box-orient: vertical;
					overflow: hidden;
					line-height: 1.4;
					min-height: 80rpx;
				}

				.price-section {
					margin-top: 12rpx;
					display: flex;
					align-items: center;

					.price {
						color: #f56c6c;
						font-size: 32rpx;
						font-weight: bold;
					}

					.original-price {
						color: #999;
						font-size: 24rpx;
						text-decoration: line-through;
						margin-left: 8rpx;
					}
				}

				.extra-info {
					margin-top: 12rpx;
					display: flex;
					justify-content: space-between;
					align-items: center;
					font-size: 24rpx;
					color: #999;
				}
			}
		}
	}

	.empty-tip {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 100rpx 0;

		image {
			width: 200rpx;
			height: 200rpx;
			opacity: 0.5;
		}

		text {
			margin-top: 20rpx;
			color: #999;
			font-size: 28rpx;
		}
	}
}
</style>