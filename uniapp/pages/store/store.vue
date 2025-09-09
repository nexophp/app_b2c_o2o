<template>
	<view class="store-container">
		<!-- 搜索栏 -->
		<view class="search-section">
			<view class="search-box">
				<text class="search-icon">🔍</text>
				<input class="search-input" placeholder="搜索门店名称或地址" v-model="searchKeyword" @input="searchStores" />
			</view>
			<view class="location-btn" @click="getCurrentLocation">
				<text class="location-icon">📍</text>
				<text class="location-text">定位</text>
			</view>
		</view>
		
		<!-- 门店列表 -->
		<view class="store-list">
			<view class="store-item" v-for="(store, index) in filteredStores" :key="index" @click="selectStore(store)">
				<!-- 左侧图片 -->
				<image class="store-image" :src="store.image" mode="aspectFill"></image>
				
				<!-- 右侧内容 -->
				<view class="store-content">
					<view class="store-header">
						<text class="store-name">{{store.name}}</text>
						<text class="store-distance">{{store.distance}}</text>
					</view>
					
					<view class="store-address">
						<text class="address-icon">📍</text>
						<text class="address-text">{{store.address}}</text>
					</view>
					
					<view class="store-contact">
						<text class="contact-icon">📞</text>
						<text class="contact-text">{{store.phone}}</text>
					</view>
					
					<view class="store-hours">
						<text class="hours-icon">🕐</text>
						<text class="hours-text">{{store.hours}}</text>
						<text class="store-status" :class="{open: store.isOpen, closed: !store.isOpen}">
							{{store.isOpen ? '营业中' : '已打烊'}}
						</text>
					</view>
					
					<!-- 操作按钮 -->
					<view class="store-actions">
						<view class="action-btn call-btn" @click.stop="callStore(store.phone)">
							<text class="btn-icon">📞</text>
							<text class="btn-text">拨打</text>
						</view>
						<view class="action-btn nav-btn" @click.stop="navigateToStore(store)">
							<text class="btn-icon">🧭</text>
							<text class="btn-text">导航</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		
		<!-- 空状态 -->
		<view v-if="filteredStores.length === 0" class="empty-state">
			<image class="empty-image" src="/static/404.png" mode="aspectFit"></image>
			<text class="empty-text">暂无门店信息</text>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				searchKeyword: '',
				currentLocation: null,
				storeList: [
					{
						id: 1,
						name: '旗舰店（王府井店）',
						address: '北京市东城区王府井大街138号',
						phone: '010-12345678',
						hours: '09:00-22:00',
						distance: '1.2km',
						image: '/static/demo.jpeg',
						isOpen: true,
						lat: 39.9042,
						lng: 116.4074
					},
					{
						id: 2,
						name: '朝阳门店',
						address: '北京市朝阳区朝阳门外大街16号',
						phone: '010-87654321',
						hours: '10:00-21:30',
						distance: '2.8km',
						image: '/static/demo.jpeg',
						isOpen: true,
						lat: 39.9299,
						lng: 116.4344
					},
					{
						id: 3,
						name: '三里屯店',
						address: '北京市朝阳区三里屯路19号院',
						phone: '010-11223344',
						hours: '10:00-23:00',
						distance: '3.5km',
						image: '/static/demo.jpeg',
						isOpen: false,
						lat: 39.9364,
						lng: 116.4472
					},
					{
						id: 4,
						name: '西单店',
						address: '北京市西城区西单北大街120号',
						phone: '010-55667788',
						hours: '09:30-22:00',
						distance: '4.1km',
						image: '/static/demo.jpeg',
						isOpen: true,
						lat: 39.9075,
						lng: 116.3689
					},
					{
						id: 5,
						name: '国贸店',
						address: '北京市朝阳区建国门外大街1号',
						phone: '010-99887766',
						hours: '10:00-22:00',
						distance: '5.2km',
						image: '/static/demo.jpeg',
						isOpen: true,
						lat: 39.9097,
						lng: 116.4597
					}
				]
			}
		},
		computed: {
			filteredStores() {
				if (!this.searchKeyword) {
					return this.storeList;
				}
				return this.storeList.filter(store => {
					return store.name.toLowerCase().includes(this.searchKeyword.toLowerCase()) ||
						   store.address.toLowerCase().includes(this.searchKeyword.toLowerCase());
				});
			}
		},
		methods: {
			searchStores() {
				// 搜索功能已通过computed实现
			},
			getCurrentLocation() {
				uni.showLoading({
					title: '定位中...'
				});
				
				uni.getLocation({
					type: 'gcj02',
					success: (res) => {
						uni.hideLoading();
						this.currentLocation = {
							lat: res.latitude,
							lng: res.longitude
						};
						// 重新计算距离并排序
						this.calculateDistances();
						uni.showToast({
							title: '定位成功',
							icon: 'success'
						});
					},
					fail: () => {
						uni.hideLoading();
						uni.showToast({
							title: '定位失败',
							icon: 'none'
						});
					}
				});
			},
			calculateDistances() {
				if (!this.currentLocation) return;
				
				// 简单的距离计算（实际项目中应使用更精确的算法）
				this.storeList.forEach(store => {
					const distance = this.getDistance(
						this.currentLocation.lat, 
						this.currentLocation.lng, 
						store.lat, 
						store.lng
					);
					store.distance = distance.toFixed(1) + 'km';
				});
				
				// 按距离排序
				this.storeList.sort((a, b) => {
					return parseFloat(a.distance) - parseFloat(b.distance);
				});
			},
			getDistance(lat1, lng1, lat2, lng2) {
				// 计算两点间距离（单位：km）
				const R = 6371; // 地球半径
				const dLat = (lat2 - lat1) * Math.PI / 180;
				const dLng = (lng2 - lng1) * Math.PI / 180;
				const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
						  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
						  Math.sin(dLng/2) * Math.sin(dLng/2);
				const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
				return R * c;
			},
			selectStore(store) {
				// 选择门店，可以跳转到门店详情页
				uni.showActionSheet({
					itemList: ['查看详情', '拨打电话', '导航到店'],
					success: (res) => {
						switch(res.tapIndex) {
							case 0:
								// 查看详情
								uni.showToast({
									title: '查看门店详情',
									icon: 'none'
								});
								break;
							case 1:
								this.callStore(store.phone);
								break;
							case 2:
								this.navigateToStore(store);
								break;
						}
					}
				});
			},
			callStore(phone) {
				uni.makePhoneCall({
					phoneNumber: phone,
					fail: () => {
						uni.showToast({
							title: '拨打失败',
							icon: 'none'
						});
					}
				});
			},
			navigateToStore(store) {
				uni.openLocation({
					latitude: store.lat,
					longitude: store.lng,
					name: store.name,
					address: store.address,
					fail: () => {
						uni.showToast({
							title: '导航失败',
							icon: 'none'
						});
					}
				});
			}
		}
	}
