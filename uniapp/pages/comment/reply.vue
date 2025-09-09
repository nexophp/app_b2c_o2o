<template>
	<view class="reply-page">
		<!-- 顶部导航栏 -->
		<view class="nav-bar">
			<view class="nav-back" @click="goBack">
				<uni-icons type="left" size="30"></uni-icons>
			</view>
			<view class="nav-title">商品评论</view>
			<view class="nav-placeholder"></view>
		</view>
		
		<!-- 评论列表 -->
		<mescroll-uni ref="mescrollRef" @init="mescrollInit" @down="downCallback" @up="upCallback" :up="upOption" :down="downOption">
			<view class="comment-list">
				<view v-if="comments.length === 0" class="empty-comment">
					<text class="empty-text">暂无评论</text>
				</view>
				
				<view v-for="(comment, index) in comments" :key="comment.id" class="comment-item">
					<!-- 评论头部 -->
					<view class="comment-header">
						<image class="user-avatar" :src="comment.user_avatar || '/static/default-avatar.png'" mode="aspectFill"></image>
						<view class="user-info">
							<text class="user-name">{{ comment.user_name || '匿名用户' }}</text>
							<text class="comment-time">{{ comment.created_at_text }}</text>
						</view>
					</view>
					
					<!-- 评论内容 -->
					<view class="comment-content">
						<text class="content-text">{{ comment.content }}</text>
						
						<!-- 评论图片 -->
						<view v-if="comment.images && comment.images.length > 0" class="comment-images">
							<image 
								v-for="(img, imgIndex) in comment.images" 
								:key="imgIndex" 
								class="comment-image" 
								:src="img" 
								mode="aspectFill"
								@click="previewImage(comment.images, imgIndex)"
							></image>
						</view>
					</view>
					
					<!-- 回复按钮 -->
					<view class="comment-actions">
						<text class="reply-btn" @click="showReplyModal(comment)">回复</text>
					</view>
					
					<!-- 回复列表 -->
					<view v-if="comment.replies && comment.replies.length > 0" class="reply-list">
						<view v-for="(reply, replyIndex) in comment.replies" :key="reply.id" class="reply-item">
							<view class="reply-header">
								<image class="reply-avatar" :src="reply.user_avatar || '/static/default-avatar.png'" mode="aspectFill"></image>
								<view class="reply-info">
									<text class="reply-user">{{ reply.user_name || '匿名用户' }}</text>
									<text v-if="reply.to_user_name" class="reply-to"> 回复 {{ reply.to_user_name }}</text>
									<text class="reply-time">{{ reply.created_at_text }}</text>
								</view>
							</view>
							
							<view class="reply-content">
								<text class="reply-text">{{ reply.content }}</text>
								
								<!-- 回复图片 -->
								<view v-if="reply.images && reply.images.length > 0" class="reply-images">
									<image 
										v-for="(img, imgIndex) in reply.images" 
										:key="imgIndex" 
										class="reply-image" 
										:src="img" 
										mode="aspectFill"
										@click="previewImage(reply.images, imgIndex)"
									></image>
								</view>
							</view>
							
							<view class="reply-actions">
								<text class="reply-reply-btn" @click="showReplyModal(comment, reply)">回复</text>
							</view>
						</view>
					</view>
				</view>
			</view>
		</mescroll-uni>
		
		<!-- 发表评论区域 -->
		<view class="comment-input-area">
			<view class="input-container">
				<textarea 
					v-model="commentText" 
					class="comment-input" 
					placeholder="写下你的评论..."
					maxlength="500"
					auto-height
				></textarea>
				
				<!-- 评论图片预览 -->
				<view v-if="commentImages.length > 0" class="image-preview">
					<view v-for="(img, index) in commentImages" :key="index" class="preview-item">
						<image class="preview-image" :src="img" mode="aspectFill"></image>
						<text class="delete-image" @click="removeCommentImage(index)">×</text>
					</view>
				</view>
				
				<view class="input-actions">
					<text class="add-image-btn" @click="chooseCommentImage">📷</text>
					<button class="submit-btn" @click="submitComment" :disabled="!commentText.trim()">发表</button>
				</view>
			</view>
		</view>
		
		<!-- 回复弹窗 -->
		<view v-if="showReplyPopup" class="reply-popup-mask" @click="closeReplyModal">
			<view class="reply-modal" @click.stop>
				<view class="modal-header">
					<text class="modal-title">回复评论</text>
					<text class="close-btn" @click="closeReplyModal">×</text>
				</view>
				
				<view class="modal-content">
					<textarea 
						v-model="replyText" 
						class="reply-input" 
						placeholder="写下你的回复..."
						maxlength="500"
						auto-height
					></textarea>
					
					<!-- 回复图片预览 -->
					<view v-if="replyImages.length > 0" class="image-preview">
						<view v-for="(img, index) in replyImages" :key="index" class="preview-item">
							<image class="preview-image" :src="img" mode="aspectFill"></image>
							<text class="delete-image" @click="removeReplyImage(index)">×</text>
						</view>
					</view>
					
					<view class="modal-actions">
						<text class="add-image-btn" @click="chooseReplyImage">📷</text>
						<button class="submit-reply-btn" @click="submitReply" :disabled="!replyText.trim()">发表回复</button>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
