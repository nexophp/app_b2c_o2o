<template>
	<view class="product-detail">
		<!-- 顶部导航栏 -->
		<view class="nav-bar">
			<view class="nav-back" @click="goBack">
				<uni-icons type="left" size="30"></uni-icons>
			</view>
			<view class="nav-title"></view>

		</view>

		<!-- 商品图片 -->
		<view class="product-image-container">
			<image class="product-image" :src="product.image" mode="aspectFill"></image>
		</view>

		<!-- 商品信息 -->
		<view class="product-info">
			<view class="product-title">{{ product.title || '' }}</view>
			<view class="product-price">¥{{ displayPrice || '' }}</view>
			<view class="product-desc">{{ product.desc || '' }}</view>
		</view>

		<!-- 商品详情 -->
		<view class="product-detail-content">
			<view class="detail-title">商品详情</view>
			<mp-html :content="product.body_html" :lazy-load="true" :selectable="true"></mp-html>
		</view>

		<!-- 商品评论 -->
		<t-comment :nid="productId" type="product"></t-comment>

		<!-- 底部操作栏 -->
		<view class="bottom-actions">
			<view class="icon-btn" @click="goHome">
				<uni-icons type="home" size="30"></uni-icons>
				<text class="icon-text">首页</text>
			</view>
			<view class="icon-btn" @click="goToCart">
				<uni-icons type="cart" size="30"></uni-icons>
				<text class="icon-text">购物车</text>
				<view class="cart-badge" v-if="cartCount > 0">{{ cartCount }}</view>
			</view>
			<view class="action-buttons">
				<template v-if="product.status == 'success'">
					<!-- 单规格商品 -->
					<template v-if="product.spec_type == 1">
						<template v-if="product.stock > 0">
							<view class="action-btn cart-btn" @click="addToCart">加入购物车</view>
							<view class="action-btn buy-btn" @click="buyNow">立即购买</view>
						</template>
						<template v-else>
							<view class="action-btn disabled full-width">
								<text>库存不足</text>
							</view>
						</template>
					</template>
					<!-- 多规格商品 -->
					<template v-else>
						<view class="action-btn cart-btn" @click="addToCart">加入购物车</view>
						<view class="action-btn buy-btn" @click="buyNow">立即购买</view>
					</template>
				</template>
				<!-- 商品已下架 -->
				<template v-else>
					<view class="action-btn disabled full-width">
						<text>商品已下架</text>
					</view>
				</template>
			</view>

		</view>

		<!-- 规格选择弹窗 -->
		<view class="spec-modal" v-if="showSpecModal" @click="closeSpecModal">
			<view class="spec-content" @click.stop>
				<view class="spec-header">
					<view class="spec-product-info">
						<image class="spec-product-image" :src="selectedSpec ? selectedSpec.image : product.image"
							mode="aspectFill"></image>
						<view class="spec-product-detail">
							<view class="spec-product-title">{{ product.title }}</view>
							<view class="spec-product-price" v-if="selectedSpec">¥{{ selectedSpec.price }}</view>
							<view class="spec-product-stock" v-if="selectedSpec">库存：{{ selectedSpec.stock }}件</view>
						</view>
					</view>
					<view class="spec-close" @click="closeSpecModal">×</view>
				</view>

				<view class="spec-list">
					<view class="spec-title">选择规格</view>
					<view class="spec-options">
						<view class="spec-option" v-for="(spec, index) in product.spec" :key="index"
							:class="{ active: selectedSpec && selectedSpec.title === spec.title }"
							@click="selectedSpec = spec">
							{{ spec.title }}
						</view>
					</view>
				</view>

				<!-- 属性选择 -->
				<view class="attr-list" v-if="product.new_attr && Object.keys(product.new_attr).length > 0">
					<view class="attr-group" v-for="(options, attrName) in product.new_attr" :key="attrName">
						<view class="attr-title">{{ attrName }}</view>
						<view class="attr-options">
							<view class="attr-option" v-for="(option, index) in options" :key="index"
								:class="{ active: selectedAttrs[attrName] === option }"
								@click="selectAttr(attrName, option)">
								{{ option }}
							</view>
						</view>
					</view>
				</view>

				<view class="quantity-section">
					<view class="quantity-title">购买数量</view>
					<view class="quantity-controls">
						<view class="quantity-btn" @click="decreaseQuantity">-</view>
						<view class="quantity-input">{{ quantity }}</view>
						<view class="quantity-btn" @click="increaseQuantity">+</view>
					</view>
				</view>

				<view class="spec-actions">
					<view v-if="stock_is_ok" class="spec-confirm-btn" :class="{ disabled: !selectedSpec }"
						@click="selectedSpec && onSpecConfirm({ spec: selectedSpec, quantity: quantity })">
						{{ actionType === 'cart' ? '加入购物车' : '立即购买' }}
					</view>
					<view v-else class="spec-confirm-btn disabled">
						库存不足
					</view>

				</view>
			</view>
		</view>
	</view>
