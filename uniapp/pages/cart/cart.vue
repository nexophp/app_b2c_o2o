<template>
	<view class="cart-container">
		<!-- 加载中 -->
		<view v-if="loading" class="loading">
			<text>加载中...</text>
		</view>

		<!-- 购物车为空 -->
		<view v-else-if="cartList.length == 0 && errList.length == 0" class="empty-cart">
			<image class="empty-image" src="/static/404.png" mode="aspectFit"></image>
			<text class="empty-text">购物车空空如也</text>
			<view class="go-shopping" @click="goShopping">去逛逛</view>
		</view>

		<!-- 购物车商品列表 -->
		<view v-else class="cart-content">
			<uni-swipe-action v-for="(item, index) in cartList" :key="index" :ref="'swipeAction' + index">
				<uni-swipe-action-item :right-options="swipeOptions" @click="swipeClick($event, index)">
					<view class="cart-item">
						<view class="item-checkbox" @click="toggleSelect(index)">
							<text class="checkbox" :class="{ checked: item.selected == 1 }">{{ item.selected ? '✓' : '○'
								}}</text>
						</view>
						<image class="item-image" :src="item.image" mode="aspectFill"></image>
						<view class="item-info">
							<text class="item-name">{{ item.title }}</text>
							<text class="item-spec">{{ item.spec || '' }} {{ item.attr || '' }}</text>
							<view class="item-bottom">
								<text class="item-price">¥{{ item.price }}</text>
								<view class="quantity-controls">
									<view class="quantity-btn" @click="decreaseQuantity(item)">-</view>
									<text class="quantity-text">{{ item.num }}</text>
									<view class="quantity-btn" @click="increaseQuantity(item)">+</view>
								</view>
							</view>
						</view>
					</view>
				</uni-swipe-action-item>
			</uni-swipe-action>

			<uni-swipe-action v-for="(item, index) in errList" :key="index" :ref="'swipeAction' + index">
				<uni-swipe-action-item :right-options="swipeOptions" @click="swipeClick($event, index)">
					<view class="cart-item unavailable-item">
						<view class="item-checkbox">
							<text class="checkbox" :class="{ checked: item.selected }">{{ item.selected ? '✓' : '○'
								}}</text>
						</view>
						<image class="item-image unavailable-image" :src="item.image" mode="aspectFill"></image>
						<view class="item-info">
							<text class="item-name unavailable-text">{{ item.title }}</text>
							<text class="item-spec unavailable-text">{{ item.spec || '' }} {{ item.attr || '' }}</text>
							<text class="unavailable-label">商品已下架</text>
							<view class="item-bottom">
								<text class="item-price unavailable-text">¥{{ item.price }}</text>
								<view class="quantity-controls">
									<view class="quantity-btn unavailable-btn">-</view>
									<text class="quantity-text unavailable-text">{{ item.num }}</text>
									<view class="quantity-btn unavailable-btn">+</view>
								</view>
							</view>
						</view>
					</view>
				</uni-swipe-action-item>
			</uni-swipe-action>

		</view>

		<!-- 底部结算栏 -->
		<view v-if="total_amount > 0" class="bottom-bar">
			<view class="select-all" @click="toggleSelectAll">
				<text class="checkbox" :class="{ checked: selected == 1 || selected === true }">{{ (selected == 1 ||
					selected === true) ? '✓' : '○' }}</text>
				<text class="select-text">全选</text>
			</view>
			<view class="total-info">
				<text class="total-text">合计：¥{{ total_amount }}</text>
				<view class="checkout-btn" :class="{ disabled: total_num == 0 }" @click="checkout">
					结算({{ total_num }})
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
			cartList: [],
			errList: [],
			loading: false,
			total_amount: 0,
			total_num: 0,
			selected: 0,
			swipeOptions: [{
				text: '删除',
				style: {
					backgroundColor: '#ff4757',
					color: '#fff',
					height: '160rpx',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					fontSize: '28rpx',
					fontWeight: 'bold'
				}
			}]
		}
	},
	computed: {

	},
	onLoad() {
		_this = this
		this.loadCartList()
	},
	onShow() {
		// 每次显示页面时刷新购物车数据
		this.loadCartList()
	},
	methods: {
		// 滑动删除点击事件
		swipeClick(e, index) {
			if (e.content.text === '删除') {
				this.deleteItem(index)
			}
		},
		// 收起滑动
		closeSwipe(index) {
			const swipeActionRef = this.$refs['swipeAction' + index]
			if (swipeActionRef && swipeActionRef[0]) {
				swipeActionRef[0].closeAll()
			}
		},
		// 加载购物车列表
		loadCartList(showLoading = true) {
			if (showLoading) {
				this.loading = true
			}
			_this.ajax(_this.config.cart.index, {}).then(res => {
				if (showLoading) {
					this.loading = false
				}
				if (res.code == 0) {
					_this.cartList = res.data;
					_this.errList = res.error;
					// 计算总价和选中数量
					_this.total_amount = res.total_amount
					_this.total_num = res.total_num
					_this.selected = res.selected
					// 更新tabBar徽标
					_this.updateTabBarBadge()
				}
			})
		},
		toggleSelect(index) {
			const item = this.cartList[index]
			let newSelected = 0
			if(item.selected == 1){
				newSelected = 0
			}else{
				newSelected = 1
			} 
			this.updateSelected(item.id, newSelected, index)
		},
		toggleSelectAll() {
			// 检查是否所有商品都已选中
			const allSelected = _this.selected == 1 || _this.selected === true
			const newSelected = !allSelected
			_this.selectAll(newSelected)
		},
		// 更新单个商品选中状态
		updateSelected(cartId, selected, index) {
			const params = {
				id: cartId,
				selected: selected ? 1 : 0
			}
			_this.ajax(_this.config.cart.selected, params).then(res => {
				if (res.code == 0) {
					_this.loadCartList(false)
				} else {
					uni.showToast({
						title: res.msg || '操作失败',
						icon: 'none'
					})
				}
			})
		},
		// 全选/取消全选
		selectAll(selected) {
			const params = {
				selected: selected ? 1 : 0
			}
			_this.ajax(_this.config.cart.select_all, params).then(res => {
				if (res.code == 0) {
					_this.loadCartList(false)
				} else {
					uni.showToast({
						title: res.msg || '操作失败',
						icon: 'none'
					})
				}
			})
		},
		decreaseQuantity(item) {
			if (item.num > 1) {
				_this.updateQuantity(item.id, item.num - 1)
			}
		},
		increaseQuantity(item) {
			this.updateQuantity(item.id, parseInt(item.num) + 1)
		},
		// 更新购物车商品数量
		updateQuantity(cartId, quantity) {
			const params = {
				id: cartId,
				num: quantity
			}
			_this.ajax(_this.config.cart.update, params).then(res => {
				if (res.code == 0) {
					_this.loadCartList(false)
				} else {
					uni.showToast({
						title: res.msg || '更新失败',
						icon: 'none'
					})
				}
			})
		},
		deleteItem(index) {
			uni.showModal({
				title: '提示',
				content: '确定要删除这个商品吗？',
				success: (res) => {
					// 无论确认还是取消都收起滑动
					this.closeSwipe(index)
					if (res.confirm) {
						const item = this.cartList[index]
						this.removeFromCart(item.id, index)
					}
				}
			})
		},
		// 从购物车删除商品
		removeFromCart(id, index) {
			const params = {
				id: id
			}
			_this.ajax(_this.config.cart.delete, params).then(res => {
				if (res.code == 0) {
					_this.loadCartList(false)
					uni.showToast({
						title: '删除成功',
						icon: 'success'
					})
				} else {
					uni.showToast({
						title: res.msg || '删除失败',
						icon: 'none'
					})
				}
			})
		},
		checkout() {
			if (this.total_num === 0) {
				uni.showToast({
					title: '请选择商品',
					icon: 'none'
				});
				return;
			}

			// 获取选中的商品数据
			const selectedItems = this.cartList.filter(item => item.selected).map(item => ({
				id: item.product_id,
				product_id: item.product_id,
				name: item.title,
				title: item.title,
				price: item.price,
				quantity: item.num,
				num: item.num,
				image: item.image,
				spec: item.spec,
				attr: item.attr,

			}));

			// 跳转到确认订单页面，传递选中的商品数据
			uni.navigateTo({
				url: '/pages/order/confirm?type=cart'
			});
		},
		goShopping() {
			uni.switchTab({
				url: '/pages/index/index'
			})
		},
		// 更新tabBar徽标
		updateTabBarBadge() {
			const totalCount = this.cartList.reduce((sum, item) => sum + item.quantity, 0)
			if (totalCount > 0) {
				uni.setTabBarBadge({
					index: 2, // 购物车tab的索引
					text: totalCount.toString()
				})
			} else {
				uni.removeTabBarBadge({
					index: 2
				})
			}
			// 更新全局数据
			getApp().globalData.cartCount = totalCount
		}
	}
}
</script>

