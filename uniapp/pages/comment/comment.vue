<template>
	<view class="comment-container">
		<!-- 顶部导航栏 -->
		<view class="nav-bar">
			<view class="nav-back" @click="goBack">
				<uni-icons type="left" size="30"></uni-icons>
			</view>
			<view class="nav-title">评论</view>
			<view class="nav-right"></view>
		</view>

		<!-- 评论列表 -->
		<mescroll-uni  ref="mescrollRef" @init="mescrollInit" @down="downCallback" @up="upCallback" :up="upOption">
			<view class="comment-list" v-if="commentList && commentList.length > 0">
				<view v-for="(comment, index) in commentList" class="comment-item">
					<!-- 评论内容 -->
					<view class="comment-content">
						<view class="comment-header">
							<view class="user-info">
								<image class="user-avatar" :src="comment.user_avatar || '/static/default-avatar.png'" mode="aspectFill"></image>
								<text class="user-name">{{ comment.user_name || '匿名用户' }}</text>
							</view>
							<view class="comment-time">{{ comment.created_at_text }}</view>
						</view>
						<view class="comment-text">{{ comment.content }}</view>
						<!-- 评论图片 -->
						<view v-if="comment.images && comment.images.length > 0" class="comment-images">
							<image 
								v-for="(image, imgIndex) in comment.images"  
								:src="image" 
								class="comment-image" 
								mode="aspectFill"
								@click="previewImage(image, comment.images)"
							></image>
						</view>
						<!-- 操作按钮 -->
						<view class="comment-actions" v-if="has_buy">
							<view class="action-btn" @click="showReplyInput(comment)">
								<uni-icons type="chat" size="16"></uni-icons>
								<text>回复</text>
							</view>
						</view>


					</view>

					<!-- 回复列表 -->
					<view v-if="comment.reply && comment.reply.length > 0" class="reply-list">
						<view v-for="(reply, replyIndex) in comment.reply" :key="reply.id" class="reply-item">
							<view class="reply-header">
								<view class="user-info">
									<image class="user-avatar" :src="reply.user_avatar || '/static/default-avatar.png'" mode="aspectFill"></image>
									<text class="user-name">{{ reply.user_name || '匿名用户' }}</text>
									<text v-if="reply.to_user_name" class="reply-to">回复 {{ reply.to_user_name }}</text>
								</view>
								<view class="reply-time">{{ reply.created_at_text }}</view>
							</view>
							<view class="reply-text">{{ reply.content }}</view>
							<!-- 回复图片 -->
							<view v-if="reply.images && reply.images.length > 0" class="reply-images">
								<image 
									v-for="(image, imgIndex) in reply.images" 
									:key="imgIndex" 
									:src="image" 
									class="reply-image" 
									mode="aspectFill"
									@click="previewImage(image, reply.images)"
								></image>
							</view>
							<!-- <view class="reply-actions">
								<view class="action-btn" @click="showReplyInput(comment, reply)">
									<uni-icons type="chat" size="14"></uni-icons>
									<text>回复</text>
								</view>
							</view> -->
						</view>
					</view>
				</view>
			</view>
			<view v-else>
				<view class="no-comment">暂无评论</view>
			</view>
		</mescroll-uni>

		<!-- 底部评论输入框 -->
		<view class="comment-input-container" v-if="has_buy">
			<view class="comment-input-box">
				<input 
					v-model="commentText" 
					class="comment-input" 
					placeholder="写评论..." 
					@focus="onInputFocus"
					@blur="onInputBlur"
				/>
				<view class="input-actions">
					<view class="action-icon" @click="chooseImage">
						<uni-icons type="image" size="24"></uni-icons>
					</view>
					<view class="send-btn" @click="submitComment" :class="{active: commentText.trim()}">
						发送
					</view>
				</view>
			</view>
			<!-- 选中的图片预览 -->
			<view v-if="selectedImages.length > 0" class="selected-images">
				<view v-for="(image, index) in selectedImages" :key="index" class="selected-image-item">
					<image :src="image" class="selected-image" mode="aspectFill"></image>
					<view class="remove-image" @click="removeImage(index)">
						<uni-icons type="close" size="16" color="#fff"></uni-icons>
					</view>
				</view>
			</view>
		</view>

		<!-- 回复弹窗 -->
		<uni-popup ref="replyPopup" type="bottom">
			<view class="reply-popup">
				<view class="popup-header">
					<text class="popup-title">回复评论</text>
					<view class="close-btn" @click="closeReplyPopup">
						<uni-icons type="close" size="20"></uni-icons>
					</view>
				</view>
				<view class="reply-input-container">
					<textarea 
						v-model="replyText" 
						class="reply-textarea" 
						:placeholder="replyPlaceholder"
						auto-focus
					></textarea>
					<view class="reply-actions">
						<view class="action-icon" @click="chooseReplyImage">
							<uni-icons type="image" size="24"></uni-icons>
						</view>
						<view class="send-btn" @click="submitReply" :class="{active: replyText.trim()}">
							发送
						</view>
					</view>
				</view>
				<!-- 回复选中的图片预览 -->
				<view v-if="selectedReplyImages.length > 0" class="selected-images">
					<view v-for="(image, index) in selectedReplyImages" :key="index" class="selected-image-item">
						<image :src="image" class="selected-image" mode="aspectFill"></image>
						<view class="remove-image" @click="removeReplyImage(index)">
							<uni-icons type="close" size="16" color="#fff"></uni-icons>
						</view>
					</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
