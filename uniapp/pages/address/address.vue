<template>
	<view>
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">

			<view class="address-list" v-if="list && list.length > 0">
				<!-- 地址列表 -->
				<view class="address-container">
					<view v-for="(address, index) in list" class="address-item" @click="selectAddress(address, index)">
						<!-- 默认标签 -->
						<view v-if="address.is_default" class="default-tag">默认</view>

						<!-- 地址信息 -->
						<view class="address-info">
							<view class="user-info">
								<text class="user-name">{{ address.name }}</text>
								<text class="user-phone">{{ address.phone }}</text>
							</view>
							<text class="address-detail">{{ address.address }} 
									<text v-if="address.str_1" >（{{ address.str_1 }}）</text> 
							</text>
						</view>
						

						<!-- 操作按钮 -->
						<view class="address-actions">
							<view type="default" size="mini" @click.stop="editAddress(address)" class="edit-btn">
								编辑
							</view>
							<view type="default" size="mini" @click.stop="deleteAddress(address.id)"
								class="delete-btn">
								删除
							</view>
						</view>
					</view>

				</view>


			</view>
			<view v-else>
				<view>
					<view class="no-address">
						<text>暂无地址</text>
					</view>
					 
				</view>

			</view>



		</mescroll-body>
		<!-- 底部添加按钮 -->
		<view class="bottom-action">
			<view @click="addAddress" class="add-btn">
				+ 新增收货地址
			</view>
		</view>
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
					tip: '暂无相关数据'
				},
				textNoMore: ' '
			},
			downOption: {
				auto: false, //是否在初始化后,自动执行downCallback; 默认true 
			},
			list: [],
			fromPage: '',
		}
	},
	onLoad(options) {
		_this = this
		if (options.from) {
			this.fromPage = options.from
		}
	},
	onShow() {
		this.load()
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

			_this.ajax(_this.config.address.index, w).then(res => {
				_this.is_load = true
				if (res.code != 0) {
					_this.mescroll.endBySize(0, 0);
					return;
				}
				if (res.current_page == 1) {
					_this.list = []
				}
				for (let i in res.data) {
					_this.list.push(res.data[i])
				}
				_this.mescroll.endBySize(res.per_page, res.total);
			})
		},

		// 选择地址
		selectAddress(address, index) {
			if (this.fromPage === 'order') {
				// 从订单页面来的，选择地址后返回
				uni.navigateBack({
					delta: 1
				})
				// 这里可以通过事件总线或其他方式传递选中的地址
				uni.$emit('selectAddress', address)
			}
		},

		// 添加地址
		addAddress() {
			uni.navigateTo({
				url: '/pages/address/view'
			})
		},

		// 编辑地址
		editAddress(address) {
			uni.navigateTo({
				url: `/pages/address/view?id=${address.id}`
			})
		},

		// 删除地址
		deleteAddress(id) {
			uni.showModal({
				title: '确认删除',
				content: '确定要删除这个收货地址吗？',
				success: (res) => {
					if (res.confirm) {
						_this.ajax(_this.config.address.del, { id: id }).then(res => {
							_this.reload()
						})
					}
				}
			})
		}
	}
}
</script>

<style>
.address-list {
	background-color: #f5f5f5;
	padding-bottom: 120rpx;
}

.address-container {
	padding: 20rpx;
}

.address-item {
	background-color: #fff;
	border-radius: 16rpx;
	margin-bottom: 20rpx;
	padding: 30rpx;
	position: relative;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.default-tag {
	position: absolute;
	top: 0;
	right: 0;
	background-color: #ff6b35;
	color: #fff;
	font-size: 22rpx;
	padding: 6rpx 16rpx;
	border-radius: 0 16rpx 0 16rpx;
}

.address-info {
	margin-bottom: 30rpx;
}

.user-info {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
}

.user-name {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
}

.user-phone {
	font-size: 28rpx;
	color: #666;
}

.address-detail {
	font-size: 28rpx;
	color: #666;
	line-height: 1.5;
	display: block;
}

.address-actions {
	display: flex;
	gap: 20rpx;
	justify-content: flex-end;
}

.edit-btn {
	background-color: #fff !important;
	color: #ff6b35 !important;
	border: 1rpx solid #ff6b35 !important;
	border-radius: 40rpx !important;
	padding: 8rpx 24rpx !important;
	font-size: 26rpx !important;
}

.edit-btn:active {
	opacity: 0.8;
}

.delete-btn {
	background-color: #fff !important;
	color: #ff4d4f !important;
	border: 1rpx solid #ff4d4f !important;
	border-radius: 40rpx !important;
	padding: 8rpx 24rpx !important;
	font-size: 26rpx !important;
}

.delete-btn:active {
	opacity: 0.8;
}

.bottom-action {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	background-color: #fff;
	padding: 20rpx 30rpx;
	border-top: 1rpx solid #eee;
	box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
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

.add-btn:active {
	opacity: 0.9;
}
.no-address {
	text-align: center;
	font-size: 28rpx;
	color: #999;
	margin-top: 100rpx;

}
::v-deep .cl-button.cl-button--error .cl-button__text {
	color: #666;
}
</style>