<template>
    <view class="product-container" :style="row.css">
        <!-- 布局切换按钮（可选） -->
        <view class="layout-switch" v-if="showSwitch">
            <view class="switch-btn" :class="{ active: show_type === 'grid' }" @click="changeLayout('grid')">
                <view class="iconfont"> &#xe635;</view>
            </view>
            <view class="switch-btn" :class="{ active: show_type === 'list' }" @click="changeLayout('list')">
                <view class="iconfont"> &#xe850;</view>
            </view>
        </view>

        <!-- 商品列表 -->
        <view class="product-list" :class="show_type">
            <view class="product-item" v-for="(item, index) in dataList" :key="index" @click="goToProduct(item)">
                <image class="product-image" :src="item.image || defaultImage" mode="aspectFill"></image>
                <view class="product-info">
                    <text class="product-name">{{ item.title }}</text>
                    <text class="product-price">¥{{ item.price }}</text>
                    <view class="product-extra" v-if="show_type === 'list'">
                        <text class="product-sales">销量: {{ item.sales || 0 }}</text> 
                    </view>
                </view>
            </view>
        </view>

        <uni-load-more v-if="is_pager" :status="status"></uni-load-more>

    </view>
</template>

<script>
export default {
    props: {
        row: {
            type: Object,
            default: () => ({})
        },
        list: {
            type: Array,
            default: () => []
        },
        layout: {
            type: String,
            default: 'grid', // 'grid' | 'list'
            validator: value => ['grid', 'list'].includes(value)
        },
        showSwitch: {
            type: Boolean,
            default: true
        },
        column: {
            type: Number,
            default: 2
        }
    },
    data() {
        return {
            layoutType: this.layout,
            defaultImage: '',
            dataList: [],
            show_type: '',
            total: '',
            per_page: '',
            page: 1,
            pageScrollTop: 0,
            is_pager: false,
            // more/loading/noMore	
            status: 'loading',

        }
    },
    mounted() {
        if (this.row.product_type == 'category') {
            let category_id = this.row.category_id
            this.ajax(this.config.product.index, {
                type_id: category_id,
                limit: this.row.limit
            }).then(res => {
                this.dataList = res.data
            })

        } else if (this.row.product_type == 'all') {
            this.page = 1
            this.is_pager = true
            this.loadMore()

        } else if (this.row.product_type == 'product') {
            let product_id = this.row.product_id
            this.ajax(this.config.product.index, {
                product_id: product_id,
                limit: this.row.limit
            }).then(res => {
                this.dataList = res.data
            })
        }


        if (this.row.show_type) {
            this.show_type = this.row.show_type
        } else {
            this.show_type = this.layout
        }

        if (!this.dataList) {
            $this.dataList = this.list;
        }
    },
    methods: {
        loadMore() {
            this.$emit('reach-bottom')
            if (!this.is_pager) {
                return false
            } 
            this.ajax(this.config.product.index, {
                page: this.page
            }).then(res => {
                if (res.data_count == 0) {
                    this.status = 'noMore'
                    return false
                }
                this.dataList = [...this.dataList, ...res.data]
                this.total = res.total
                this.per_page = res.per_page
            })
            this.page++
            this.$emit('reach-bottom')
        },

        changeLayout(type) {
            this.layoutType = type
            this.$emit('layout-change', type)
        },
        goToProduct(item) {
            this.$emit('item-click', item)
            uni.navigateTo({
                url: '/pages/product/view?id=' + item.id
            })
        }
    }
}
</script>

<style lang="scss" scoped>
.product-container {
    background-color: #f5f5f5;
    padding: 20rpx;
    padding-top: 0px;
}

/* 布局切换按钮 */
.layout-switch {
    display: flex;
    justify-content: flex-end;
    padding: 20rpx;
    background-color: #fff;
    margin-bottom: 20rpx;
    border-radius: 10rpx;

    .switch-btn {
        width: 60rpx;
        height: 60rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 20rpx;
        border-radius: 8rpx;
        background-color: #f5f5f5;

        &.active {
            background-color: #ff6b35;
            color: #fff;
        }
    }
}

/* 商品列表公共样式 */
.product-list {
    &.grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    &.list {
        display: flex;
        flex-direction: column;
    }
}

.product-item {
    background: #fff;
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 20rpx;

    /* 网格布局样式 */
    .product-list.grid & {
        width: calc(50% - 10rpx);

        @for $i from 1 through 5 {
            .product-list.grid[data-column="#{$i}"] & {
                width: calc(100% / #{$i} - 20rpx);
            }
        }
    }

    /* 列表布局样式 */
    .product-list.list & {
        display: flex;
        width: 100%;

        .product-image {
            width: 200rpx;
            height: 200rpx;
            flex-shrink: 0;
        }

        .product-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            /* 新增：防止内容溢出 */
            padding: 20rpx;
        }

        .product-extra {
            display: flex;
            justify-content: space-between;
            margin-top: 10rpx;
            font-size: 24rpx;
            color: #999;
        }
    }
}

.product-image {
    width: 100%;
    height: 200rpx;
    background-color: #f5f5f5;
}

.product-info {
    padding: 20rpx;
}

.product-name {
    font-size: 28rpx;
    color: #333;
    display: block;
    margin-bottom: 10rpx;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-price {
    font-size: 32rpx;
    color: #ff6b35;
    font-weight: bold;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 新增：确保列表布局时内容不会溢出 */
.product-list.list {

    .product-name,
    .product-price,
    .product-sales,
    .product-stock {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .product-extra {
        >text {
            flex: 1;

            &:first-child {
                margin-right: 10rpx;
            }
        }
    }
}
</style>