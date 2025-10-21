<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => '物流查寻',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];

Menu::setGroup('admin');
// 添加顶级菜单
Menu::add('system', '系统管理', '', 'bi-gear', 50);

Menu::add('logistic', '物流查寻', '/logistic/admin', '', 2000, 'system');
Menu::add('logistic-template', '运费模板', '/logistic/template', '', 19999, 'system');

add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <?= lang('物流查寻') ?>
            <a href="https://market.aliyun.com/apimarket/detail/cmapi021863" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </h6>
        <div class="row g-3">

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('阿里云市场 AppCode') ?>
                </label>
                <input v-model="form.aliyun_m_code" type="text" class="form-control" placeholder="">
            </div>

        </div>
    </div>
<?php
});


add_action('get_freight_template', function (&$get_freight_template) {
    $all = db_get("logistic_template", "*", ['ORDER' => ['id' => 'DESC', 'status' => 1]]);
    if ($all) {
        foreach ($all as $v) {
            $get_freight_template[] = [
                'value' => $v['id'],
                'label' => $v['name'],
            ];
        }
    }
});

/**
 * 检查用户区域是否在允许的区域内
 * @param array $user_region 用户区域，格式为 ['省份', '城市', '区县']
 * @param array $allow 允许的区域列表，每个元素为 ['省份', '城市', '区县']
 * @return bool 
 */
function is_region_in($user_region, $allow)
{
    if (!$user_region) {
        json_error(lang('获取用户区域失败'));
    }
    foreach ($allow as $allowed_region) {
        // 去除可能存在的空格
        $allowed_region_clean = array_map('trim', $allowed_region);
        $user_region_clean = array_map('trim', $user_region);

        // 比较三级地址是否完全匹配
        if (
            $allowed_region_clean[0] === $user_region_clean[0] &&
            $allowed_region_clean[1] === $user_region_clean[1] &&
            $allowed_region_clean[2] === $user_region_clean[2]
        ) {
            return true;
        }
    }
    return false;
}


add_action("shipping", function (&$shipping) {
    modules\logistic\lib\Shipping::do($shipping);
});