var _this;
import MescrollMixin from "@/uni_modules/mescroll-uni/components/mescroll-uni/mescroll-mixins.js";
 
export default {
	mixins: [MescrollMixin],
	data() {
		return {
			mescroll: null,
			where: {
				page: 1,
				page_size: 10
			},
			upOption: {
				page: {
					size: 10
				},
				noMoreSize: 5,
				empty: {
					tip: '暂无评论'
				},
				textNoMore: '-- 到底了 --'
			},
			commentList: [],
			commentText: '',
			selectedImages: [],
			replyText: '',
			selectedReplyImages: [],
			currentComment: null,
			currentReply: null,
			replyPlaceholder: '写回复...',
			nid: 0, // 关联ID，如商品ID
			type: '', // 评论类型，如product
			loading: false,
			image_type:'',
			has_buy:false,
		}
	},
	onLoad(options) {
		_this = this
		// 获取传入的参数
		this.nid = options.nid || 0
		this.type = options.type || 'product'
		
		// 设置导航栏标题
		uni.setNavigationBarTitle({
			title: '评论'
		})
		this.get_has_buy() 
	}, 
	methods: {
		after_upload(res){ 
			if(!res.http_url){
				return
			}
			if(this.image_type == 'comment'){
				this.selectedImages.push(res.http_url)
			}else{
				this.selectedReplyImages.push(res.http_url)
			}			
		},
		// mescroll初始化
		mescrollInit(mescroll) {
			this.mescroll = mescroll
		},
		// 下拉刷新
		downCallback() {
			this.where.page = 1
			this.load()
			this.mescroll.resetUpScroll();
		},
		// 上拉加载
		upCallback(page) {
			this.where.page = page.num
			this.load()
		},
		// 返回上一页
		goBack() {
			uni.navigateBack()
		},
		get_has_buy(){
			this.ajax(this.config.order.has_buy,{product_id:this.nid}).then(res=>{
				if(res.code == 0){
					this.has_buy = true 
				}else{
					this.has_buy = false 
				}
			})
		},
		// 加载评论列表
		load() { 
			let w = this.where
			const params = {
				nid: this.nid,
				type: this.type,
				page: w.page,
				per_page: w.page_size
			}
			
			_this.ajax(_this.config.comment.index, params).then(res => {
				if (res.current_page == 1) {
					_this.commentList = []
				}
				
				if (res.data && res.data.length > 0) {
					// 处理评论数据
					res.data.forEach(comment => {
						// 处理图片数组
						if (comment.images) {
							try {
								comment.images_array = JSON.parse(comment.images)
							} catch (e) {
								comment.images_array = []
							}
						} else {
							comment.images_array = []
						}
						
						// 处理回复数据
						if (comment.replies && comment.replies.length > 0) {
							comment.replies.forEach(reply => {
								if (reply.images) {
									try {
										reply.images_array = JSON.parse(reply.images)
									} catch (e) {
										reply.images_array = []
									}
								} else {
									reply.images_array = []
								}
							})
						}
						
						_this.commentList.push(comment)
					})
				}
				
				// 结束加载
				_this.mescroll.endBySize(res.per_page, res.total);
			}).catch(error => {
				console.error('加载评论失败:', error)
				_this.toast('加载评论失败')
				this.mescroll.endErr()
			})
		},
		// 选择图片
		chooseImage() {
			this.image_type = 'comment'
			uni.chooseImage({
				count: 9 - this.selectedImages.length,
				sizeType: ['compressed'],
				sourceType: ['album', 'camera'],
				success: (res) => {
					res.tempFilePaths.forEach(path => {
						this.upload_await(path,{}).then(r=>{ 
						}) 
					})
				}
			})
		},
		// 移除图片
		removeImage(index) {
			this.selectedImages.splice(index, 1)
		},
		// 选择回复图片
		chooseReplyImage() {
			this.image_type = 'reply'
			uni.chooseImage({
				count: 9 - this.selectedReplyImages.length,
				sizeType: ['compressed'],
				sourceType: ['album', 'camera'],
				success: (res) => {
					this.upload_await(path,{}).then(r=>{ 
					}) 
				}
			})
		},
		// 移除回复图片
		removeReplyImage(index) {
			this.selectedReplyImages.splice(index, 1)
		},
		// 预览图片
		previewImage(current, urls) {
			uni.previewImage({
				current: current,
				urls: urls
			})
		},
		// 显示回复输入框
		showReplyInput(comment, reply = null) {
			this.currentComment = comment
			this.currentReply = reply
			this.replyText = ''
			this.selectedReplyImages = []
			
			if (reply) {
				this.replyPlaceholder = `回复 ${reply.user_name || '匿名用户'}:`
			} else {
				this.replyPlaceholder = `回复 ${comment.user_name || '匿名用户'}:`
			}
			
			this.$refs.replyPopup.open()
		},
		// 关闭回复弹窗
		closeReplyPopup() {
			this.$refs.replyPopup.close()
			this.currentComment = null
			this.currentReply = null
			this.replyText = ''
			this.selectedReplyImages = []
		},
		// 输入框获得焦点
		onInputFocus() {
			// 可以在这里处理输入框获得焦点的逻辑
		},
		// 输入框失去焦点
		onInputBlur() {
			// 可以在这里处理输入框失去焦点的逻辑
		},
		// 提交评论
		submitComment() {
			if (!this.commentText.trim()) {
				_this.toast('请输入评论内容')
				return
			}
			
			uni.showLoading({ title: '发布中...' })
			
			const params = {
				nid: this.nid,
				type: this.type,
				content: this.commentText.trim(),
				images: JSON.stringify(this.selectedImages)
			}
			
				_this.ajax(_this.config.comment.do_comment, params).then(res => {
				uni.hideLoading()
				_this.toast('评论发布成功')
				
				// 清空输入
				this.commentText = ''
				this.selectedImages = []
				
				// 刷新评论列表
				this.where.page = 1
				this.load()
				this.mescroll.resetUpScroll()
			}).catch(error => {
				uni.hideLoading()
				console.error('发布评论失败:', error)
				_this.toast('发布评论失败')
			})
		},
		// 提交回复
		submitReply() {
			if (!this.replyText.trim()) {
				_this.toast('请输入回复内容')
				return
			}
			
			if (!this.currentComment) {
				_this.toast('回复失败，请重试')
				return
			}
			
			uni.showLoading({ title: '发布中...' })
			
			const params = {
				comment_id: this.currentComment.id,
				content: this.replyText.trim(),
				images: JSON.stringify(this.selectedReplyImages)
			}
			
			// 如果是回复某个回复，添加reply_id和to_user_id
			if (this.currentReply) {
				params.reply_id = this.currentReply.id
				params.to_user_id = this.currentReply.user_id
			}
			
				_this.ajax(_this.config.comment.do_reply, params).then(res => {
				uni.hideLoading()
				_this.toast('回复发布成功')
				
				// 关闭弹窗
				this.closeReplyPopup()
				
				// 刷新评论列表
				this.where.page = 1
				this.load()
				this.mescroll.resetUpScroll()
			}).catch(error => {
				uni.hideLoading()
				console.error('发布回复失败:', error)
				_this.toast('发布回复失败')
			})
		},
		// 输入框聚焦
		onInputFocus() {
			// 可以在这里处理键盘弹起的逻辑
		},
		// 输入框失焦
		onInputBlur() {
			// 可以在这里处理键盘收起的逻辑
		},
		// 返回上一页
		goBack() {
			uni.navigateBack()
		}
	}
}
</script>

