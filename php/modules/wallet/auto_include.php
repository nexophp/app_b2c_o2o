<?php

use core\Menu;
use modules\wallet\lib\WalletIn;
use modules\wallet\lib\WalletOut;

/**
 * 模块信息
 */
$module_info = [
	'version' => '1.0.0',
	'title' => '钱包',
	'description' => '',
	'url' => '',
	'email' => '68103403@qq.com',
	'author' => 'sunkangchina',
	'depends' => [
		'blog',
		'payment'
	]
];

Menu::setGroup('admin');
// 添加顶级菜单
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('wallet-admin', '钱包', '/wallet/admin', '', 20, 'source');

/**
 * 增加钱包
 * @param array $data 数据  
 */
function add_wallet($data = [])
{
	return WalletIn::add($data);
}
/**
 * 确认收入
 * @param string $order_num 订单号
 * @param string $type 类型 
 */
function confirm_wallet($order_num, $type = 'product')
{
	return WalletIn::confirm($order_num, $type);
}

/**
 * 提现
 */
function add_wallet_out($data = [])
{
	return WalletOut::add($data);
}

/**
 * 同意提现并打款
 */
function confirm_wallet_out($out_id)
{
	return WalletOut::confirm($out_id);
}

/**
 * 提现审核通知
 */
function wallet_notice()
{
	$count = db_get_count("wallet_cash_out", ['status' => 'wait']);
	if ($count > 0) {
		return lang("有") . " <span style='color:red;font-size:16px;'>" . $count . "</span> " . lang("条提现申请待处理");
	}
}

add_action('header_center', function () {
	if (is_admin()) { 
		$notice = wallet_notice();
		if ($notice) {
			echo "<div><a href='/admin/site#/wallet/admin'>" . $notice . "</a></div>";
		}
	}
});

add_action('admin.setting.form', function () {
?>
	<div class="mb-4">
		<h6 class="fw-bold mb-3 border-bottom pb-2">
			<i class="bi bi-list me-2"></i><?= lang('提现设置') ?>
		</h6>

		<div class="row g-3">
			<div class="col-md-3">
				<label class="form-label">
					<?= lang('提现手续费比例,千分比') ?> , <?= lang('当前比例') ?>：<?= get_config('wallet_rate') ?: 10 ?><span style="font-size: 16px;">‰</span>
				</label>
				<input type="number" class="form-control" v-model="form.wallet_rate" placeholder="<?= lang('请输入提现比例') ?>">
			</div>

			<div class="col-md-3">
				<label class="form-label">
					<?= lang('最低提现金额') ?> <?= lang('当前金额') ?>：<?= get_config('wallet_min') ?: 10 ?>元

				</label>
				<input type="number" class="form-control" v-model="form.wallet_min" placeholder="<?= lang('请输入提现金额') ?>">
			</div>
		</div>
	</div>
<?php
});