</template>

<script>
var _this
import mpHtml from '@/uni_modules/mp-html/components/mp-html/mp-html.vue'
export default {
	components: {
		mpHtml,
	},
	data() {
		return {
			productId: '',
			quantity: 1,
			cartCount: 0,
			product: {
				id: '',
				title: '',
				price: '',
				image: '',
				desc: '',
				body_html: '',
				spec_type: '1',
				spec: [],
				new_attr: {}
			},
			showSpecModal: false,
			selectedSpec: null,
			actionType: '', // 'cart' 或 'buy'
			// 属性选择
			selectedAttrs: {},
			stock_is_ok: false,
			user_info:{},
		}
	},
	computed: {
		displayPrice() {
			// 如果是单规格商品，直接显示商品价格
			if (this.product.spec_type === '1') {
				return this.product.price;
			}
			// 如果是多规格商品且已选择规格，显示选中规格的价格
			if (this.selectedSpec && this.selectedSpec.price) {
				return this.selectedSpec.price;
			}
			// 默认显示商品价格
			return this.product.price;
		}
	},
	watch: {
		selectedSpec: {
			handler(newVal, oldVal) {
				this.checkStock()
			},
			deep: true
		},
		quantity: {
			handler(newVal, oldVal) {
				this.checkStock()
			},
			deep: true
		},
		selectedAttrs: {
			handler(newVal, oldVal) {
				this.checkStock()
			},
			deep: true
		},

	},
	onLoad(options) {
		_this = this
		if (options.id) {
			this.productId = options.id;
			this.loadProductDetail();
			this.loadCartCount();
		}
	},
	onShow() {
		this.get_user_info()
	}, 
	methods: {
		checkStock() {
			this.ajax(_this.config.product.stock, {
				product_id: this.productId,
				spec: this.selectedSpec.title,
				num: this.quantity,
			}).then(res => {
				if (res.code == 0) {
					this.stock_is_ok = true
				} else {
					this.stock_is_ok = false
				}
			})

		},
		loadProductDetail() {
			// 调用product.view接口获取商品详情
			_this.ajax(_this.config.product.view, { id: this.productId }).then(res => {
				if (res.code == 0) {
					_this.product = res.data;
					// 如果没有属性数据，添加测试数据
					if (!_this.product.new_attr || Object.keys(_this.product.new_attr).length === 0) {
						_this.product.new_attr = {

						};
					}
					// 初始化属性选择，默认选择第一个
					this.initDefaultAttrs();
				} else {
					uni.showToast({
						title: res.msg || '获取商品详情失败',
						icon: 'none'
					});
				}
			}).catch(err => {
				console.error('获取商品详情失败:', err);
				uni.showToast({
					title: '网络错误',
					icon: 'none'
				});
			});
		},
		decreaseQuantity() {
			if (this.quantity > 1) {
				this.quantity--;
			}
		},
		increaseQuantity() {
			this.quantity++;
		},

		addToCart() {
			if(!this.user_info.id){
				this.jump('/pages/user/user')
				return 
			}
			if (this.product.spec_type === '2' && this.product.spec && this.product.spec.length > 0) {
				this.actionType = 'cart';
				this.setDefaultSpec();
				this.showSpecModal = true;
			} else {
				// 单规格商品直接加入购物车
				this.doAddToCart();
			}
		},
		buyNow() {
			if(!this.user_info.id){
				this.jump('/pages/user/user')
				return 
			}
			if (this.product.spec_type === '2' && this.product.spec && this.product.spec.length > 0) {
				this.actionType = 'buy';
				this.setDefaultSpec();
				this.showSpecModal = true;
			} else {
				// 单规格商品直接购买
				this.doBuyNow();
			}
		},
		setDefaultSpec() {
			// 查找默认规格
			const defaultSpec = this.product.spec.find(spec => spec.is_default == "1" || spec.is_default === true);
			if (defaultSpec) {
				this.selectedSpec = defaultSpec;
			} else if (this.product.spec.length > 0) {
				// 如果没有默认规格，选择第一个
				this.selectedSpec = this.product.spec[0];
			}
		},
		doAddToCart(spec = null, quantity = 1) {
			// 构建请求参数
			let params = {
				product_id: this.productId,
				num: quantity || this.quantity
			};

			// 如果有规格，添加规格参数 (str_1是规格)
			if (spec) {
				if (spec.id) {
					params.spec_id = spec.id;
				}
				if (spec.title) {
					params.spec = spec.title;
				}
			}

			// 添加属性参数 (str_2是多个属性，以空格连接)
			if (this.selectedAttrs && Object.keys(this.selectedAttrs).length > 0) {
				const attrValues = Object.values(this.selectedAttrs).filter(val => val);
				if (attrValues.length > 0) {
					params.attr = attrValues.join(' ');
				}
			}

			// 调用加入购物车API
			_this.ajax(_this.config.cart.add, params).then(res => {
				if (res.code == 0) {
					uni.showToast({
						title: '已加入购物车',
						icon: 'success'
					});
					// 更新购物车数量
					this.updateCartCount();
				} else {
					uni.showToast({
						title: res.msg || '加入购物车失败',
						icon: 'none'
					});
				}
			})

			this.showSpecModal = false;
		},
		doBuyNow(spec = null, quantity = 1) {
			// 构建请求参数
			let params = {
				product_id: this.productId,
				num: this.quantity || quantity,
				image: this.product.image,
				title: this.product.title,
				price: this.product.price,
			};

			// 如果有规格，添加规格参数 (str_1是规格)
			if (spec) {
				if (spec.id) {
					params.spec_id = spec.id;
				}
				if (spec.title) {
					params.spec = spec.title;
				}
				params.price = this.selectedSpec.price;

			}

			// 添加属性参数 (str_2是多个属性，以空格连接)
			if (this.selectedAttrs && Object.keys(this.selectedAttrs).length > 0) {
				const attrValues = Object.values(this.selectedAttrs).filter(val => val);
				if (attrValues.length > 0) {
					params.attr = attrValues.join(' ');
				}
			}
			let items = [params];

			let url = '/pages/order/confirm?items=' + encodeURIComponent(JSON.stringify(items));

			if (spec) {
				url += '&specId=' + spec.id;
			}
			uni.navigateTo({ url });
			this.showSpecModal = false;
		},
		closeSpecModal() {
			this.showSpecModal = false;
		},
		onSpecConfirm(data) {
			if (this.actionType === 'cart') {
				this.doAddToCart(data.spec, data.quantity);
			} else if (this.actionType === 'buy') {
				this.doBuyNow(data.spec, data.quantity);
			}
		},

		// 加载购物车数量
		loadCartCount() {
			_this.ajax(_this.config.cart.count).then(res => {
				if (res.code == 0) {
					this.cartCount = res.data.count || 0;
				}
			}).catch(err => {
				console.error('获取购物车数量失败:', err);
			});
		},

		// 初始化默认属性选择
		initDefaultAttrs() {
			if (this.product.new_attr && Object.keys(this.product.new_attr).length > 0) {
				const defaultAttrs = {};
				for (const attrName in this.product.new_attr) {
					const options = this.product.new_attr[attrName];
					if (options && options.length > 0) {
						defaultAttrs[attrName] = options[0]; // 默认选择第一个
					}
				}
				this.selectedAttrs = defaultAttrs;
			}
		},

		// 选择属性
		selectAttr(attrName, option) {
			this.selectedAttrs = {
				...this.selectedAttrs,
				[attrName]: option
			};
		},

		// 更新购物车数量
		updateCartCount() {
			_this.ajax(_this.config.cart.count).then(res => {
				if (res.code == 0) {
					this.cartCount = res.data.count || 0;
					// 更新全局购物车数量
					getApp().globalData.cartCount = res.data.count || 0;
					// 更新tabBar徽标
					if (res.data.count > 0) {
						uni.setTabBarBadge({
							index: 2, // 购物车tab的索引
							text: res.data.count.toString()
						});
					} else {
						uni.removeTabBarBadge({
							index: 2
						});
					}
				}
			}).catch(err => {
				console.error('获取购物车数量失败:', err);
			});
		},

		// 返回上一页
		goBack() {
			uni.navigateBack();
		},

		// 跳转到购物车
		goToCart() {
			uni.switchTab({
				url: '/pages/cart/cart'
			});
		},

		// 跳转到首页
		goHome() {
			uni.switchTab({
				url: '/pages/index/index'
			});
		}
	}
}
</script>