<style>
.comment-container {
	background: #f5f5f5;
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
	background: #fff;
	border-bottom: 1px solid #f0f0f0;
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

.nav-right {
	width: 60rpx;
}

/* 评论列表样式 */
.comment-list {
	padding: calc(88rpx + var(--status-bar-height)) 0 0 0;
	/* #ifdef H5 */
	padding-top: 0;
	/* #endif */
}

.comment-item {
	background: #fff;
	margin-bottom: 20rpx;
	padding: 30rpx;
}

.comment-content {
	margin-bottom: 20rpx;
}

.comment-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
}

.user-info {
	display: flex;
	align-items: center;
}

.user-avatar {
	width: 60rpx;
	height: 60rpx;
	border-radius: 50%;
	margin-right: 20rpx;
}

.user-name {
	font-size: 28rpx;
	color: #333;
	font-weight: 500;
}

.comment-time {
	font-size: 24rpx;
	color: #999;
}

.comment-text {
	font-size: 30rpx;
	color: #333;
	line-height: 1.6;
	margin-bottom: 20rpx;
}

.comment-images {
	display: flex;
	flex-wrap: wrap;
	gap: 10rpx;
	margin-bottom: 20rpx;
}

.comment-image {
	width: 150rpx;
	height: 150rpx;
	border-radius: 8rpx;
}