</script>

<style>
	.store-container {
		background: #f5f5f5;
		min-height: 100vh;
	}

	.search-section {
		background: #fff;
		padding: 20rpx 30rpx;
		display: flex;
		align-items: center;
		gap: 20rpx;
		border-bottom: 2rpx solid #eee;
	}

	.search-box {
		flex: 1;
		display: flex;
		align-items: center;
		background: #f5f5f5;
		border-radius: 50rpx;
		padding: 0 30rpx;
		height: 70rpx;
	}

	.search-icon {
		font-size: 28rpx;
		color: #999;
		margin-right: 15rpx;
	}

	.search-input {
		flex: 1;
		font-size: 28rpx;
		color: #333;
		background: transparent;
		border: none;
		outline: none;
	}

	.location-btn {
		display: flex;
		align-items: center;
		padding: 15rpx 25rpx;
		background: #ff6b35;
		border-radius: 50rpx;
		gap: 8rpx;
	}

	.location-icon {
		font-size: 24rpx;
	}

	.location-text {
		font-size: 24rpx;
		color: #fff;
	}

	.store-list {
		padding: 20rpx;
	}

	.store-item {
		background: #fff;
		border-radius: 15rpx;
		margin-bottom: 20rpx;
		padding: 25rpx;
		display: flex;
		box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.1);
	}

	.store-image {
		width: 150rpx;
		height: 150rpx;
		border-radius: 10rpx;
		margin-right: 25rpx;
		flex-shrink: 0;
	}

	.store-content {
		flex: 1;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	.store-header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		margin-bottom: 15rpx;
	}

	.store-name {
		font-size: 32rpx;
		font-weight: bold;
		color: #333;
		flex: 1;
		margin-right: 20rpx;
	}

	.store-distance {
		font-size: 24rpx;
		color: #ff6b35;
		font-weight: bold;
		background: #fff2ee;
		padding: 8rpx 15rpx;
		border-radius: 20rpx;
	}

	.store-address, .store-contact, .store-hours {
		display: flex;
		align-items: center;
		margin-bottom: 12rpx;
		gap: 10rpx;
	}

	.address-icon, .contact-icon, .hours-icon {
		font-size: 24rpx;
		width: 30rpx;
	}

	.address-text, .contact-text, .hours-text {
		font-size: 26rpx;
		color: #666;
		flex: 1;
	}

	.store-status {
		font-size: 22rpx;
		padding: 4rpx 12rpx;
		border-radius: 15rpx;
		font-weight: bold;
	}

	.store-status.open {
		color: #4CAF50;
		background: #e8f5e8;
	}

	.store-status.closed {
		color: #f44336;
		background: #ffeaea;
	}

	.store-actions {
		display: flex;
		gap: 15rpx;
		margin-top: 15rpx;
	}

	.action-btn {
		display: flex;
		align-items: center;
		gap: 8rpx;
		padding: 12rpx 20rpx;
		border-radius: 25rpx;
		flex: 1;
		justify-content: center;
	}

	.call-btn {
		background: #e3f2fd;
		color: #2196F3;
	}

	.nav-btn {
		background: #fff3e0;
		color: #ff9800;
	}

	.btn-icon {
		font-size: 20rpx;
	}

	.btn-text {
		font-size: 24rpx;
		font-weight: bold;
	}

	.empty-state {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		height: 60vh;
	}

	.empty-image {
		width: 200rpx;
		height: 200rpx;
		margin-bottom: 30rpx;
	}

	.empty-text {
		font-size: 28rpx;
		color: #999;
	}
</style>