<style lang="scss">
.product-detail {
	background: #f5f5f5;
	min-height: 100vh;
	padding-bottom: 120rpx;
}

/* 导航栏样式 */
.nav-bar {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	height: calc(88rpx + var(--status-bar-height));
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: var(--status-bar-height) 30rpx 0 30rpx;
	z-index: 1000;
	box-sizing: border-box;
}

.nav-back {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.nav-back-icon {
	font-size: 60rpx;
	color: #333;
}

.nav-title {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
}

.nav-cart {
	position: relative;
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.cart-icon {
	font-size: 36rpx;
}



.product-image-container {
	width: 100%;
	height: 500rpx;
	background: #fff;
}

.product-image {
	width: 100%;
	height: 100%;
}

.product-info {
	background: #fff;
	padding: 30rpx;
	margin-top: 10rpx;
}

.product-title {
	font-size: 36rpx;
	font-weight: bold;
	color: #333;
	margin-bottom: 10rpx;
	line-height: 1.4;
}

.product-price {
	font-size: 48rpx;
	color: #ff6b35;
	font-weight: bold;
	margin-bottom: 20rpx;
}

.product-desc {
	font-size: 28rpx;
	color: #666;
	line-height: 1.6;
}

.product-detail-content {
	background: #fff;
	padding: 30rpx;
	margin-top: 10rpx;
}

.detail-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #333;
	margin-bottom: 10rpx;
}



/* 规格弹窗样式 */
.spec-modal {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	z-index: 1000;
	display: flex;
	align-items: flex-end;
}

.spec-content {
	background: #fff;
	border-radius: 20rpx 20rpx 0 0;
	width: 100%;
	max-height: 80vh;
	padding: 30rpx;
}

.spec-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 30rpx;
}

