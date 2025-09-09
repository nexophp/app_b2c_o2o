<template>
	<view class="address-form">
		<form @submit="submitForm">
			<!-- 联系人信息 -->
			<view class="form-section">
				<view class="section-title">联系人信息</view>

				<view class="form-item">
					<text class="form-label required">收货人</text>
					<view class="input-wrapper">
						<input v-model="formData.name" class="form-input" placeholder="请输入收货人姓名" maxlength="20" />
					</view>
				</view>

				<view class="form-item">
					<text class="form-label required">手机号</text>
					<view class="input-wrapper">
						<input v-model="formData.phone" class="form-input" placeholder="请输入手机号码" type="number"
							maxlength="11" />
					</view>
				</view>
			</view>
			<!-- 地址信息 -->
			<view class="form-section">
				<view class="section-title address-section-title">
					<text class="title-icon">🏠</text>
					<text>地址信息</text>
				</view>

				<!-- 预设地址选择 -->
				<view v-if="allow_address && allow_address.length > 0 && delivery_range <= 0">
					<view class="form-item">
						<text class="form-label required">选择地址</text>
						<view class="address-selector">
							<picker @change="onAddressChange" :value="selectedAddressIndex" :range="addressTitles">
								<view class="picker-display">
									<text class="picker-text">
										{{ selectedAddressIndex >= 0 ? addressTitles[selectedAddressIndex] : default_select_add }}
									</text>
									<text class="picker-arrow">›</text>
								</view>
							</picker>
						</view>
					</view>
					
					<!-- 选中地址详情显示 -->
					<view v-if="formData.str_2" class="address-detail-card">
						<view class="address-detail-header">
							<text class="detail-icon">📍</text>
							<text class="detail-title">配送地址</text>
						</view>
						<view class="address-detail-content">
							<text class="address-text">{{ formData.province }}</text>
							<text v-if="formData.city != '市辖区'" class="address-text">{{ formData.city }}</text>
							<text class="address-text">{{ formData.district }}</text>
							<text class="address-detail">{{ formData.str_2 }}</text>
						</view>
					</view>
				</view>

				<!-- 地区选择 - 使用cl-select-region组件 -->
				<view class="form-item" v-if="delivery_range > 0">
					<text class="form-label required">所在地区</text>
					<view class="region-selector">
						<view class="region-select-wrapper">
							<cl-select-region v-if="options && options.length > 0" @change="onRegionChange"
								:options="options" :label-key="label" :value-key="label" v-model="formData.region"
								placeholder="请选择省市区" class="region-select"></cl-select-region>
						</view>
					</view>
				</view>

				<!-- 详细地址 -->
				<view class="form-item">
					<text class="form-label required">详细地址</text>
					<view class="textarea-wrapper">
						<textarea v-model="formData.detail" class="form-textarea" placeholder="请输入楼栋号门牌号，如100号301室"
							maxlength="100" auto-height />
					</view>
				</view>
			</view>

			<!-- 设置选项 -->
			<view class="form-section">
				<view class="form-item switch-item">
					<text class="form-label">设为默认地址</text>
					<cl-switch v-model="formData.is_default" active-color="#007aff" active-value="1"
						inactive-value="0"></cl-switch>
				</view>
			</view>

			<!-- 提交按钮 -->
			<view class="form-actions">
				<view class="add-btn" @click="submitForm">
					{{ isEdit ? '保存修改' : '保存地址' }}
				</view>
			</view>
		</form>
		<cl-message ref="message"></cl-message>
	</view>
</template>

