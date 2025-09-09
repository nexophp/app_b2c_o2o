<template>
	<!-- 完全保持原有template结构不变 -->
	<view class="container"> 
		<view class="invite-methods">
			<view class="section-title">邀请方式</view>
			<view class="method-list">
				<button class="method-item" open-type="share">
					<image src="/static/weixin.png" class="method-icon"></image>
					<text>微信好友</text>
				</button>
				<view class="method-item" @click="generatePoster">
					<image src="/static/poster.png" class="method-icon"></image>
					<text>生成海报</text>
				</view> 
			</view>
		</view>
		
		<view class="poster-section" v-if="showPoster">
			<view class="section-title">专属邀请海报</view>
			<view class="poster-box">
				<image :src="posterUrl" style="height:200px;" mode="aspectFit" class="poster-image method-icon"></image>
				<button class="save-btn" @click="savePoster">保存海报</button>
			</view>
		</view>
		<mescroll-body top="0" :up="upOption" :down="downOption" @init="mescrollInit" @down="downCallback"
			@up="upCallback">
		<view class="invite-records">
			<view class="section-title">邀请记录</view>
			<view class="record-list">
				<view class="record-item" v-for="(item, index) in list"  >
					<image :src="item.avatar" class="record-avatar"></image>
					<view class="record-info">
						<view class="record-name">{{item.user_name}}</view>
						<view class="record-time">{{item.created_at_text}}</view>
					</view> 
				</view>
			</view> 
		</view>
		</mescroll-body>
	</view>
</template>

<script>
	var _this
	import MescrollMixin from "@/uni_modules/mescroll-uni/components/mescroll-uni/mescroll-mixins.js";
	
	export default {
		mixins: [MescrollMixin],
		data() {
			return {
				inviteCode: 'WX' + Math.floor(Math.random() * 9000 + 1000),
				posterUrl: '',
				showPoster: false,
				inviteList: [ 
				],
				hasMore: true,
				where: {
					page: 1,
					page_size: 10,
					status: ''
				},
				upOption: {
					page: {
						size: 10 // 每页数据的数量,默认10
					},
					noMoreSize: 5, // 配置列表的总数量要大于等于5条才显示'-- END --'的提示
					empty: {
						tip: '暂无订单数据'
					},
					textNoMore: '-- 到底了 --'
				},
				downOption: {
					auto: false, //是否在初始化后,自动执行downCallback; 默认true 
				},
				list:[],
			}
		},
		onLoad() {
			_this = this
		},
		methods: {
			/*下拉刷新的回调 */
			downCallback() {
				this.where.page = 1
				this.load()
				this.mescroll.resetUpScroll();
			},
			upCallback(page) {
				this.where.page = page.num
				this.load()
			},
			reload() {
				this.where.page = 1
				this.load()
			},
			load() {
				let w = this.where
				_this.ajax(_this.config.share_user.index, w).then(res => {
					if (res.current_page == 1) {
						_this.list = []
					}
					for (let i in res.data) { 
						_this.list.push(res.data[i] )
					}
					_this.mescroll.endBySize(res.per_page, res.total);
				})
			},
			copyCode() {
				const shareUrl = this.create_share_url('/pages/index/index');
				uni.setClipboardData({
					data: shareUrl,
					success: () => {
						uni.showToast({
							title: '邀请码已复制',
							icon: 'success'
						});
					}
				});
			},
			copyLink() {
				const shareUrl = this.create_share_url('/pages/index/index');
				uni.setClipboardData({
					data: shareUrl,
					success: () => {
						uni.showToast({
							title: '链接已复制',
							icon: 'success'
						});
					}
				});
			},
			create_share_url(path) {
				return `${path}?inviteCode=${this.inviteCode}`;
			},
			generatePoster() {
				uni.showLoading({
					title: '海报生成中'
				});
				
				setTimeout(() => {
					this.posterUrl = '/static/images/invite-poster.jpg';
					this.showPoster = true;
					uni.hideLoading();
				}, 1000);
			},
			savePoster() {
				uni.saveImageToPhotosAlbum({
					filePath: this.posterUrl,
					success: () => {
						uni.showToast({
							title: '海报保存成功',
							icon: 'success'
						});
					},
					fail: (err) => {
						console.log(err);
						uni.showToast({
							title: '保存失败，请重试',
							icon: 'none'
						});
					}
				});
			}, 
		},
		onShareAppMessage() {
			return {
				title: '邀请您加入我们，一起赚佣金！',
				path: this.create_share_url('/pages/index/index'),
				imageUrl: '/static/images/share-logo.jpg'
			}
		}
	}
</script>

<style lang="scss">
	/* 优化后的样式 */
	.container {
		padding: 24rpx;
		background-color: #f8f8f8;
		min-height: 100vh;
	}
	
	.section-title {
		font-size: 32rpx;
		font-weight: 600;
		color: #333;
		margin-bottom: 24rpx;
		padding-left: 12rpx;
		border-left: 6rpx solid #FF5F6D;
		line-height: 1.2;
	}
	
	.invite-methods {
		background-color: #fff;
		padding: 32rpx;
		border-radius: 16rpx;
		margin-bottom: 24rpx;
		box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.04);
		
		.method-list {
			display: flex;
			justify-content: space-around;
			padding-top: 16rpx;
			
			.method-item {
				display: flex;
				flex-direction: column;
				align-items: center;
				width: 40%;
				padding: 24rpx 0;
				background: #f9f9f9;
				border-radius: 12rpx;
				
				.method-icon {
					width: 80rpx;
					height: 80rpx;
					margin-bottom: 16rpx;
				}
				
				text {
					font-size: 28rpx;
					color: #666;
				}
			}
		}
	}
	
	.poster-section {
		background-color: #fff;
		padding: 32rpx;
		border-radius: 16rpx;
		margin-bottom: 24rpx;
		box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.04);
		
		.poster-box {
			display: flex;
			flex-direction: column;
			align-items: center;
			
			
			
			.save-btn {
				width: 80%;
				height: 80rpx;
				line-height: 80rpx;
				background: linear-gradient(to right, #FF5F6D, #FF8E53);
				color: #fff;
				font-size: 32rpx;
				border-radius: 40rpx;
				box-shadow: 0 4rpx 16rpx rgba(255, 95, 109, 0.3);
			}
		}
	}
	
	.invite-records {
		background-color: #fff;
		padding: 32rpx;
		border-radius: 16rpx;
		box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.04);
		
		.record-list {
			.record-item {
				display: flex;
				align-items: center;
				padding: 28rpx 0;
				border-bottom: 1rpx solid #f0f0f0;
				
				.record-avatar {
					width: 88rpx;
					height: 88rpx;
					border-radius: 50%;
					margin-right: 24rpx;
					background-color: #f5f5f5;
				}
				
				.record-info {
					flex: 1;
					
					.record-name {
						font-size: 30rpx;
						color: #333;
						margin-bottom: 8rpx;
						font-weight: 500;
					}
					
					.record-time {
						font-size: 24rpx;
						color: #999;
					}
				}
				
				.record-amount {
					font-size: 32rpx;
					color: #FF5F6D;
					font-weight: 600;
				}
			}
		}
		
		.view-more {
			text-align: center;
			color: #999;
			font-size: 28rpx;
			padding: 24rpx 0 8rpx;
		}
	}
</style>