.spec-product-info {
	display: flex;
	flex: 1;
}

.spec-product-image {
	width: 120rpx;
	height: 120rpx;
	border-radius: 10rpx;
	margin-right: 20rpx;
}

.spec-product-detail {
	flex: 1;
}

.spec-product-title {
	font-size: 28rpx;
	color: #333;
	margin-bottom: 10rpx;
}

.spec-product-price {
	font-size: 32rpx;
	color: #ff6b35;
	font-weight: bold;
	margin-bottom: 10rpx;
}

.spec-product-stock {
	font-size: 24rpx;
	color: #999;
}

.spec-close {
	font-size: 48rpx;
	color: #999;
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.spec-list {
	margin-bottom: 30rpx;
}

.spec-title {
	font-size: 28rpx;
	color: #333;
	margin-bottom: 20rpx;
}

.spec-options {
	display: flex;
	flex-wrap: wrap;
	gap: 20rpx;
}

.spec-option {
	padding: 15rpx 30rpx;
	border: 2rpx solid #ddd;
	border-radius: 10rpx;
	font-size: 28rpx;
	color: #666;
	background: #f9f9f9;
}

.spec-option.active {
	border-color: #ff6b35;
	color: #ff6b35;
	background: #fff;
}

/* 属性选择样式 */
.attr-list {
	margin-bottom: 30rpx;
}

.attr-group {
	margin-bottom: 20rpx;
}

.attr-title {
	font-size: 28rpx;
	color: #333;
	margin-bottom: 20rpx;
	font-weight: 500;
}

.attr-options {
	display: flex;
	flex-wrap: wrap;
	gap: 20rpx;
}

.attr-option {
	padding: 15rpx 30rpx;
	border: 2rpx solid #ddd;
	border-radius: 10rpx;
	font-size: 28rpx;
	color: #666;
	background: #f9f9f9;
}

.attr-option.active {
	border-color: #ff6b35;
	color: #ff6b35;
	background: #fff;
}

.quantity-section {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 30rpx;
}

.quantity-title {
	font-size: 28rpx;
	color: #333;
}

.quantity-controls {
	display: flex;
	align-items: center;
}

.quantity-btn {
	width: 60rpx;
	height: 60rpx;
	border: 2rpx solid #ddd;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 32rpx;
	color: #666;
	background: #f9f9f9;
}

.quantity-input {
	width: 80rpx;
	height: 60rpx;
	border-top: 2rpx solid #ddd;
	border-bottom: 2rpx solid #ddd;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28rpx;
	color: #333;
	background: #fff;
}

.spec-actions {
	padding-top: 20rpx;
}

.spec-confirm-btn {
	width: 100%;
	height: 88rpx;
	background: #ff6b35;
	color: #fff;
	border-radius: 44rpx;
	font-size: 32rpx;
	font-weight: bold;
	display: flex;
	align-items: center;
	justify-content: center;
}

.spec-confirm-btn.disabled {
	background: #ccc;
	color: #999;
}

.bottom-actions {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	height: 120rpx;
	background: #fff;
	display: flex;
	border-top: 2rpx solid #eee;
	padding: 10rpx 20rpx;
	box-shadow: 0 -4rpx 20rpx rgba(0, 0, 0, 0.1);
}

.icon-btn {
	position: relative;
	width: 120rpx;
	height: 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	color: #666;
	transition: all 0.3s ease;
}

.icon-btn:active {
	transform: scale(0.95);
	opacity: 0.8;
}

.icon-text {
	font-size: 20rpx;
	margin-top: 4rpx;
	color: #666;
}

.cart-badge {
	position: absolute;
	top: 10rpx;
	right: 20rpx;
	background: linear-gradient(135deg, #ff6b35, #ff8c42);
	color: #fff;
	border-radius: 50%;
	min-width: 32rpx;
	height: 32rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 20rpx;
	padding: 0 6rpx;
	box-shadow: 0 2rpx 8rpx rgba(255, 107, 53, 0.3);
}

.action-buttons {
	flex: 1;
	display: flex;
	gap: 10rpx;
	padding-top: 8px;
}

.action-btn {
	flex: 1;
	height: 80rpx;
	line-height: 80rpx;
	margin: 0 10rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 30rpx;
	font-weight: 600;
	color: #fff;
	border-radius: 40rpx;
	transition: all 0.3s ease;
	box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.15);
	position: relative;
	overflow: hidden;
}

.action-btn.full-width {
	margin: 0 10rpx;
	flex: none;
	width: calc(100% - 20rpx);
}

.action-btn::before {
	content: '';
	position: absolute;
	top: 0;
	left: -100%;
	width: 100%;
	height: 100%;
	background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
	transition: left 0.5s ease;
}

.action-btn:active::before {
	left: 100%;
}

.action-btn:active {
	transform: translateY(2rpx);
	box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.2);
}

.action-btn.disabled {
	background: linear-gradient(135deg, #e0e0e0, #bdbdbd);
	color: #9e9e9e;
	box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.1);
	cursor: not-allowed;
}

.action-btn.disabled:active {
	transform: none;
	box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.1);
}

.cart-btn {
	background: linear-gradient(135deg, #ffa500, #ff8c00);
	box-shadow: 0 4rpx 12rpx rgba(255, 165, 0, 0.3);
}

.cart-btn:hover {
	background: linear-gradient(135deg, #ff8c00, #ff7700);
}

.buy-btn {
	background: linear-gradient(135deg, #ff6b35, #ff4500);
	box-shadow: 0 4rpx 12rpx rgba(255, 107, 53, 0.3);
}

.buy-btn:hover {
	background: linear-gradient(135deg, #ff4500, #e63900);
}
</style>
