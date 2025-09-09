<template>
	<view class="category-page">
		<!-- 左侧分类列表 -->
		<view class="category-sidebar">
			<scroll-view scroll-y class="category-list">
				<view 
					v-for="(category, index) in categoryList" 
					:key="category.id"
					:class="['category-item', currentCategoryIndex === index ? 'active' : '']"
					@click="selectCategory(index)"
				>
					<text class="category-name">{{ category.name }}</text>
				</view>
			</scroll-view>
		</view>
		
		<!-- 右侧商品列表 -->
		<view class="product-content">
			<scroll-view scroll-y class="product-scroll" :scroll-top="scrollTop" @scroll="onScroll">
				<!-- 循环显示所有分类及其商品 -->
				<view v-for="(category, categoryIndex) in categoryList" :key="category.id" :id="'category-' + categoryIndex" class="category-section">
					<!-- 分类标题 -->
					<view class="category-header">
						<text class="category-title">{{ category.name }}</text> 
					</view>
					
					<!-- 商品网格 -->
					<view class="product-grid">
						<view 
							v-for="product in category.products" 
							:key="product.id"
							class="product-item"
							@click="goToProduct(product)"
						>
							<image :src="product.image" class="product-image"></image>
							<view class="product-info">
								<text class="product-name">{{ product.title }}</text>
								<text class="product-price">¥{{ product.price }}</text>
							</view>
						</view>
					</view>
					
					<!-- 空状态 -->
					<view v-if="category.products && category.products.length === 0" class="empty-state">
						<text class="empty-text">该分类暂无商品</text>
					</view>
				</view>
			</scroll-view>
		</view>
	</view>
</template>

<script>
var _this
export default {
	data() {
			return {
				currentCategoryIndex: 0,
				categoryList: [],
				loading: false,
				lastScrollTop: 0,
				scrollTop: 0,
				scrollTimer: null
			}
		},
	onLoad() {
		_this = this
		this.loadCategories()
	},
	
	computed: {
		currentCategory() {
			return this.categoryList[this.currentCategoryIndex] || {}
		}
	},
	onShow() {
		_this.loadCategories()
	},
	methods: {
		// 加载分类数据
		loadCategories() { 
			
			this.loading = true
			this.ajax(this.config.product_type.index, {}).then(res => {
				this.loading = false
				
				if (res.data && res.data.length > 0) {
					// 处理API返回的数据结构
					this.categoryList = res.data.map(item => ({
						id: item.id,
						name: item.title,
						desc: item.title,
						image: item.image,
						products: item.products.map(product => ({
							id: product.id,
							name: product.title,
							title: product.title,
							desc: product.desc,
							price: product.price,
							image: product.image,
							sku: product.sku,
							stock: product.stock
						}))
					}))
					
					// 如果有分类数据，默认选中第一个
					if (this.categoryList.length > 0) {
						this.currentCategoryIndex = 0
					}
				} else {
					this.toast('暂无分类数据')
				}
			}).catch(error => {
				this.loading = false
				console.error('加载分类数据失败:', error)
				this.toast('网络错误，请重试')
			})
		},
		
		selectCategory(index) {
			this.currentCategoryIndex = index
			// 滚动到对应的分类区域
			this.scrollToCategorySection(index)
		},
		
		// 滚动事件监听
		onScroll(e) {
			const scrollTop = e.detail.scrollTop
			// 根据滚动位置自动选中对应的分类
			this.updateCurrentCategoryByScroll(scrollTop)
			this.lastScrollTop = scrollTop
		},
		
		// 滚动到指定分类区域
		scrollToCategorySection(categoryIndex) {
			// 使用uni.createSelectorQuery获取分类区域的位置
			this.$nextTick(() => {
				const query = uni.createSelectorQuery().in(this)
				// 同时获取scroll-view和目标元素的位置
				query.select('.product-scroll').boundingClientRect()
				query.select(`#category-${categoryIndex}`).boundingClientRect()
				query.exec((res) => {
					const scrollViewRect = res[0]
					const targetRect = res[1]
					if (scrollViewRect && targetRect) {
						// 计算目标元素相对于scroll-view的位置
						const scrollPosition = targetRect.top - scrollViewRect.top + this.lastScrollTop
						// 设置滚动位置，减去一些偏移量确保标题可见
						this.scrollTop = Math.max(0, scrollPosition - 50)
					}
				})
			})
		},
		
		// 根据滚动位置更新当前选中的分类
		updateCurrentCategoryByScroll(scrollTop) {
			// 使用节流，避免频繁查询
			if (this.scrollTimer) {
				clearTimeout(this.scrollTimer)
			}
			this.scrollTimer = setTimeout(() => {
				// 一次性查询所有分类区域的位置
				const query = uni.createSelectorQuery().in(this)
				for (let i = 0; i < this.categoryList.length; i++) {
					query.select(`#category-${i}`).boundingClientRect()
				}
				query.exec((res) => {
					// 找到当前可视区域内的分类
					for (let i = 0; i < res.length; i++) {
						const data = res[i]
						if (data && data.top <= 150 && data.bottom > 150) {
							if (this.currentCategoryIndex !== i) {
								this.currentCategoryIndex = i
							}
							break
						}
					}
				})
			}, 16) // 16ms节流，约60fps，提供更流畅的响应
		},
		
		goToProduct(item) {
			uni.navigateTo({
				url: '/pages/product/view?id=' + item.id
			})
		}
	}
}
</script>