var _this;
import MescrollMixin from "@/uni_modules/mescroll-uni/components/mescroll-uni/mescroll-mixins.js";
 

export default {
	name: 'reply',
	mixins: [MescrollMixin],
	data() {
		return {
			nid: '',
		type: 'product',
		comments: [],
		loading: false,
		total: 0,
		per_page: 10,
		where: {
			page: 1,
			page_size: 10
		},
			
			// 评论输入
			commentText: '',
			commentImages: [],
			
			// 回复相关
			replyText: '',
			replyImages: [],
			currentComment: null,
			currentReply: null,
			showReplyPopup: false,
			
			// mescroll配置
			upOption: {
				page: {
					num: 0,
					size: 10
				},
				noMoreSize: 5,
				textNoMore: '没有更多评论了',
				empty: {
					use: true,
					icon: '/static/empty.png',
					tip: '暂无评论'
				}
			},
			downOption: {
				use: true,
				auto: false
			}
		}
	},
	onLoad(options) {
		_this = this;
		if (options.nid) {
			this.nid = options.nid;
		}
		if (options.type) {
			this.type = options.type;
		}
		this.loadComments();
	},
	methods: {
		// mescroll初始化
		mescrollInit(mescroll) {
			this.mescroll = mescroll;
		},
		
		// 上拉加载的回调
		upCallback(page) {
			this.where.page = page.num;
			this.loadComments();
		},
		
		// 下拉刷新的回调
		downCallback() {
			this.where.page = 1;
			this.loadComments();
			this.mescroll.resetUpScroll();
		},
		
		// 返回上一页
		goBack() {
			uni.navigateBack();
		},
		
		// 加载评论列表
		loadComments() {
			let w = this.where;
			const params = {
				nid: this.nid,
				type: this.type,
				page: w.page,
				per_page: w.page_size
			};
			
			_this.ajax(_this.config.comment.index, params).then(res => {
				if (res.current_page == 1) {
					_this.comments = [];
				}
				
				if (res.data && res.data.length > 0) {
					res.data.forEach(comment => {
						_this.comments.push(comment);
					});
				}
				
				_this.total = res.total || 0;
				
				// 结束加载
				_this.mescroll.endBySize(res.per_page, res.total);
			}).catch(error => {
				console.error('加载评论失败:', error);
				_this.toast('加载评论失败');
				_this.mescroll.endErr();
			});
		},
		
		// 选择评论图片
		chooseCommentImage() {
			uni.chooseImage({
				count: 9 - this.commentImages.length,
				sizeType: ['compressed'],
				sourceType: ['album', 'camera'],
				success: (res) => {
					this.commentImages = this.commentImages.concat(res.tempFilePaths);
				}
			});
		},
		
		// 删除评论图片
		removeCommentImage(index) {
			this.commentImages.splice(index, 1);
		},
		
		// 选择回复图片
		chooseReplyImage() {
			uni.chooseImage({
				count: 9 - this.replyImages.length,
				sizeType: ['compressed'],
				sourceType: ['album', 'camera'],
				success: (res) => {
					this.replyImages = this.replyImages.concat(res.tempFilePaths);
				}
			});
		},
		
		// 删除回复图片
		removeReplyImage(index) {
			this.replyImages.splice(index, 1);
		},
		
		// 预览图片
		previewImage(images, current) {
			uni.previewImage({
				current: current,
				urls: images
			});
		},
		
		// 发表评论
		submitComment() {
			if (!this.commentText.trim()) {
				_this.toast('请输入评论内容');
				return;
			}
			
			uni.showLoading({ title: '发布中...' });
			
			const params = {
				nid: this.nid,
				type: this.type,
				content: this.commentText,
				images: JSON.stringify(this.commentImages)
			};
			
			_this.ajax(_this.config.comment.do_comment, params).then(res => {
				uni.hideLoading();
				_this.toast('评论发布成功');
				
				// 清空输入
				this.commentText = '';
				this.commentImages = [];
				
				// 刷新评论列表
				this.where.page = 1;
				this.loadComments();
				this.mescroll.resetUpScroll();
			}).catch(error => {
				uni.hideLoading();
				console.error('发布评论失败:', error);
				_this.toast('发布评论失败');
			});
		},
		
		// 显示回复弹窗
		showReplyModal(comment, reply = null) {
			this.currentComment = comment;
			this.currentReply = reply;
			this.replyText = '';
			this.replyImages = [];
			this.showReplyPopup = true;
		},
		
		// 关闭回复弹窗
		closeReplyModal() {
			this.showReplyPopup = false;
			this.currentComment = null;
			this.currentReply = null;
		},
		
		// 发表回复
		submitReply() {
			if (!this.replyText.trim()) {
				_this.toast('请输入回复内容');
				return;
			}
			
			if (!this.currentComment) {
				_this.toast('回复失败，请重试');
				return;
			}
			
			uni.showLoading({ title: '发布中...' });
			
			const params = {
				comment_id: this.currentComment.id,
				content: this.replyText,
				images: JSON.stringify(this.replyImages)
			};
			
			// 如果是回复某个回复
			if (this.currentReply) {
				params.reply_id = this.currentReply.id;
				params.to_user_id = this.currentReply.user_id;
			}
			
			_this.ajax(_this.config.comment.do_reply, params).then(res => {
				uni.hideLoading();
				_this.toast('回复发布成功');
				
				// 关闭弹窗
				this.closeReplyModal();
				
				// 刷新评论列表
				this.where.page = 1;
				this.loadComments();
				this.mescroll.resetUpScroll();
			}).catch(error => {
				uni.hideLoading();
				console.error('发布回复失败:', error);
				_this.toast('发布回复失败');
			});
		}
	}
}
</script>

