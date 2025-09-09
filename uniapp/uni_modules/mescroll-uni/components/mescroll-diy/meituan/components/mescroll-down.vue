<!-- 下拉刷新区域 -->
<template>
	<view v-if="mOption.use" class="mescroll-downwarp" :style="{'background':mOption.bgColor,'color':mOption.textColor}">
		<view class="downwarp-content">
			<view :class="[isDownLoading ? 'downwarp-load-start' : 'downwarp-load']" :style="{ height: downLoadHeight }"></view>
			<view class="downwarp-load-preload"></view>
		</view>
	</view>
</template>

<script>
export default {
	props: {
		option: Object, // down的配置项
		type: Number, // 下拉状态（inOffset：1， outOffset：2， showLoading：3， endDownScroll：4）
		downHight: Number // 下拉刷新: 容器高度
	},
	computed: {
		// 支付宝小程序需写成计算属性,prop定义default仍报错
		mOption() {
			return this.option || {};
		},
		// 是否在加载中 (outOffset时就显示加载动画)
		isDownLoading() {
			return this.type === 2 || this.type === 3;
		},
		// 下拉刷新进度的高度,模拟下拉时小人被拉出的感觉
		downLoadHeight(){
			return this.type === 1 ? this.downHight/2+"px" : '50px'
		}
	}
};
</script>

<style>
@import '../../../mescroll-uni/components/mescroll-down.css';
@import './mescroll-down.css';
</style>
