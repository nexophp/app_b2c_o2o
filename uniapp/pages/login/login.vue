<template>
	<view class="login-container">
		<view class="login-form">
			<!-- 手机号输入 -->
			<view class="form-item">
				<text class="form-label">手机号</text>
				<input class="input" type="number" placeholder="请输入手机号" v-model="formData.phone" maxlength="11"
					placeholder-style="color: #ccc;" />
			</view>

			<!-- 验证码输入 -->
			<view class="form-item">
				<text class="form-label">验证码</text>
				<view class="code-input">
					<input class="input" type="number" placeholder="请输入验证码" v-model="formData.code" maxlength="6"
						placeholder-style="color: #ccc;" />
					<button class="code-btn" :class="{ disabled: !canGetCode }" :disabled="!canGetCode"
						@click="getSmsCode">
						{{ codeBtnText }}
					</button>
				</view>
			</view>

			<!-- 登录按钮 -->
			<button class="login-btn" :disabled="!canLogin" @click="handleLogin">
				登录
			</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				formData: {
					phone: '',
					code: ''
				},
				codeBtnText: '获取验证码',
				countdown: 60,
				canGetCode: true,
				timer: null
			}
		},
		computed: {
			canLogin() {
				return /^1[3-9]\d{9}$/.test(this.formData.phone) &&
					/^\d{6}$/.test(this.formData.code)
			}
		},
		methods: {
			// 获取短信验证码
			getSmsCode() {
				if (!/^1[3-9]\d{9}$/.test(this.formData.phone)) {
					uni.showToast({
						title: '请输入正确的手机号',
						icon: 'none'
					})
					return
				}

				this.startCountdown()
				this.requestSmsCode()
			},

			// 请求发送验证码
			requestSmsCode() {
				uni.showLoading({
					title: '发送中...'
				})

				this.$http.post('/api/sms/send', {
					phone: this.formData.phone,
					type: 'login'
				}).then(res => {
					uni.showToast({
						title: '验证码已发送',
						icon: 'success'
					})
				}).catch(err => {
					uni.showToast({
						title: err.message || '发送失败',
						icon: 'none'
					})
					this.resetCountdown()
				}).finally(() => {
					uni.hideLoading()
				})
			},

			// 倒计时
			startCountdown() {
				this.canGetCode = false
				this.timer = setInterval(() => {
					this.countdown--
					this.codeBtnText = `${this.countdown}s`

					if (this.countdown <= 0) {
						this.resetCountdown()
					}
				}, 1000)
			},

			// 重置倒计时
			resetCountdown() {
				clearInterval(this.timer)
				this.countdown = 60
				this.codeBtnText = '获取验证码'
				this.canGetCode = true
			},

			// 处理登录
			handleLogin() {
				if (!this.canLogin) {
					uni.showToast({
						title: '请填写正确的手机号和验证码',
						icon: 'none'
					})
					return
				}

				uni.showLoading({
					title: '登录中...'
				})

				this.ajax(this.config.login.phone_code, {
					phone: this.formData.phone,
					code: this.formData.code
				}).then(res => {

					if (res.code == 0) {
						let data = res.data
						this.login_success(data)
					} else {
						uni.showToast({
							title: res.msg,
							icon: 'error'
						})
					}
					uni.hideLoading()
					setTimeout(()=>{
						this.jump('/pages/index/index')
					},1000)
				})
			}
		},

		beforeDestroy() {
			this.resetCountdown()
		}
	}
</script>

<style lang="scss">
	.login-container {
		padding: 60rpx 40rpx;
		min-height: 100vh;
		background-color: #f8f8f8;
	}

	.login-form {
		background-color: #fff;
		border-radius: 16rpx;
		padding: 40rpx;
		box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
	}

	.form-item {
		margin-bottom: 40rpx;
	}

	.form-label {
		display: block;
		font-size: 28rpx;
		color: #333;
		margin-bottom: 16rpx;
		font-weight: 500;
	}

	.input {
		width: 100%;
		height: 88rpx;
		padding: 0 24rpx;
		font-size: 28rpx;
		color: #333;
		background-color: #f5f5f5;
		border-radius: 8rpx;
		border: 1rpx solid #eee;
		box-sizing: border-box;
	}

	.code-input {
		display: flex;
		align-items: center;

		.input {
			flex: 1;
			margin-right: 20rpx;
		}
	}

	.code-btn {
		width: 200rpx;
		height: 88rpx;
		line-height: 88rpx;
		font-size: 28rpx;
		color: #007AFF;
		background-color: #fff;
		border: 1rpx solid #007AFF;
		border-radius: 8rpx;

		&.disabled {
			color: #999;
			border-color: #e5e5e5;
			background-color: #f5f5f5;
		}
	}

	.login-btn {
		width: 100%;
		height: 88rpx;
		line-height: 88rpx;
		font-size: 32rpx;
		color: #fff;
		background-color: #007AFF;
		border-radius: 8rpx;
		margin-top: 60rpx;

		&[disabled] {
			background-color: #e5e5e5;
			color: #999;
		}
	}
</style>