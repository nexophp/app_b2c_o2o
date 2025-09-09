<template>
  <view class="container">
    <!-- 搜索栏 -->
    <view class="search-bar">
      <uni-search-bar 
        placeholder="搜索商品" 
        radius="100"
        @confirm="handleSearch"
        v-model="searchKeyword"
        @clear="clearSearch"
      />
    </view>

    <!-- 排序选项卡 -->
    <view class="sort-tabs">
      <view 
        v-for="tab in sortTabs" 
        :key="tab.value"
        :class="['tab-item', { 'active': activeTab === tab.value }]"
        @click="changeSort(tab.value)"
      >
        <text>{{ tab.label }}</text>
        <view class="underline" v-if="activeTab === tab.value"></view>
      </view>
    </view>

    <!-- 商品列表 -->
    <mescroll-body 
      ref="mescrollRef" 
      @init="mescrollInit" 
      @down="downCallback" 
      @up="upCallback"
      :up="upOption"
      :down="downOption"
      top="0rpx"
    >
      <view class="product-list">
        <view 
          v-for="(item, index) in productList" 
          :key="index" 
          class="product-item"
          @click="navToDetail(item.id)"
        >
          <image 
            :src="item.image || 'http://nexo.local/uploads/tmp/2025-08-07/bbea2256d844df36d1ace9587fd7f4c7.png'" 
            mode="aspectFill" 
            class="product-image"
          />
          <view class="product-info">
            <text class="title">{{ item.title }}</text>
            <view class="price-section">
              <text class="price">¥{{ item.price }}</text>
              <text v-if="item.original_price" class="original-price">¥{{ item.original_price }}</text>
            </view>
            <view class="extra-info">
              <text>销量: {{ item.sales || 0 }}</text>
              <!-- <uni-rate :value="item.rating || 0" :size="12" readonly></uni-rate> -->
            </view>
          </view>
        </view>
      </view>
    </mescroll-body>

    <!-- 空状态提示 -->
    <view class="empty-tip" v-if="productList.length === 0 && isLoaded">
      <image src="/static/empty.png" mode="aspectFit"></image>
      <text>暂无商品数据</text>
    </view>
  </view>
</template>

<script>
import MescrollMixin from "@/uni_modules/mescroll-uni/components/mescroll-uni/mescroll-mixins.js";

export default {
  mixins: [MescrollMixin],
  data() {
    return {
      searchKeyword: '',
      activeTab: 'recommend', // 默认推荐排序
      sortTabs: [
       { label: '推荐', value: 'recommend' },
        { label: '最新', value: 'recent' },
        { label: '销量', value: 'sales' }
      ],
      productList: [],
      isLoaded: false,
      
      // Mescroll配置
      upOption: {
        page: { size: 10 },
        noMoreSize: 5,
        empty: { 
          tip: '暂无更多商品',
          icon: '/static/empty.png'
        }
      },
      downOption: { 
        auto: false,
        native: true
      },
      
      // 请求参数
      queryParams: {
        page: 1,
        page_size: 10,
        sort: 'recommend',
        keyword: ''
      }
    }
  },
  methods: {
    // 切换排序方式
    changeSort(sortType) {
      this.activeTab = sortType;
      this.queryParams.sort = sortType;
      this.mescroll.resetUpScroll();
    },
    
    // 处理搜索
    handleSearch() {
      this.queryParams.keyword = this.searchKeyword;
      this.mescroll.resetUpScroll();
    },

    // 清空搜索
    clearSearch() {
      this.searchKeyword = '';
      this.handleSearch();
    },

    // Mescroll回调
    downCallback() {
      this.queryParams.page = 1;
      this.loadProducts();
    },
    
    upCallback(page) {
      this.queryParams.page = page.num;
      this.loadProducts();
    },
    
    // 加载商品数据
    loadProducts() {
      uni.showLoading({ title: '加载中...' });
      
      this.ajax(this.config.product.index, this.queryParams).then(res => {
        uni.hideLoading();
        this.isLoaded = true;
        
        const curPageData = res.data || [];
        
        if (this.queryParams.page === 1) {
          this.productList = [];
        }
        
        this.productList = this.productList.concat(curPageData);
        this.mescroll.endBySize(curPageData.length, res.data.total);
      }) 
    },
    
    // 跳转详情页
    navToDetail(id) {
      uni.navigateTo({
        url: `/pages/product/view?id=${id}`
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  height: 100vh;
  background-color: #f7f7f7;
}

.search-bar {
  padding: 15rpx 30rpx;
  background-color: #fff;
}

.sort-tabs {
  display: flex;
  background-color: #fff;
  padding: 20rpx 0;
  border-bottom: 1rpx solid #eee;
  
  .tab-item {
    flex: 1;
    text-align: center;
    font-size: 28rpx;
    color: #666;
    position: relative;
    
    &.active {
      color: #ff5f6d;
      font-weight: bold;
    }
    
    .underline {
      position: absolute;
      bottom: -10rpx;
      left: 50%;
      transform: translateX(-50%);
      width: 60rpx;
      height: 4rpx;
      background-color: #ff5f6d;
    }
  }
}

.product-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20rpx;
  padding: 20rpx;
  
  .product-item {
    background: #fff;
    border-radius: 12rpx;
    overflow: hidden;
    box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);
    
    .product-image {
      width: 100%;
      height: 300rpx;
    }
    
    .product-info {
      padding: 16rpx;
      
      .title {
        font-size: 28rpx;
        color: #333;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
        min-height: 80rpx;
      }
      
      .price-section {
        margin-top: 12rpx;
        display: flex;
        align-items: center;
        
        .price {
          color: #ff5f6d;
          font-size: 32rpx;
          font-weight: bold;
        }
        
        .original-price {
          color: #999;
          font-size: 24rpx;
          text-decoration: line-through;
          margin-left: 8rpx;
        }
      }
      
      .extra-info {
        margin-top: 12rpx;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 24rpx;
        color: #999;
      }
    }
  }
}

.empty-tip {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 0;
  
  image {
    width: 200rpx;
    height: 200rpx;
    opacity: 0.5;
  }
  
  text {
    margin-top: 20rpx;
    color: #999;
    font-size: 28rpx;
  }
}
</style>