.comment-actions {
	display: flex;
	align-items: center;
}

.action-btn {
	display: flex;
	align-items: center;
	gap: 8rpx;
	padding: 10rpx 20rpx;
	background: #f8f8f8;
	border-radius: 20rpx;
	font-size: 24rpx;
	color: #666;
	margin-right: 20rpx;
}

/* 回复列表样式 */
.reply-list {
	background: #f8f8f8;
	border-radius: 12rpx;
	padding: 20rpx;
	margin-left: 80rpx;
	z-index: 9999;
}

.reply-item {
	padding: 20rpx 0;
	border-bottom: 1px solid #f0f0f0;
}

.reply-item:last-child {
	border-bottom: none;
}

.reply-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 15rpx;
}

.reply-to {
	font-size: 24rpx;
	color: #007aff;
	margin-left: 10rpx;
}

.reply-time {
	font-size: 22rpx;
	color: #999;
}

.reply-text {
	font-size: 28rpx;
	color: #333;
	line-height: 1.5;
	margin-bottom: 15rpx;
}

.reply-images {
	display: flex;
	flex-wrap: wrap;
	gap: 8rpx;
	margin-bottom: 15rpx;
}

.reply-image {
	width: 120rpx;
	height: 120rpx;
	border-radius: 6rpx;
}

.reply-actions {
	display: flex;
	align-items: center;
}

.reply-actions .action-btn {
	padding: 8rpx 16rpx;
	font-size: 22rpx;
}

/* 底部输入框样式 */
.comment-input-container {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	background: #fff;
	border-top: 1px solid #f0f0f0;
	padding: 20rpx;
	padding-bottom: calc(20rpx + env(safe-area-inset-bottom));
	z-index: 999;
}

.comment-input-box {
	display: flex;
	align-items: center;
	gap: 20rpx;
	margin-bottom: 20rpx;
}

.comment-input {
	flex: 1;
	height: 70rpx;
	padding: 0 20rpx;
	background: #f8f8f8;
	border-radius: 35rpx;
	font-size: 28rpx;
	border: none;
}

.input-actions {
	display: flex;
	align-items: center;
	gap: 20rpx;
}

.action-icon {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #f8f8f8;
	border-radius: 50%;
	color: #666;
}

.send-btn {
	padding: 15rpx 30rpx;
	background: #ccc;
	color: #fff;
	border-radius: 30rpx;
	font-size: 28rpx;
	transition: background 0.3s;
}

.send-btn.active {
	background: #007aff;
}

.selected-images {
	display: flex;
	flex-wrap: wrap;
	gap: 15rpx;
}

.selected-image-item {
	position: relative;
	width: 120rpx;
	height: 120rpx;
}

.selected-image {
	width: 100%;
	height: 100%;
	border-radius: 8rpx;
}

.remove-image {
	position: absolute;
	top: -10rpx;
	right: -10rpx;
	width: 40rpx;
	height: 40rpx;
	background: rgba(0, 0, 0, 0.6);
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
}

/* 回复弹窗样式 */
.reply-popup {
	background: #fff;
	border-radius: 20rpx 20rpx 0 0;
	padding: 30rpx;
	padding-bottom: calc(30rpx + env(safe-area-inset-bottom));
	max-height: 80vh;
}

.popup-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 30rpx;
	padding-bottom: 20rpx;
	border-bottom: 1px solid #f0f0f0;
}

.popup-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #333;
}

.close-btn {
	width: 50rpx;
	height: 50rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #999;
}

.reply-input-container {
	margin-bottom: 20rpx;
}

.reply-textarea {
	width: 100%;
	height: 200rpx;
	padding: 20rpx;
	background: #f8f8f8;
	border-radius: 12rpx;
	font-size: 28rpx;
	border: none;
	resize: none;
	margin-bottom: 20rpx;
	box-sizing: border-box;
}

.reply-actions {
	display: flex;
	justify-content: space-between;
	align-items: center;
}
.no-comment {
	font-size: 28rpx;
	color: #999;
	text-align: center;
	padding: 60rpx;
}

</style>
