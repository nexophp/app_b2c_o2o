<!-- 下拉刷新区域 -->
<template>
	<view v-if="mOption.use" class="mescroll-downwarp" :class="{'moon-show':isShowMoon}">
		<view class="downwarp-content">
			<view v-if="isShowDownLoad" class="downwarp-progress" :class="{'mescroll-rotate':isDownLoading}">
				<view class="progress-arc">
					<view class="progress-left-arc" :style="{'transform':rotateLeft}"></view>
				</view>
				<view class="progress-arc">
					<view class="progress-right-arc" :style="{'transform':rotateRight}"></view>
				</view>
			</view>
			<view class="downwarp-tip">{{downText}}</view>
		</view>
	</view>
</template>

<script>
export default {
	props: {
		option: Object, // down的配置项
		type: Number, // 下拉状态（inOffset：1， outOffset：2， showLoading：3， endDownScroll：4）
		rate: Number, // 下拉比率 (inOffset: rate<1; outOffset: rate>=1)
		isShowMoon: Boolean // 是否显示月亮的动画
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
		// 是否显示加载区域 （大于2倍offset时隐藏加载区域，显示二楼）
		isShowDownLoad() {
			return this.rate < 2
		},
		// 左半圆旋转的角度
		rotateLeft(){
			if (this.rate<1) {
				let progress = 360 * this.rate;
				if (progress<180) {
					return 'rotate(45deg)';
				} else{
					return 'rotate(' + (progress-180+45) + 'deg)';
				}
			}else{
				return 'rotate(225deg)';
			}
		},
		// 右半圆旋转的角度
		rotateRight(){
			let progress = 360 * this.rate;
			if (progress<180) {
				return 'rotate(' + (progress+45) + 'deg)';
			} else{
				return 'rotate(225deg)';
			}
		},
		// 文本提示
		downText() {
			switch (this.type) {
				case 1:
					return this.mOption.textInOffset;
				case 2:
					if (this.isShowDownLoad) {
						return this.mOption.textOutOffset;
					} else{
						return  "欢迎光临 淘宝二楼";
					}
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
