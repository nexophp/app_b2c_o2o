<template>
	<view :style="row.css">
		<view class="image-row">
			<view class="image-container" v-for="(v, index) in row.config" :key="index">
				<image 
					class="image-item"
					@click="pageClick(v)" 
					:src="v.image" 
					:style="row.css_inner" 
					mode="aspectFill">
				</image>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		props: {
			row: {
				type: Object,
				default: () => ({})
			},
		},
		data() {
			return {

			}
		},
		methods: { 
		}
	}
</script>

<style scoped>
	.image-row {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between; 
	}
	
	.image-container {
		width:50%; /* 留1%的间隙 */ 
		box-sizing: border-box;
	}
	
	.image-item {
		width: 100%;
		height: 180px; 
		/*border-radius: 8px;  
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); */
	}
</style>