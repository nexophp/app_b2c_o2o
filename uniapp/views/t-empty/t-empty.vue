<template>
	<view class="t-empty">
		<view class="t-empty__content">
			<!-- 显示图片 -->
			<image v-if="!$slots.default" class="t-empty__image" :src="computedImage" mode="aspectFit" />

			<!-- 显示文本 -->
			<text v-if="!$slots.default" class="t-empty__text">{{ computedText }}</text>

			<!-- 默认插槽，允许完全自定义内容 -->
			<slot v-else />

			<!-- 操作按钮 -->
			<view v-if="actionText && !$slots.default" class="t-empty__action">
				<button class="t-empty__button" @click="handleAction">{{ actionText }}</button>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 't-empty',
		props: {
			// 空状态类型
			type: {
				type: String,
				default: ''
			},
			// 显示文本
			text: {
				type: String,
				default: ''
			},
			// 操作按钮文本
			actionText: {
				type: String,
				default: ''
			},
			// 自定义图片
			image: {
				type: String,
				default: ''
			}
		},
		computed: {
			computedImage() {
				if (this.image) return this.image;

				// 根据类型返回默认图片
				const images = {
					order: '/static/empty/empty-order.png',
					cart: '/static/empty/empty-cart.png',
					search: '/static/empty/empty-search.png',
					network: '/static/empty/empty-network.png'
				};

				return images[this.type] || '/static/empty/empty-default.png';
			},
			computedText() {
				if (this.text) return this.text;

				// 根据类型返回默认文本
				const texts = {
					order: '暂无订单数据',
					cart: '购物车是空的',
					search: '没有找到相关内容',
					network: '网络连接失败'
				};

				return texts[this.type] || '暂无数据';
			}
		},
		methods: {
			handleAction() {
				this.$emit('action');
			}
		}
	};
</script>

<style scoped lang="scss">
	.t-empty {
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 60rpx 40rpx;
		width: 100%;
		box-sizing: border-box;

		&__content {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
		}

		&__image {
			width: 360rpx;
			height: 360rpx;
			margin-bottom: 32rpx;
			opacity: 0.6;
		}

		&__text {
			font-size: 28rpx;
			color: #909399;
			text-align: center;
			line-height: 1.5;
			margin-bottom: 40rpx;
		}

		&__action {
			margin-top: 20rpx;
		}

		&__button {
			padding: 20rpx 48rpx;
			background-color: #2979ff;
			color: white;
			border-radius: 12rpx;
			font-size: 28rpx;

			&::after {
				border: none;
			}

			&:active {
				background-color: #2d8cf0;
			}
		}
	}
</style>