<template>
	<view class="submit-page">
		<!-- 余额信息 -->
		<view class="balance-info">
			<text class="balance-label">可提现余额</text>
			<text class="balance-amount">¥{{ availableBalance }}</text>
		</view>

		<!-- 提现表单 -->
		<view class="form-section">
			<!-- 提现金额 -->
			<view class="form-item">
				<text class="form-label">提现金额</text>
				<view class="amount-input">
					<text class="currency">¥</text>
					<input class="input" type="digit" placeholder="请输入提现金额" v-model="withdrawAmount"
						@input="onAmountInput" />
				</view>
				<view class="quick-amounts">
					<text class="quick-amount" v-for="(amount, index) in quickAmounts" :key="index"
						@click="selectQuickAmount(amount)">
						{{ amount === 'all' ? '全部' : '¥' + amount }}
					</text>
				</view>
			</view>

			<!-- 提现方式 -->
			<view class="form-item">
				<text class="form-label">提现方式</text>
				<view class="withdraw-methods">
					<view class="method-item" :class="{ active: selectedMethod === method.value }"
						v-for="(method, index) in withdrawMethods" :key="index" @click="selectMethod(method.value)">
						<image :src="method.icon" class="method-icon"></image>
						<text class="method-name">{{ method.name }}</text>
						<text class="method-desc">{{ method.desc }}</text>
					</view>
				</view>
			</view>

			<!-- 银行卡信息 -->
			<view class="form-item" v-if="selectedMethod === 'bank'">
				<text class="form-label">银行卡信息</text>
				<view class="bank-info">
					<input class="input" placeholder="请输入银行卡号" v-model="account.bank_card" />
					<input class="input" placeholder="请输入开户行" v-model="account.bank_name" />
					<input class="input" placeholder="请输入持卡人姓名" v-model="account.card_holder" />
				</view> 
			</view>

			<!-- 支付宝信息 -->
			<view class="form-item" v-if="selectedMethod === 'alipay'">
				<text class="form-label">支付宝信息</text>
				<view class="alipay-info">
					<input class="input" placeholder="请输入支付宝账号" v-model="account.account" />
					<input class="input" placeholder="请输入真实姓名" v-model="account.realname" />
				</view>
			</view>
		</view>

		<!-- 费用说明 -->
		<view class="fee-info">
			<view class="fee-item">
				<text class="fee-label">提现金额：</text>
				<text class="fee-value">¥{{ withdrawAmount || '0.00' }}</text>
			</view>
			<view class="fee-item">
				<text class="fee-label">手续费：</text>
				<text class="fee-value">¥{{ feeAmount }}</text>
			</view>
			<view class="fee-item total">
				<text class="fee-label">实际到账：</text>
				<text class="fee-value">¥{{ actualAmount }}</text>
			</view>
		</view>

		<!-- 提现说明 -->
		<view class="notice-section">
			<text class="notice-title">提现说明</text>
			<view class="notice-list">
				<text class="notice-item">• 提现金额最低10元，最高单次5000元</text>
				<text class="notice-item">• 工作日提现1-3个工作日到账</text>
				<text class="notice-item">• 节假日提现顺延至工作日处理</text>
			</view>
		</view>

		<!-- 提交按钮 -->
		<view class="submit-section">
			<button class="submit-btn" :class="{ disabled: !canSubmit }" @click="submitWithdraw" :disabled="!canSubmit">
				申请提现
			</button>
		</view>
	</view>
</template>

<script>
var _this
export default {
	data() {
		return {
			availableBalance: '',
			withdrawAmount: '',
			selectedMethod: 'weixin',
			account: {
				// 微信提现信息
				openid: '',
				// 银行卡提现信息
				bank_card: '',
				bank_name: '',
				card_holder: '',
				// 支付宝提现信息
				alipay_account: '',
				alipay_realname: ''
			},
			quickAmounts: ['100', '200', '500', 'all'],
			withdrawMethods: [
				{
					value: 'weixin',
					name: '微信',
					icon: '/static/weixin.png'
				},
				{
					value: 'bank',
					name: '银行卡',
					icon: '/static/bank.png'
				}
			],
			wallet_rate: 0,
			wallet_min: 0
		}
	},
	computed: {
		feeAmount() {
			if (!this.withdrawAmount || this.wallet_rate === 0) return '0.00'
			const amount = parseFloat(this.withdrawAmount) || 0
			const fee = amount * this.wallet_rate / 1000
			return Math.max(fee, 0.01).toFixed(2)
		},
		actualAmount() {
			if (!this.withdrawAmount) return '0.00'
			const amount = parseFloat(this.withdrawAmount) || 0
			const fee = parseFloat(this.feeAmount) || 0
			return (amount - fee).toFixed(2)
		},
		canSubmit() {
			if (!this.withdrawAmount) return false

			const amount = parseFloat(this.withdrawAmount)
			if (amount < this.wallet_min) return false
			if (amount > parseFloat(this.availableBalance)) return false

			if (this.selectedMethod === 'bank') {
				return this.account.bank_card && this.account.bank_name && this.account.card_holder
			}
			if (this.selectedMethod === 'weixin') {
				return uni.getStorageSync('openid')
			}
			return false
		}
	},
	onLoad() {
		_this = this
		this.get_stat()
		this.account.openid = uni.getStorageSync('openid') || ''
	},
	methods: {
		get_stat() {
			this.ajax(this.config.wallet.index, {}).then(res => {
				this.availableBalance = res.data.can_out_amount || 0
			})
			this.ajax(this.config.config, {}).then(res => {
				this.wallet_rate = parseFloat(res.data.wallet_rate) || 0
				this.wallet_min = parseFloat(res.data.wallet_min) || 0
				this.updateNotice()
			})
		},
		updateNotice() {
			this.noticeItems = [
				`• 提现金额最低${this.wallet_min}元`,
				'• 工作日提现1-3个工作日到账',
				'• 节假日提现顺延至工作日处理',
				`• 手续费率为${this.wallet_rate / 10}%`
			]
		},
		onAmountInput(e) {
			let value = e.detail.value
			if (value.includes('.')) {
				const parts = value.split('.')
				if (parts[1] && parts[1].length > 2) {
					value = parts[0] + '.' + parts[1].substring(0, 2)
				}
			}
			this.withdrawAmount = value
		},
		selectQuickAmount(amount) {
			this.withdrawAmount = amount === 'all' ? this.availableBalance : amount
		},
		selectMethod(method) {
			this.selectedMethod = method
		},
		submitWithdraw() {
			if (!this.canSubmit) {
				let message = '请完善提现信息'
				const amount = parseFloat(this.withdrawAmount) || 0
				if (amount < this.wallet_min) message = `提现金额不能少于${this.wallet_min}元`
				else if (amount > parseFloat(this.availableBalance)) message = '提现金额不能超过可用余额'

				uni.showToast({ title: message, icon: 'none' })
				return
			}

			uni.showModal({
				title: '确认提现',
				content: `确认申请提现¥${this.withdrawAmount}吗？\n手续费：¥${this.feeAmount}\n实际到账：¥${this.actualAmount}`,
				success: (res) => res.confirm && this.doSubmit()
			})
		},
		doSubmit() {
			uni.showLoading({ title: '提交中...' })

			const postData = {
				amount: this.withdrawAmount,
				type: this.selectedMethod,
				account: this.account
			}

			this.ajax(this.config.wallet.do_out, postData).then(res => {
				uni.hideLoading()
				uni.showToast({ title: '提现申请已提交', icon: 'success' })
				setTimeout(() => uni.navigateBack(), 1500)
			})
		}
	}
}
</script>

