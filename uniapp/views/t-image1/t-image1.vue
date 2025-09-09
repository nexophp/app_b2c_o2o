<template>
	<view :style="row.css">
		<view v-for="(v, index) in row.config">
			<image style="width: 100%;" @click="pageClick(v)" :src="v.image" :style="row.css_inner" mode="aspectFill">
			</image>
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

<style>

</style>