<script>
export default {
	data() {
		return {
			default_select_add: '请选择可配送的地址',
			api_city: '',
			label: 'label',
			city_url: '',
			isEdit: false,
			addressId: '',
			options: [],
			formData: {
				name: '',
				phone: '',
				region: [], // 省市区数组 [province, city, district]
				province: '',
				city: '',
				district: '',
				detail: '',
				is_default: false,
				str_1: '', // 预设地址选择字段
			},
			delivery_range: 0,
			allow_address: [],
			selectedAddressIndex: -1, // 选中的地址索引
		}
	},
	computed: {
		// 获取地址标题数组
		addressTitles() {
			return this.allow_address.map(item => item.title)
		}
	},
	onLoad(options) {
		this.api_city = this.config.domain + '/address/api/index'
		this.load_info()
		this.load()
		if (options.id) {
			this.isEdit = true
			this.addressId = options.id
			this.loadAddressData()
		}
	},
	methods: {
		load_info() {
			this.ajax(this.config.o2o.home, {}).then(res => {
				this.delivery_range = res.data.delivery_range
			})
			this.ajax(this.config.o2o.address, {}).then(res => {
				this.allow_address = res.data
			})

		},
		load() {
			this.ajax(this.config.address.city, {}).then(res => {
				// 处理API返回的数据，确保children是数组格式
				this.options = this.processRegionData(res.data)
			})
		},

		// 处理地区数据，确保children字段是数组格式
		processRegionData(data) {
			if (!data || typeof data !== 'object') return []

			const processItem = (item) => {
				if (!item) return null

				const processed = {
					id: item.id,
					name: item.name,
					label: item.label,
					pid: item.pid
				}

				// 确保children是数组格式
				if (item.children && Array.isArray(item.children)) {
					processed.children = item.children.map(child => processItem(child)).filter(child => child !== null)
				} else {
					processed.children = []
				}

				return processed
			}

			// 如果data是数组
			if (Array.isArray(data)) {
				return data.map(item => processItem(item)).filter(item => item !== null)
			}

			// 如果data是对象，转换为数组
			const result = []
			for (const key in data) {
				if (data.hasOwnProperty(key) && typeof data[key] === 'object') {
					const processed = processItem(data[key])
					if (processed) {
						result.push(processed)
					}
				}
			}

			return result
		},
		loadAddressData() {
			this.ajax(this.config.address.view, {
				id: this.addressId
			}).then(res => {
				this.formData = res.data
				this.default_select_add = res.data.str_1
			})
		},

		// 预设地址选择变化事件
		onAddressChange(e) {
			const index = e.detail.value
			this.selectedAddressIndex = index

			if (index >= 0 && this.allow_address[index]) {
				const selectedAddress = this.allow_address[index]
				// 将选中的地址标题保存到str_1字段
				this.formData.str_1 = selectedAddress.title || ''
				// 自动填充地区信息
				if (selectedAddress.regions && selectedAddress.regions.length >= 3) {
					this.formData.region = selectedAddress.regions
					this.formData.province = selectedAddress.regions[0]
					this.formData.city = selectedAddress.regions[1]
					this.formData.district = selectedAddress.regions[2]
				}
				// 自动填充详细地址
				if (selectedAddress.detail) {
					this.formData.str_2 = selectedAddress.detail
				}
			} else {
				// 清空str_1字段
				this.formData.str_1 = ''
			}
		},

		// 地区选择变化事件
		onRegionChange(value) {
			console.log('地区选择变化:', value)
			// value 是一个数组，包含省市区信息
			if (value && value.length === 3) {
				this.formData.region = value
				this.formData.province = value[0]
				this.formData.city = value[1]
				this.formData.district = value[2]
			}
			// 当手动选择地区时，清除预设地址选择
			this.selectedAddressIndex = -1
			// 清空str_1字段
			this.formData.str_1 = ''
		},

		// 提交表单
		submitForm() {
			console.log('提交地址数据:', this.formData)
			let url = this.config.address.create
			if (this.isEdit) {
				url = this.config.address.update
			}
			this.ajax(url, this.formData).then(res => {

				this.$refs["message"].open({
					message: res.msg,
					type: res.type
				});
				if (res.code == 0) {
					setTimeout(() => {
						uni.navigateBack({
							delta: 1
						})
					}, 1000)
				}
			})


		}
	}
}
</script>

<style>
.address-form {
	background-color: #f5f5f5;
	padding-bottom: 50rpx;
	height: 100vh;
}

.form-section {
	background-color: #fff;
	margin-bottom: 20rpx;
	padding: 30rpx;
}

.section-title {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
	margin-bottom: 30rpx;
}

