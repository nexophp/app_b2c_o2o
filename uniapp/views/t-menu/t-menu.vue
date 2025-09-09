<template>
  <view class="t-menu-container" :style="row.css">
    <view class="menu-list" :style="{ flexWrap: wrap ? 'wrap' : 'nowrap', justifyContent: justifyType }">
      <view class="menu-item" v-for="(item, index) in menuList" :key="index" @click="pageClick(item)" 
        :style="'width: '+itemWidth + 'px;'  ">

        <image class="menu-icon" :src="item.image" mode="aspectFill" />
        <text class="menu-text" :style="row.css_inner">{{ item.title }}</text>

      </view>
    </view>
  </view>
</template>

<script>
export default {
  props: {
    row:{
      type: Object,
      default: () => {}
    },
    menuList: {
      type: Array,
      default: () => []
    },
    rowCount: {
      type: Number,
      default: 5 // 默认每行显示 5 个菜单项
    },
    alignLeft: {
      type: Boolean,
      default: true // 默认左对齐
    }
  },
  data() {
    return {
      itemWidth: 0, // 菜单项宽度
      screenWidth: 0, // 屏幕宽度，初始化为 0
      wrap: false // 是否换行
    }
  },
  computed: {
    justifyType() {
      // 当菜单数量少于或等于 4 时平分显示，否则靠左或换行
      if (this.menuList.length <= 4) {
        return 'space-between'
      }
      return this.alignLeft ? 'flex-start' : 'space-between'
    }
  },
  mounted() {
    this.calculateLayout()
    uni.onWindowResize(() => {
      this.calculateLayout() // 监听窗口大小变化，重新计算布局
    })
  },
  methods: {
    calculateLayout() {
      const systemInfo = uni.getSystemInfoSync()
      this.screenWidth = systemInfo.windowWidth || 375 // 获取屏幕宽度，保底值 375
      this.screenWidth = this.screenWidth - 20
      // 当菜单数量超过 5 个时启用换行
      this.wrap = this.menuList.length > 5
      // 当数量少于等于 4 时平分宽度，否则按 5 个每行计算
      const itemsPerRow = this.menuList.length <= 4 ? this.menuList.length : this.rowCount
      this.itemWidth = Math.floor(this.screenWidth / itemsPerRow)
    },
     
  }
}
</script>

<style lang="scss" scoped>
.t-menu-container {
  width: 100%;
  padding: 20rpx 0; 
}

.menu-list {
  display: flex;
  padding: 0 20rpx;
  flex-direction: row; // 横向排列
}

.menu-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10rpx 0;
  box-sizing: border-box;
  flex-shrink: 0; // 防止菜单项被压缩
  text-align: center;
}

.menu-icon {
  width: 80rpx;
  height: 80rpx;
  margin-bottom: 10rpx;
  border-radius: 50%; // 圆形图标
}

.menu-text {
  font-size: 24rpx;
  color: #666;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%; // 文本溢出处理
}
</style>