<style>
.cart-container {
	background: #f5f5f5;
	padding-bottom: 120rpx;
}

.loading {
	display: flex;
	align-items: center;
	justify-content: center;
	height: 80vh;
	font-size: 32rpx;
	color: #999;
}

.empty-cart {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 80vh;
}

.empty-image {
	width: 200rpx;
	height: 200rpx;
	margin-bottom: 40rpx;
}

.empty-text {
	font-size: 32rpx;
	color: #999;
	margin-bottom: 40rpx;
}

.go-shopping {
	padding: 20rpx 40rpx;
	background: #ff6b35;
	color: #fff;
	border-radius: 50rpx;
	font-size: 28rpx;
}

.cart-content {
	padding: 0rpx;
}

.cart-item {
	background: #fff;
	padding: 20rpx;
	display: flex;
	align-items: center;
}

.item-checkbox {
	margin-right: 20rpx;
}

.checkbox {
	width: 40rpx;
	height: 40rpx;
	border: 3rpx solid #ddd;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24rpx;
	color: transparent;
	background: #fff;
	transition: all 0.3s ease;
}

.checkbox.checked {
	border-color: #ff6b35;
	background: #ff6b35;
	color: #fff;
	transform: scale(1.1);
}

.item-image {
	width: 120rpx;
	height: 120rpx;
	border-radius: 10rpx;
	margin-right: 20rpx;
}

