<!-- 下拉刷新区域 -->
<template>
	<view v-if="mOption.use" class="mescroll-downwarp" :style="{'background':mOption.bgColor,'color':mOption.textColor}">
		<view class="downwarp-content">
			<view class="downwarp-slogan">让购物更便捷</view>
			<view class="downwarp-text">{{downText}}</view>
			<view v-if="isDownLoading" class="downwarp-loading"></view>
			<view v-else class="downwarp-progress">
				<image class="downwarp-man" :style="{'transform':downRotate, 'transition': transition}" src="https://www.mescroll.com/img/jingdong/mescroll-progress1.png" mode="widthFix"/>
				<image class="downwarp-box" :style="{'transform':downRotate, 'transition': transition}" src="https://www.mescroll.com/img/jingdong/mescroll-progress0.png" mode="widthFix"/>
			</view>
			<!-- 预加载,避免首次显示动画时,图片没及时加载完毕导致短暂的白屏 -->
			<view class="downwarp-preload"></view>
		</view>
	</view>
</template>

<script>
export default {
	props: {
		option: Object, // down的配置项
		type: Number, // 下拉状态（inOffset：1， outOffset：2， showLoading：3， endDownScroll：4）
		rate: Number // 下拉比率 (inOffset: rate<1; outOffset: rate>=1)
	},
	computed: {
		// 支付宝小程序需写成计算属性,prop定义default仍报错
		mOption() {
			return this.option || {};
		},
		// 是否在加载中
		isDownLoading() {
			return this.type === 3;
		},
		// 缩放比例
		downRotate() {
			return this.type === 1 ? 'scale('+this.rate+','+this.rate+')' : this.type === 4 ? 'scale(0,0)' : '';
		},
		// 过渡
		transition() {
			return this.type===4 ? 'transform 300ms' : ''
		},
		// 文本提示
		downText() {
			switch (this.type) {
				case 1:
					return this.mOption.textInOffset;
				case 2:
					return this.mOption.textOutOffset;
				case 3:
					return this.mOption.textLoading;
				case 4:
					return this.mOption.textLoading;
				default:
					return this.mOption.textInOffset;
			}
		}
	}
};
</script>

<style>
@import '../../../mescroll-uni/components/mescroll-down.css';
@import './mescroll-down.css';
</style>
