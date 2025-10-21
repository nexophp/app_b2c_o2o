<template>
	<view class="xh-privacy">
		<!-- 默认主题 -->
		<view :style="'background:'+background+';'" class="theme theme-normal" v-if="show_privacy && theme == 'normal'">
			<view class="theme-content">
				<view class="title">{{ title }}</view>
				<view class="des">
					在使用当前小程序服务之前，请仔细阅读<text :style="'color:'+color+';'" class="link"
						@click="onOpenPrivacy">{{PrivacyName}}</text>。如你同意{{PrivacyName}}，请点击“同意”开始使用。
				</view>
				<view class="btns">
					<button class="item reject" @click="onExitPrivacy">拒绝</button>
					<button id="agree-btn" :style="'background:'+color+';'" class="item agree" open-type="agreePrivacyAuthorization"
						v-on:agreeprivacyauthorization="onHandleAgree">同意</button>
				</view>
			</view>
		</view>
		<!-- 纵向主题 -->
		<view :style="'background:'+background" class="theme theme-direction" v-if="show_privacy && theme == 'direction'">
			<view class="theme-content">
				<view class="title">{{ title }}</view>
				<view class="des">
					在使用当前小程序服务之前，请仔细阅读<text :style="'color:'+color+';'" class="link"
						@click="onOpenPrivacy">{{PrivacyName}}</text>。如你同意{{PrivacyName}}，请点击“同意”开始使用。
				</view>
				<view class="btns">
					<button id="agree-btn" :style="'background:'+color+';'" class="item agree"
						open-type="agreePrivacyAuthorization" v-on:agreeprivacyauthorization="onHandleAgree">同意</button>
					<button class="item reject" @click="onExitPrivacy">拒绝</button>
				</view>
			</view>
		</view>
		<!-- 后续会增加更多好看的主题 -->
		<view class="theme-content">
		</view>
	</view>
	</view>
</template>
<script>
	export default {
		name: "xh-privacy",
		props: {
			background: {
				type: String,
				default: 'rgba(0, 0, 0, .5)'
			},
			color: {
				type: String,
				default: '#07c160'
			},
			theme: {
				type: String,
				default: 'normal'
			},
			title: {
				type: String,
				default: '隐私保护指引'
			}
		},
		data() {
			return {
				show_privacy: false,
				PrivacyName: ""
			}
		},
		mounted() {
			let _this = this
			wx.getPrivacySetting({
				success(res) {
					console.log('弹出授权协议',res.errMsg)
					if (res.errMsg == "getPrivacySetting:ok") {
						// 弹出隐私授权协议
						_this.show_privacy = res.needAuthorization
					}
					_this.PrivacyName = res.privacyContractName
				}
			})
		},
		methods: {
			// 拒绝隐私协议
			onExitPrivacy() {
				uni.showToast({
					title: '必须同意后才可以继续使用当前小程序',
					icon: 'none'
				})
			},
			// 打开隐私协议
			onOpenPrivacy() {
				wx.openPrivacyContract({
					fail: () => {
						wx.showToast({
							title: '遇到错误',
							icon: 'error'
						})
					}
				})
			},
			// 同意隐私协议
			onHandleAgree() {
				let _this = this
				_this.show_privacy = false
			}
		}
	}
</script>
<style lang="scss">
	// 纵向主题
	.theme-direction {
		.theme-content {
			.title {
				text-align: center;
				color: #333;
				font-weight: bold;
				font-size: 32rpx;
			}

			.des {
				font-size: 26rpx;
				color: #666;
				margin-top: 40rpx;
				text-align: justify;
				line-height: 1.6;

				.link {
					text-decoration: underline;
				}
			}


			.btns {
				margin-top: 48rpx;
				display: flex;
				flex-direction: column;

				.item {
					justify-content: space-between;
					width: 244rpx;
					height: 80rpx;
					display: flex;
					align-items: center;
					justify-content: center;
					border-radius: 16rpx;
					box-sizing: border-box;
					border: none;
				}

				.reject {
					width: 100%;
					background: #f4f4f5;
					color: #909399;
					margin-top: 15rpx;
				}

				.agree {
					width: 100%;
					color: #fff;
				}
			}
		}
	}

	// 默认主题1

	.theme-normal {
		.theme-content {
			.title {
				text-align: center;
				color: #333;
				font-weight: bold;
				font-size: 32rpx;
			}

			.des {
				font-size: 26rpx;
				color: #666;
				margin-top: 40rpx;
				text-align: justify;
				line-height: 1.6;

				.link {
					color: #07c160;
					text-decoration: underline;
				}
			}


			.btns {
				margin-top: 48rpx;
				display: flex;

				.item {
					justify-content: space-between;
					width: 244rpx;
					height: 80rpx;
					display: flex;
					align-items: center;
					justify-content: center;
					border-radius: 16rpx;
					box-sizing: border-box;
					border: none;
				}

				.reject {
					background: #f4f4f5;
					color: #909399;
				}

				.agree {
					background: #07c160;
					color: #fff;
				}
			}
		}
	}

	.theme {
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		z-index: 9999999;
		display: flex;
		align-items: center;
		justify-content: center;

		.theme-content {
			width: 632rpx;
			padding: 48rpx;
			box-sizing: border-box;
			background: #fff;
			border-radius: 16rpx;
		}
	}

	/* 无样式button */
	.btn-normal {
		display: block;
		margin: 0;
		padding: 0;
		line-height: normal;
		background: none;
		border-radius: 0;
		box-shadow: none;
		border: none;
		font-size: unset;
		text-align: unset;
		overflow: visible;
		color: inherit;
	}

	.btn-normal:after {
		border: none;
	}

	.btn-normal.button-hover {
		color: inherit;
	}

	button:after {
		content: none;
		border: none;
	}
</style>