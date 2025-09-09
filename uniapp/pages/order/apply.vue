<template>
	<view class="refund-apply">
		 
		<!-- 订单信息 -->
		<view class="order-info">
			<view class="section-title">订单信息</view>
			<view class="order-item">
				<text class="order-no">订单号：{{ orderInfo.order_num || '' }}</text>
				<text class="order-status">状态：{{ orderInfo.status_text || ''}}</text>
			</view>
		</view>

		<!-- 商品选择 -->
		<view class="product-section">
			<view class="section-title">{{ productId ? '售后商品' : '选择商品' }}</view>
			<view class="product-list">
				<view v-for="(product, index) in displayProducts" :key="product.id" class="product-item">
					<checkbox v-if="!productId" :value="product.id" :checked="selectedProducts.includes(product.id)" @change="toggleProduct(product.id)" />
					<image :src="product.image || '/static/demo.jpeg'" class="product-image"></image>
					<view class="product-info">
						<text class="product-name">{{ product.title || product.name }}</text>
						<text class="product-spec">{{ product.spec || '默认规格' }}</text>
						<view class="product-price-qty">
							<text class="product-price">¥{{ product.price }}</text>
							<text class="product-qty">x{{ product.num }}</text>
						</view>
					</view>
				</view>
			</view>
		</view>

		<!-- 售后类型 -->
		<view class="refund-type-section">
			<view class="section-title">售后类型</view>
			<uni-data-select 
				v-model="refundForm.type" 
				:localdata="refundTypeOptions" 
				placeholder="请选择售后类型"
				@change="onRefundTypeChange"
			></uni-data-select>
		</view>
		
		<view 
		  v-if="['exchange', 'return'].includes(refundForm.type)" 
		  class="address-container" @click="cp(address)"
		>
		  <view class="address-title">退货地址：</view>
		  <view v-if="address" class="address-content">
		    {{ address }}
		  </view>
		  <view v-else class="address-empty">
		    暂无默认退货地址，请联系客服
		  </view>
		</view>
 
		<!-- 问题描述 -->
		<view class="description-section">
			<view class="section-title">问题描述</view>
			<textarea v-model="refundForm.desc" placeholder="请详细描述遇到的问题" class="description-input" maxlength="500" />
			 
		</view>

		<!-- 上传图片 -->
		<view class="upload-section">
			<view class="section-title">上传图片（可选）</view>
			<view class="upload-list">
				<view v-for="(image, index) in refundForm.images"   class="upload-item">
					<image :src="image" class="upload-image" @click="previewImage(image)" />
					<text class="delete-btn" @click="deleteImage(index)">×</text>
				</view>
				<view v-if="refundForm.images.length < 6" class="upload-btn" @click="chooseImage">
					<text class="upload-icon">+</text>
					<text class="upload-text">添加图片</text>
				</view>
			</view>
			<text class="upload-tip">最多上传6张图片，支持jpg、png格式</text>
		</view>
 

		<!-- 提交按钮 -->
		<view class="submit-section">
			<button class="submit-btn" @click="submitRefund" >提交申请</button>
		</view>

		<!-- 底部安全距离 -->
		<view class="bottom-safe-area"></view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			orderId: '',
			productId: null,
			orderInfo: {},
			selectedProducts: [],
			reasonIndex: 0,
			address:'',
			reasonList: [
				'商品质量问题',
				'商品与描述不符',
				'收到商品破损',
				'发错商品',
				'不喜欢/不想要',
				'其他原因'
			],
			refundTypeOptions: [
				{ value: 'refund', text: '退款' }, 
			],
			refundForm: {
				type: 'refund',
				reason: '线下门店退款',
				description: '',
				images: [],
				receiver_name: '',
				receiver_phone: '',
				receiver_address: ''
			}
		}
	},

	computed: { 
		// 显示的商品列表
		displayProducts() {
			if (!this.orderInfo.products) return []
			// 如果有指定商品ID，只显示该商品
			if (this.productId) {
				return this.orderInfo.products.filter(p => p.id == this.productId)
			}
			// 否则显示所有商品
			return this.orderInfo.products
		}
	},

	onLoad(options) {
		this.orderId = options.id
		this.productId = options.product_id || null
		this.loadOrderDetail()
		this.loadAddress() 
	},

	methods: {
		loadAddress(){
			this.ajax(this.config.refund.address,{}).then(res=>{
				this.address = res.data 
			}) 
		},
		// 返回上一页
		goBack() {
			uni.navigateBack()
		},

		// 加载订单详情
		loadOrderDetail() {
			this.ajax(this.config.order.view, {
				id: this.orderId
			}).then(res => {
				this.orderInfo = res.data
				// 如果有指定商品ID，则只选择该商品，否则默认选择所有商品
				if (this.productId) {
					this.selectedProducts = [parseInt(this.productId)]
				} else {
					this.selectedProducts = this.orderInfo.products.map(p => p.id)
				}
			})
		},

		// 切换商品选择
		toggleProduct(productId) {
			const index = this.selectedProducts.indexOf(productId)
			if (index > -1) {
				this.selectedProducts.splice(index, 1)
			} else {
				this.selectedProducts.push(productId)
			}
		},

		// 售后类型改变
		onRefundTypeChange(e) {
			this.refundForm.type = e.detail.value
		},

		// 退款原因改变
		onReasonChange(e) {
			this.reasonIndex = e.detail.value
			this.refundForm.reason = this.reasonList[e.detail.value]
		},

		// 选择图片
		chooseImage() {
			uni.chooseImage({
				count: 6 - this.refundForm.images.length,
				sizeType: ['compressed'],
				sourceType: ['album', 'camera'],
				success: (res) => { 
					res.tempFilePaths.map(filePath => {
						return this.upload(filePath, {})
					}) 
				}
			})
		}, 
		after_upload(res){ 
			if(!this.refundForm.images){
				this.refundForm.images = []
			}
			this.refundForm.images.push(res.http_url)
		},

		// 预览图片
		previewImage(current) {
			uni.previewImage({
				current,
				urls: this.refundForm.images
			})
		},

		// 删除图片
		deleteImage(index) {
			this.refundForm.images.splice(index, 1)
		},

		// 提交售后申请
		submitRefund() {
			 
			uni.showLoading({
				title: '提交中...'
			})

			const formData = {
				order_id: this.orderId,
				order_item_ids: this.selectedProducts,
				type: this.refundForm.type,
				reason: this.refundForm.reason,
				desc: this.refundForm.desc,
				images: this.refundForm.images,
				address:{
					name: this.refundForm.receiver_name,
					phone: this.refundForm.receiver_phone,
					address: this.refundForm.receiver_address
				}
			}

			this.ajax(this.config.refund.create, formData).then(res => {
				uni.hideLoading()
				if(res.code == 0){
					uni.showToast({
						title: '申请提交成功',
						icon: 'success'
					})
				}else{
					uni.showToast({
						title:res.msg ,
						icon:"none"
					})
				}
				 
			}) 
		}
	}
}
</script>