<style scoped>
.submit-page {
	background: #f7f7f7;
	min-height: 100vh;
	padding-bottom: 100px;
}

.balance-info {
	background: #ffffff;
	padding: 25px 20px;
	color: #333;
	text-align: center;
	border-bottom: 1px solid #f0f0f0;
}

.balance-label {
	font-size: 14px;
	color: #666;
	margin-bottom: 8px;
	display: block;
}

.balance-amount {
	font-size: 28px;
	font-weight: bold;
	color: #ff6b35;
	display: block;
}

.form-section {
	padding: 15px;
}

.form-item {
	background: white;
	border-radius: 12px;
	padding: 18px;
	margin-bottom: 12px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.form-label {
	font-size: 15px;
	font-weight: 500;
	color: #333;
	margin-bottom: 15px;
	display: block;
}

.amount-input {
	display: flex;
	align-items: center;
	border: 1px solid #e5e5e5;
	border-radius: 8px;
	padding: 0 15px;
	margin-bottom: 15px;
	height: 48px;
	background: #fafafa;
}

.currency {
	font-size: 18px;
	color: #333;
	margin-right: 8px;
	font-weight: 500;
}

.input {
	flex: 1;
	height: 100%;
	font-size: 16px;
	border: none;
	background: transparent;
}

.quick-amounts {
	display: flex;
	gap: 10px;
	flex-wrap: wrap;
}

.quick-amount {
	padding: 6px 14px;
	border: 1px solid #e5e5e5;
	border-radius: 16px;
	font-size: 13px;
	color: #666;
	background: #f8f8f8;
}

.withdraw-methods {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.method-item {
	display: flex;
	align-items: center;
	padding: 12px;
	border: 1px solid #e5e5e5;
	border-radius: 8px;
	background: #fafafa;
}

.method-item.active {
	border-color: #ff6b35;
	background: #fff8f6;
}

.method-icon {
	width: 28px;
	height: 28px;
	margin-right: 12px;
}

.method-name {
	font-size: 15px;
	font-weight: 500;
	color: #333;
	margin-right: 8px;
}

.method-desc {
	font-size: 12px;
	color: #999;
	flex: 1;
}

.bank-info,
.alipay-info {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.bank-info .input,
.alipay-info .input {
	height: 48px;
	padding: 0 15px;
	border: 1px solid #e5e5e5;
	border-radius: 8px;
	font-size: 14px;
	background: #fafafa;
}

.fee-info {
	background: white;
	margin: 0 15px 15px;
	border-radius: 12px;
	padding: 18px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.fee-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 10px;
}

.fee-item:last-child {
	margin-bottom: 0;
}

.fee-item.total {
	padding-top: 12px;
	border-top: 1px solid #f0f0f0;
	font-weight: 500;
}

.fee-label {
	font-size: 14px;
	color: #666;
}

.fee-value {
	font-size: 14px;
	color: #333;
}

.fee-item.total .fee-value {
	color: #ff6b35;
	font-size: 16px;
	font-weight: 500;
}

.notice-section {
	background: white;
	margin: 0 15px 15px;
	border-radius: 12px;
	padding: 18px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.notice-title {
	font-size: 15px;
	font-weight: 500;
	color: #333;
	margin-bottom: 12px;
	display: block;
}

.notice-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.notice-item {
	font-size: 12px;
	color: #999;
	line-height: 1.5;
}

.submit-section {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	padding: 15px;
	background: white;
	border-top: 1px solid #f0f0f0;
	box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
}

.submit-btn {
	width: 100%;
	height: 48px;
	background: #ff6b35;
	color: white;
	border: none;
	border-radius: 8px;
	font-size: 16px;
	font-weight: 500;
}

.submit-btn.disabled {
	background: #e5e5e5;
	color: #999;
}
</style>