.item-info {
	flex: 1;
	display: flex;
	flex-direction: column;
}

.item-name {
	font-size: 28rpx;
	color: #333;
	margin-bottom: 10rpx;
}

.item-spec {
	font-size: 24rpx;
	color: #999;
	margin-bottom: 20rpx;
}

.item-bottom {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.item-price {
	font-size: 32rpx;
	color: #ff6b35;
	font-weight: bold;
}

.quantity-controls {
	display: flex;
	align-items: center;
	border-radius: 25rpx;
	overflow: hidden;
	border: 2rpx solid #f0f0f0;
}

.quantity-btn {
	width: 50rpx;
	height: 50rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28rpx;
	color: #666;
	background: #f8f8f8;
	transition: all 0.3s ease;
	font-weight: bold;
}

.quantity-btn:active {
	background: #e8e8e8;
	transform: scale(0.95);
}

.quantity-text {
	width: 60rpx;
	height: 50rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 26rpx;
	color: #333;
	background: #fff;
	font-weight: 500;
}



.bottom-bar {
	position: fixed;
	z-index: 9999;
	bottom: 0;
	/* #ifdef H5 */
	bottom: 100rpx;
	/* #endif */
	left: 0;
	right: 0;
	height: 120rpx;
	background: #fff;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0 30rpx;
	border-top: 1rpx solid #f0f0f0;
	box-shadow: 0 -4rpx 20rpx rgba(0, 0, 0, 0.1);
}

.select-all {
	display: flex;
	align-items: center;
	padding: 10rpx;
	border-radius: 10rpx;
	transition: background 0.3s ease;
}

.select-all:active {
	background: #f5f5f5;
}

.select-text {
	margin-left: 15rpx;
	font-size: 30rpx;
	color: #333;
	font-weight: 500;
}

.total-info {
	display: flex;
	align-items: center;
}

.total-text {
	font-size: 30rpx;
	color: #ff6b35;
	font-weight: bold;
	margin-right: 30rpx;
}

.checkout-btn {
	padding: 25rpx 50rpx;
	background: linear-gradient(135deg, #ff6b35, #ff8c42);
	color: #fff;
	border-radius: 50rpx;
	font-size: 30rpx;
	font-weight: bold;
	box-shadow: 0 6rpx 20rpx rgba(255, 107, 53, 0.4);
	transition: all 0.3s ease;
	min-width: 160rpx;
	text-align: center;
}

.checkout-btn:active {
	transform: translateY(2rpx);
	box-shadow: 0 4rpx 15rpx rgba(255, 107, 53, 0.3);
}

.checkout-btn.disabled {
	background: #ccc;
	box-shadow: none;
	transform: none;
}

/* 下架商品样式 */
.unavailable-item {
	background-color: #f8f8f8;
	opacity: 0.85;
}

.unavailable-image {
	opacity: 0.6;
}

.unavailable-text {
	color: #999;
}

.unavailable-btn {
	background-color: #e0e0e0;
	color: #999;
}

.unavailable-label {
	display: inline-block; 
	color: #999;
	font-size: 22rpx;
	padding: 4rpx 10rpx;
	border-radius: 6rpx;
	margin-top: 6rpx;
	margin-bottom: 6rpx; 
}
</style>