<style scoped>
.reply-page {
	background-color: #f8f8f8;
	min-height: 100vh;
	padding-bottom: 120rpx;
}

/* 导航栏样式 */
.nav-bar {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	height: calc(88rpx + var(--status-bar-height));
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: var(--status-bar-height) 30rpx 0 30rpx;
	background-color: #fff;
	border-bottom: 1rpx solid #e5e5e5;
	z-index: 1000;
	box-sizing: border-box;
}

.nav-back {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.nav-title {
	font-size: 32rpx;
	color: #333;
	font-weight: bold;
}

.nav-placeholder {
	width: 60rpx;
}

/* 评论列表样式 */
.comment-list {
	padding: calc(88rpx + var(--status-bar-height) + 20rpx) 20rpx 200rpx 20rpx;
}

.empty-comment {
	text-align: center;
	padding: 100rpx 0;
}

.empty-text {
	color: #999;
	font-size: 28rpx;
}

.comment-item {
	background-color: #fff;
	margin-bottom: 20rpx;
	padding: 30rpx;
	border-radius: 16rpx;
}

.comment-header {
	display: flex;
	align-items: center;
	margin-bottom: 20rpx;
}

.user-avatar {
	width: 80rpx;
	height: 80rpx;
	border-radius: 50%;
	margin-right: 20rpx;
}

.user-info {
	flex: 1;
}

.user-name {
	font-size: 28rpx;
	font-weight: bold;
	color: #333;
	display: block;
	margin-bottom: 10rpx;
}

.comment-time {
	font-size: 24rpx;
	color: #999;
}

.comment-content {
	margin-bottom: 20rpx;
}

.content-text {
	font-size: 30rpx;
	color: #333;
	line-height: 1.6;
}

.comment-images {
	display: flex;
	flex-wrap: wrap;
	gap: 10rpx;
	margin-top: 20rpx;
}

.comment-image {
	width: 200rpx;
	height: 200rpx;
	border-radius: 8rpx;
}

.comment-actions {
	display: flex;
	justify-content: flex-end;
}

.reply-btn {
	font-size: 26rpx;
	color: #007aff;
	padding: 10rpx 20rpx;
}

/* 回复列表样式 */
.reply-list {
	margin-top: 20rpx;
	padding-left: 20rpx;
	border-left: 4rpx solid #e5e5e5;
}

.reply-item {
	margin-bottom: 20rpx;
	padding: 20rpx;
	background-color: #f5f5f5;
	border-radius: 12rpx;
}

.reply-header {
	display: flex;
	align-items: center;
	margin-bottom: 15rpx;
}

.reply-avatar {
	width: 60rpx;
	height: 60rpx;
	border-radius: 50%;
	margin-right: 15rpx;
}

.reply-info {
	flex: 1;
}

.reply-user {
	font-size: 26rpx;
	font-weight: bold;
	color: #333;
}

.reply-to {
	font-size: 26rpx;
	color: #007aff;
}

.reply-time {
	font-size: 22rpx;
	color: #999;
	display: block;
	margin-top: 5rpx;
}

.reply-content {
	margin-bottom: 15rpx;
}

.reply-text {
	font-size: 28rpx;
	color: #333;
	line-height: 1.5;
}

.reply-images {
	display: flex;
	flex-wrap: wrap;
	gap: 8rpx;
	margin-top: 15rpx;
}

.reply-image {
	width: 150rpx;
	height: 150rpx;
	border-radius: 6rpx;
}

.reply-actions {
	display: flex;
	justify-content: flex-end;
}

.reply-reply-btn {
	font-size: 24rpx;
	color: #007aff;
	padding: 8rpx 15rpx;
}

/* 评论输入区域样式 */
.comment-input-area {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	background-color: #fff;
	border-top: 1rpx solid #e5e5e5;
	padding: 20rpx;
	z-index: 100;
}

.input-container {
	width: 100%;
}

.comment-input {
	width: 100%;
	min-height: 80rpx;
	max-height: 200rpx;
	padding: 20rpx;
	border: 1rpx solid #e5e5e5;
	border-radius: 12rpx;
	font-size: 28rpx;
	background-color: #f8f8f8;
	margin-bottom: 20rpx;
}

.image-preview {
	display: flex;
	flex-wrap: wrap;
	gap: 10rpx;
	margin-bottom: 20rpx;
}

.preview-item {
	position: relative;
	width: 120rpx;
	height: 120rpx;
}

.preview-image {
	width: 100%;
	height: 100%;
	border-radius: 8rpx;
}

.delete-image {
	position: absolute;
	top: -10rpx;
	right: -10rpx;
	width: 40rpx;
	height: 40rpx;
	background-color: #ff4757;
	color: #fff;
	border-radius: 50%;
	font-size: 24rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.input-actions {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.add-image-btn {
	font-size: 40rpx;
	padding: 10rpx;
}

.submit-btn {
	background-color: #007aff;
	color: #fff;
	border: none;
	padding: 15rpx 40rpx;
	border-radius: 8rpx;
	font-size: 28rpx;
}

.submit-btn:disabled {
	background-color: #ccc;
}

/* 回复弹窗样式 */
.reply-popup-mask {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.5);
	z-index: 1000;
	display: flex;
	align-items: flex-end;
}

.reply-modal {
	background-color: #fff;
	border-radius: 20rpx 20rpx 0 0;
	padding: 40rpx;
	max-height: 80vh;
	width: 100%;
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 30rpx;
	padding-bottom: 20rpx;
	border-bottom: 1rpx solid #e5e5e5;
}

.modal-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #333;
}

.close-btn {
	font-size: 40rpx;
	color: #999;
	padding: 10rpx;
}

.modal-content {
	width: 100%;
}

.reply-input {
	width: 100%;
	min-height: 120rpx;
	max-height: 300rpx;
	padding: 20rpx;
	border: 1rpx solid #e5e5e5;
	border-radius: 12rpx;
	font-size: 28rpx;
	background-color: #f8f8f8;
	margin-bottom: 20rpx;
}

.modal-actions {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-top: 30rpx;
}

.submit-reply-btn {
	background-color: #007aff;
	color: #fff;
	border: none;
	padding: 15rpx 40rpx;
	border-radius: 8rpx;
	font-size: 28rpx;
}

.submit-reply-btn:disabled {
	background-color: #ccc;
}
</style>