.address-section-title {
	display: flex;
	align-items: center;
	background: linear-gradient(90deg, #ff6b35, #f7931e);
	-webkit-background-clip: text;
	-webkit-text-fill-color: transparent;
	background-clip: text;
}

.title-icon {
	font-size: 36rpx;
	margin-right: 12rpx;
	filter: none;
	-webkit-text-fill-color: initial;
}

.form-item {
	display: flex;
	align-items: center;
	padding: 30rpx 0;
	border-bottom: 1px solid #f0f0f0;
}

.form-item:last-child {
	border-bottom: none;
}

.form-label {
	width: 180rpx;
	font-size: 28rpx;
	color: #333;
	flex-shrink: 0;
}

.input-wrapper {
	flex: 1;
	background: #f8f9fa;
	border-radius: 12rpx;
	padding: 16rpx 20rpx;
	border: 2rpx solid #e9ecef;
	transition: all 0.3s ease;
}

.input-wrapper:focus-within {
	border-color: #007aff;
	background: #fff;
	box-shadow: 0 0 0 4rpx rgba(0, 122, 255, 0.1);
}

.form-input {
	width: 100%;
	font-size: 28rpx;
	color: #333;
	text-align: right;
	background: transparent;
	border: none;
	outline: none;
}

.textarea-wrapper {
	flex: 1;
	background: #f8f9fa;
	border-radius: 12rpx;
	padding: 16rpx 20rpx;
	border: 2rpx solid #e9ecef;
	transition: all 0.3s ease;
}

.textarea-wrapper:focus-within {
	border-color: #007aff;
	background: #fff;
	box-shadow: 0 0 0 4rpx rgba(0, 122, 255, 0.1);
}

.form-textarea {
	width: 100%;
	font-size: 28rpx;
	color: #333;
	min-height: 120rpx;
	text-align: right;
	background: transparent;
	border: none;
	outline: none;
	resize: none;
}

.region-selector {
	flex: 1;
	display: flex;
	justify-content: flex-end;
	align-items: center;
}

.region-select-wrapper {
	background: #f8f9fa;
	border-radius: 12rpx;
	padding: 16rpx 20rpx;
	border: 2rpx solid #e9ecef;
	transition: all 0.3s ease;
	width: 100%;
}

.region-select-wrapper:focus-within {
	border-color: #007aff;
	background: #fff;
	box-shadow: 0 0 0 4rpx rgba(0, 122, 255, 0.1);
}

.region-select {
	width: 100%;
	text-align: right;
	background: transparent;
}

.switch-item {
	justify-content: space-between;
}

.form-actions {
	padding: 40rpx 30rpx;
}

::v-deep .cl-select__icon {
	top: 10px !important;
}

.add-btn {
	width: 100%;
	height: 80rpx;
	background-color: #ff6b35 !important;
	color: #fff !important;
	border: none !important;
	border-radius: 40rpx !important;
	font-size: 30rpx !important;
	line-height: 80rpx !important;
	box-shadow: 0 4rpx 12rpx rgba(255, 107, 53, 0.3);
	text-align: center;
}

.address-selector {
	flex: 1;
	display: flex;
	justify-content: flex-end;
	align-items: center;
}

.picker-display {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	background: #f8f9fa;
	border-radius: 12rpx;
	padding: 16rpx 20rpx;
	border: 2rpx solid #e9ecef;
	transition: all 0.3s ease;
}

.picker-display:active {
	background: #e9ecef;
	border-color: #007aff;
}

.picker-text {
	font-size: 28rpx;
	color: #333;
	flex: 1;
	text-align: right;
}

.picker-arrow {
	font-size: 32rpx;
	color: #999;
	margin-left: 12rpx;
	transform: rotate(0deg);
	transition: transform 0.3s ease;
}

/* 地址详情卡片样式 */
.address-detail-card {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: 16rpx;
	padding: 24rpx;
	margin-top: 20rpx;
	box-shadow: 0 8rpx 24rpx rgba(102, 126, 234, 0.15);
}

.address-detail-header {
	display: flex;
	align-items: center;
	margin-bottom: 16rpx;
}

.detail-icon {
	font-size: 32rpx;
	margin-right: 12rpx;
}

.detail-title {
	font-size: 28rpx;
	color: #fff;
	font-weight: 600;
}

.address-detail-content {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
}

.address-text {
	font-size: 26rpx;
	color: rgba(255, 255, 255, 0.9);
	margin-right: 8rpx;
	margin-bottom: 4rpx;
}

.address-detail {
	font-size: 26rpx;
	color: #fff;
	font-weight: 500;
	margin-top: 8rpx;
	width: 100%;
	line-height: 1.4;
}
</style>