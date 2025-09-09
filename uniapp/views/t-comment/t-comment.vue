<template>
	<view class="comment-container">
		<!-- 评论标题和查看更多 -->
		<view class="comment-header-section">
			<text class="comment-section-title">商品评论</text>
			<text class="view-more-btn" @click="viewAllComments">查看全部</text>
		</view>
		
		<!-- 评论列表 -->
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
					<!-- <view class="comment-actions">
						<text class="reply-btn" @click="showReplyModal(comment)">回复</text>
					</view> -->
					
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
		
		 
	</view>
</template>

<script>
var _this; 
export default {
	name: 't-comment', 
	props: {
		nid: {
			type: [Number, String],
			required: true
		},
		type: {
			type: String,
			default: 'product'
		}
	},
	data() {
		return {
			comments: [],
			loading: false,
			total: 0,
			per_page: 3,
			
			// 评论输入
			commentText: '',
			commentImages: [],
			
			// 回复相关
			replyText: '',
			replyImages: [],
			currentComment: null,
			currentReply: null,
			showReplyPopup: false,
			

		}
	},
	mounted() { 
		_this = this;
		this.loadComments();
	},
	methods: {
		
		// 查看全部评论
		viewAllComments() {
			uni.navigateTo({
				url: `/pages/comment/comment?nid=${this.nid}&type=${this.type}`
			});
		},
		
		// 加载评论列表
		loadComments() { 
			
			this.loading = true;
			
			const params = {
				nid: this.nid,
				type: this.type,
				per_page: this.per_page
			};
			
			_this.ajax(_this.config.comment.index, params).then(res => {
				this.loading = false;  
				if (res.total > 0) {  
					this.comments = res.data || [];
					this.total = res.data.total || 0;
				} else {
					console.error('获取评论失败:', res.msg);
				}
			});
		},
		  
		
		// 预览图片
		previewImage(images, current) {
			uni.previewImage({
				current: current,
				urls: images
			});
		},
		
		 
	}
}
</script>

<style scoped>
.comment-container {
	padding: 20rpx;
	background-color: #fff;
	margin-top: 10rpx; 
}

/* 评论标题区域 */
.comment-header-section {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20rpx 0;
	border-bottom: 1rpx solid #f0f0f0;
	margin-bottom: 20rpx;
}

.comment-section-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #333;
}

.view-more-btn {
	font-size: 28rpx;
	color: #007aff;
}

/* 评论列表样式 */
.comment-list {
	padding-bottom: 20rpx;
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
