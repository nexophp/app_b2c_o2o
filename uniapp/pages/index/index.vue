<template>
	<view>
		<view class="" v-for="(v, index) in page_data">
			<view class="" v-if="v.type == 'banner'" class="page-margin-bottom">
				<t-banner :banner="v.config" :row="v"></t-banner>
			</view>
			<view class="" v-if="v.type == 'search'" class="page-margin-bottom">
				<view class="search-entry" @click="jump('/pages/search/search')" :style="v.css">
					<uni-icons type="search" size="16" color="#999"></uni-icons>
					<text class="placeholder">请输入搜索内容</text>
				</view>
			</view>
			<view class="" v-if="v.type == 'menu'" class="page-margin-bottom">
				<t-menu :menuList="v.config" :row="v" />
			</view>
			<view class="" v-if="v.type == 'product'" class="page-margin-bottom">
				<t-product :row="v" :showSwitch="false" ref="product" @reach-bottom="handleReachBottom(index)" />
			</view>
			
			<view class="" v-if="v.type == 'image_1'" class="page-margin-bottom">
				<t-image1 :row="v" />
			</view>
			
			<view class="" v-if="v.type == 'image_2'" class="page-margin-bottom">
				<t-image2 :row="v" />
			</view>
			
			
		</view>
	</view>
</template>

<script>
export default {
	data() {
		return {
			height: '',
			page: '',
			row: {},
			page_data: [],
		}
	},
	onLoad(opt) {
		this.page = opt.page || ''
		this.height = uni.getSystemInfoSync().windowWidth
	},
	onShow() {
		this.load()
	},
	onReachBottom() {
		console.log('已经滚动到底部')
		if (this.$refs.product) {
			this.$refs.product.forEach(comp => {
				if (comp.loadMore) {
					comp.loadMore()
				}
			})
		}
	},
	methods: {
		handleReachBottom(index) {
			if (this.$refs.product[index]) {
				this.$refs.product[index].loadMore()
			}
		},
		load() {
			this.ajax(this.config.page, {
				url: this.page
			}).then(res => {
				this.row = res.data
				this.set_title(this.row.name)
				this.page_data = res.data.page_data
			})
		}
	}
}
</script>

<style lang="scss">
.search-entry {
	display: flex;
	align-items: center;
	padding: 20rpx 30rpx;
	background-color: #fff;
	border-radius: 30rpx;
	margin: 20rpx;
	margin-bottom: 0px;
}

.placeholder {
	margin-left: 10rpx;
	color: #999;
	font-size: 14px;
}

.page-margin-bottom {}
</style>