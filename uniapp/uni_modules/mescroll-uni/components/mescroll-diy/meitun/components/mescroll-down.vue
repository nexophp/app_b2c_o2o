<!-- 下拉刷新区域 -->
<template>
	<view v-if="mOption.use" class="mescroll-downwarp" :style="{'background':mOption.bgColor,'color':mOption.textColor}">
		<view class="downwarp-content"><view class="downwarp-progress" :class="[isDownLoading ? 'downwarp-progress-fast' : 'downwarp-progress-slow']"></view></view>
	</view>
</template>

<script>
export default {
	props: {
		option: Object, // down的配置项
		type: Number // 下拉状态（inOffset：1， outOffset：2， showLoading：3， endDownScroll：4）
	},
	computed: {
		// 支付宝小程序需写成计算属性,prop定义default仍报错
		mOption() {
			return this.option || {};
		},
		// 是否在加载中  (outOffset时就显示加载动画)
		isDownLoading() {
			return this.type === 2 || this.type === 3;
		}
	}
};
</script>

<style>
@import '../../../mescroll-uni/components/mescroll-down.css';
@import './mescroll-down.css';
</style>
