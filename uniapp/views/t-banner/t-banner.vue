<template>
	<view :style="row.css"> 
		<u-swiper :style="row.css_inner" @click="click" :list="banner" keyName="image"
			:height="inner_height"></u-swiper>
	</view>
</template>

<script>
	export default {
		props: {
			row: {
				type: Object,
				default: () => ({})
			},

			banner: {
				type: Array,
				default: () => []
			},
			height: {
				type: Number,
				default: 0
			},
		},
		data() {
			return {
				inner_height: 0
			}
		},
		mounted() {
			if(this.height > 0){
				this.inner_height = this.height
			}else{
				this.inner_height = uni.getSystemInfoSync().windowWidth
			}
			
		},

		methods: {
			click(e) {
				let row = this.banner[e]
				this.pageClick(row)
			},
		},
	}
</script>