<style>
	.category-page {
		display: flex;
		height: 100vh;
		background-color: #f5f5f5;
	}
	
	/* 左侧分类列表 */
	.category-sidebar {
		width: 200rpx;
		background-color: #f8f8f8;
		border-right: 1px solid #e0e0e0;
	}
	
	.category-list {
		height: 100%;
	}
	
	.category-item {
		padding: 40rpx 20rpx;
		text-align: center;
		border-bottom: 1px solid #f0f0f0;
		position: relative;
	}
	
	.category-item.active {
		background-color: #fff;
		color: #007aff;
	}
	
	.category-item.active::before {
		content: '';
		position: absolute;
		left: 0;
		top: 0;
		bottom: 0;
		width: 6rpx;
		background-color: #007aff;
	}
	
	.category-name {
		font-size: 26rpx;
		color: #333;
		line-height: 1.2;
		width: 160rpx;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		display: inline-block;
	}
	
	.category-item.active .category-name {
		color: #007aff;
		font-weight: bold;
	}
	
	/* 右侧商品内容 */
	.product-content {
		flex: 1;
		background-color: #f8f8f8;
		overflow: hidden;
		width: 0; /* 确保flex子项不会超出容器 */
	}
	
	.category-section {
		margin-bottom: 20rpx;
	}
	
	.product-scroll {
		height: 100%;
	}
	
	.category-header {
		padding: 30rpx;
		border-bottom: 1px solid #f0f0f0;
		margin-bottom: 20rpx;
	}
	
	.category-title {
		font-size: 32rpx;
		color: #333;
		font-weight: bold;
		margin-bottom: 10rpx;
		display: block;
	}
	
	.category-desc {
		font-size: 24rpx;
		color: #999;
		display: block;
	}
	
	/* 商品网格 */
	.product-grid {
		display: flex;
		flex-wrap: wrap;
		padding: 0 20rpx;
		gap: 20rpx;
		width: 100%;
		box-sizing: border-box;
	}
	
	.product-item {
		width: calc(50% - 10rpx);
		background-color: #fff;
		border-radius: 12rpx;
		overflow: hidden;
		box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.1);
		margin-bottom: 20rpx;
		box-sizing: border-box;
		flex-shrink: 0;
	}
	
	.product-image {
		width: 100%;
		height: 300rpx;
		object-fit: cover;
	}
	
	.product-info {
		padding: 20rpx;
	}
	
	.product-name {
		font-size: 26rpx;
		color: #333;
		margin-bottom: 15rpx;
		display: block;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	
	.product-price {
		font-size: 28rpx;
		color: #ff6b35;
		font-weight: bold;
		margin-right: 15rpx;
	}
	
	.original-price {
		font-size: 22rpx;
		color: #999;
		text-decoration: line-through;
	}
	
	/* 空状态 */
	.empty-state {
		text-align: center;
		padding: 100rpx 0;
	}
	
	.empty-text {
		font-size: 28rpx;
		color: #999;
	}
</style>