<style>
.refund-apply {
	background-color: #f5f5f5;
	min-height: 100vh;
	padding-bottom: 120rpx;
}

.section-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #333;
	margin-bottom: 20rpx;
}

/* 订单信息 */
.order-info {
	background-color: #fff;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.order-item {
	display: flex;
	flex-direction: column;
	gap: 10rpx;
}

.order-no, .order-status {
	font-size: 28rpx;
	color: #666;
}

/* 商品选择 */
.product-section {
	background-color: #fff;
	padding: 30rpx;
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
	margin: 0 20rpx;
}

.product-info {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 10rpx;
}

.product-name {
	font-size: 28rpx;
	color: #333;
	font-weight: 500;
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
	color: #666;
}

/* 售后类型 */
.refund-type-section {
	background-color: #fff;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

/* 退款原因 */
.reason-section {
	background-color: #fff;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.picker-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20rpx 0;
	font-size: 28rpx;
	color: #333;
	border-bottom: 1px solid #f0f0f0;
}

.picker-arrow {
	color: #999;
}

/* 问题描述 */
.description-section {
	background-color: #fff;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.description-input {
	width: 100%;
	height: 200rpx;
	padding: 20rpx;
	border: 1px solid #e0e0e0;
	border-radius: 8rpx;
	font-size: 28rpx;
	box-sizing: border-box;
}

.char-count {
	font-size: 24rpx;
	color: #999;
	text-align: right;
	display: block;
	margin-top: 10rpx;
}

/* 上传图片 */
.upload-section {
	background-color: #fff;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.upload-list {
	display: flex;
	flex-wrap: wrap;
	gap: 20rpx;
	margin-bottom: 20rpx;
}

.upload-item {
	position: relative;
	width: 160rpx;
	height: 160rpx;
}

.upload-image {
	width: 100%;
	height: 100%;
	border-radius: 8rpx;
}

.delete-btn {
	position: absolute;
	top: -10rpx;
	right: -10rpx;
	width: 40rpx;
	height: 40rpx;
	background-color: #ff4d4f;
	color: #fff;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 24rpx;
}

.upload-btn {
	width: 160rpx;
	height: 160rpx;
	border: 2rpx dashed #d9d9d9;
	border-radius: 8rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 10rpx;
}

.upload-icon {
	font-size: 48rpx;
	color: #999;
}

.upload-text {
	font-size: 24rpx;
	color: #999;
}

.upload-tip {
	font-size: 24rpx;
	color: #999;
}

/* 收货地址 */
.address-section {
	background-color: #fff;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.address-form {
	display: flex;
	flex-direction: column;
	gap: 20rpx;
}

.form-input {
	padding: 20rpx;
	border: 1px solid #e0e0e0;
	border-radius: 8rpx;
	font-size: 28rpx;
}

.form-textarea {
	padding: 20rpx;
	border: 1px solid #e0e0e0;
	border-radius: 8rpx;
	font-size: 28rpx;
	height: 120rpx;
}

/* 提交按钮 */
.submit-section {
	padding: 30rpx;
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	background-color: #fff;
	border-top: 1px solid #f0f0f0;
	z-index: 100;
}

.submit-btn {
	width: 100%;
	height: 88rpx;
	background: linear-gradient(135deg, #ff6b35 0%, #ff8f5a 100%);
	color: #fff;
	border: none;
	border-radius: 44rpx;
	font-size: 32rpx;
	font-weight: 600;
	box-shadow: 0 8rpx 24rpx rgba(255, 107, 53, 0.3);
	transition: all 0.3s ease;
}

.submit-btn:disabled {
	background: #e0e0e0;
	color: #999;
	box-shadow: none;
}

.submit-btn:not(:disabled):active {
	transform: translateY(2rpx);
	box-shadow: 0 4rpx 12rpx rgba(255, 107, 53, 0.4);
}

/* 底部安全距离 */
.bottom-safe-area {
	height: 180rpx;
}
.address-container {
  margin-top: 20rpx;
  padding: 30rpx;
  background-color: #fff;
  border-radius: 12rpx;
  border: 2rpx solid #e8f4fd;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  position: relative;
}

.address-container::after {
  content: '';
  position: absolute;
  top: 20rpx;
  right: 20rpx;
  width: 32rpx;
  height: 32rpx;
  background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEwLjUgMUgzLjVDMi42NzE1NyAxIDIgMS42NzE1NyAyIDIuNVY5LjVDMiAxMC4zMjg0IDIuNjcxNTcgMTEgMy41IDExSDEwLjVDMTEuMzI4NCAxMSAxMiAxMC4zMjg0IDEyIDkuNVYyLjVDMTIgMS42NzE1NyAxMS4zMjg0IDEgMTAuNSAxWiIgc3Ryb2tlPSIjNjY2IiBzdHJva2Utd2lkdGg9IjEuNSIgZmlsbD0ibm9uZSIvPgo8cGF0aCBkPSJNMTQgNS41VjEyLjVDMTQgMTMuMzI4NCAxMy4zMjg0IDE0IDEyLjUgMTRINS41IiBzdHJva2U9IiM2NjYiIHN0cm9rZS13aWR0aD0iMS41IiBmaWxsPSJub25lIi8+Cjwvc3ZnPgo=');
  background-size: contain;
  background-repeat: no-repeat;
  opacity: 0.6;
}

.address-title {
  font-weight: 600;
  margin-bottom: 15rpx;
  color: #1a73e8;
  font-size: 28rpx;
  display: flex;
  align-items: center;
}

.address-title::before {
  content: '📍';
  margin-right: 8rpx;
  font-size: 24rpx;
}

.address-content {
  color: #333;
  line-height: 1.8;
  font-size: 26rpx;
  padding: 15rpx;
  background-color: #f8f9fa;
  border-radius: 8rpx;
  border-left: 4rpx solid #1a73e8;
  word-break: break-all;
}

.address-empty {
  color: #999;
  font-style: italic;
  text-align: center;
  padding: 30rpx;
  font-size: 26rpx;
}
</style>
