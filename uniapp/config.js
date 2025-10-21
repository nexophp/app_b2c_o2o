var domain = ''
var host = ''

//host = 'http://nexo.local' 
host = 'https://o2odemo.xda.com.cn'


domain = host
const config = {
	//首页
	home: '/pages/home/home',
	domain: domain,
	host: host,
	//上传地址
	upload_url: domain + '/admin/upload',
	//upload_hash
	upload_hash: domain + '/admin/upload/hash',
	//登录
	login: {
		//微信小程序openid
		openid: domain + '/minapp/api-login/openid',
		//微信小程序手机号授权登录
		weixin: domain + '/minapp/api-login/phone',
		//手机号、验证码登录 
		phone_code: domain + '/minapp/api-login/phone-code',
	},
	//用户信息
	user_info: domain + '/minapp/api-user/info',
	//设置用户信息， {field: ,value:}
	user_info_set: domain + '/minapp/api-user/set-info',
	//用户地址
	address: {
		//获取地址列表
		index: domain + '/address/api-user/index',
		//获取城市列表
		city: domain + '/address/api/index',
		//获取地址详情
		view: domain + '/address/api-user/view',
		//获取默认地址
		default: domain + '/address/api-user/default',
		//创建地址
		create: domain + '/address/api-user/create',
		//更新地址
		update: domain + '/address/api-user/update',
		//删除地址
		del: domain + '/address/api-user/del',
	},
	//商品
	product: {
		//列表 
		index: domain + '/product/api-product/index',
		//详情
		view: domain + '/product/api-product/view',
		//检测库存
		stock: domain + '/product/api-product/check-stock',
	},
	product_type: {
		index: domain + '/product/api-type/index',
		view: domain + '/product/api-type/view',
	},
	//物流
	logistic: domain + '/logistic/api/index',
	//物流支持的物流公司
	logistic_support: domain + '/logistic/api/support',
	//购物车
	cart: {
		//添加
		add: domain + '/cart/api-cart/add',
		//分页
		index: domain + '/cart/api-cart/index',
		//选中的列表
		index_selected: domain + '/cart/api-cart/index-selected',
		count: domain + '/cart/api-cart/count',
		//更新数量 id num
		update: domain + '/cart/api-cart/update',
		//删除
		delete: domain + '/cart/api-cart/delete',
		//清空
		clear: domain + '/cart/api-cart/clear',
		//选中
		selected: domain + '/cart/api-cart/selected',
		//全选
		select_all: domain + '/cart/api-cart/select-all',
		delete_selected:domain+'/cart/api-cart/delete-selected',
	},
	//订单
	order: {
		//订单列表
		index: domain + '/order/api/index',
		//订单详情
		view: domain + '/order/api/view',
		//创建订单
		create: domain + '/order/api/create',
		//快速创建订单
		quick_create: domain + '/order/api/quick-create',
		//确认订单
		confirm: domain + '/order/api/confirm',
		//更新状态
		update: domain + '/order/api/update',
		//删除订单
		delete: domain + '/order/api/delete',
		//已支付，退款
		refund: domain + '/order/api/refund',
		//统计
		stat: domain + '/order/api/stat',
		//是否购买过此商品
		has_buy: domain + '/order/api/has-buy',

	},

	//售后管理
	refund: {
		//列表
		index: domain + '/order/api-refund/list',
		//商家收货信息
		address: domain + '/order/api-refund/address',
		//详情
		detail: domain + '/order/api-refund/detail',
		//创建
		create: domain + '/order/api-refund/create',
		//取消
		cancel: domain + '/order/api-refund/cancel',
		//提交退货物流
		logic: domain + '/order/api-refund/logic',
	},

	//评论
	comment: {
		//评论列表
		index: domain + '/comment/api/index',
		//回复列表
		reply: domain + '/comment/api/reply',
		//发表评论
		do_comment: domain + '/comment/api/do-comment',
		//发表回复
		do_reply: domain + '/comment/api/do-reply',
	},

	//服务
	service: {
		order: {
			//服务订单列表
			index: domain + '/service/api-order/index',
			//服务订单详情
			view: domain + '/service/api-order/view',
			//统计
			stat: domain + '/service/api-order/stat',
		},
		cost: {
			//消费记录
			index: domain + '/service/api-cost/index',
		}
	},

	//点赞 收藏
	like: {
		//当前用户是否点赞
		check: domain + '/like/api/check',
		//点赞
		add: domain + '/like/api/add',
		//点赞数量
		count: domain + '/like/api/count',
	},

	pay: {
		//小程序支付
		weixin: domain + '/payment/api-weixin/jsapi',
	},

	blog: {
		//查看协议 title=
		view: domain + '/blog/api/view',
	},
	//员工 
	staff: {
		//是否是员工
		is: domain + '/staff/api/is'
	},
	//优惠券
	coupon: {
		//可领优惠券列表
		guest_list: domain + '/coupon/api/guest-list',
		//我的优惠券
		my_list: domain + '/coupon/api/my-list',
		//领取优惠券
		receive: domain + '/coupon/api/receive',
		//我的优惠券数量
		count: domain + '/coupon/api/count',
	},
	//绑定上下关系
	share_user: {
		index: domain + '/share_user/api/index',
		sub: domain + '/share_user/api/sub',
		bind: domain + '/share_user/api/bind',
		qr: domain + '/share_user/api/qr',
	},
	//积分
	point: {
		//积分明细
		index: domain + '/point/api/index',
		//总积分
		total: domain + '/point/api/total',
		//商品
		product: domain + '/point/api-point/index',
		//兑换
		create_order: domain + '/point/api-order/create',


	},

	//打卡
	daka: {
		//打卡记录 month
		index: domain + '/staff_daka/api/index',
		// lat lng
		do: domain + '/staff_daka/api/do'
	},

	//banner
	banner: domain + '/banner/api/index',
	//配置
	config: domain + '/minapp/api-config/info',
	//页面
	page: domain + '/mall/api-uniapp/index',
	//钱包
	wallet: {
		//首页
		index: domain + '/wallet/api/index',
		//收入记录
		in: domain + '/wallet/api/in',
		//提现记录
		out: domain + '/wallet/api/out',
		//提现
		do_out: domain + '/wallet/api/do-out',
		//流水
		in_out: domain + '/wallet/api/in-out',
	},


	seller: {
		view: domain + '/seller/api/view',
	},
	store: {
		view: domain + '/store/api/view',
	},
	
	//o2o
	o2o:{
		home:domain+'/o2o/api/index',
		address:domain+'/o2o/api/address',
	}

}

export default config