<?php

use core\Menu;

/**
 * 模块信息
 */
$module_info = [
    'version' => '1.0.0',
    'title' => '飞蛾小票打印',
    'description' => '',
    'url' => '',
    'email' => '68103403@qq.com',
    'author' => 'sunkangchina'
];

Menu::setGroup('admin');
Menu::add('source', '资源', '', 'bi-suit-club', 900);
Menu::add('ticket', '小票打印机', '/ticket/admin', '', 10, 'source');

add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="bi bi-printer me-2"></i>
            <a href="https://admin.feieyun.com/" target="_blank">
                <?= lang('飞鹅打印') ?>
            </a>
        </h6>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">
                    USER
                </label>
                <input v-model="form.feie58_user" class="form-control" placeholder="">
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    UKEY
                </label>
                <input v-model="form.feie58_ukey" class="form-control" placeholder="">
            </div>


        </div>
    </div>
<?php
});

/**
 * 打印
 */
function do_ticket_print($printer_id, $data)
{
    $name = "modules\\ticket\lib\\Printer";
    $res = db_get_one("ticket", '*', ['id' => $printer_id]);
    if (!$res) {
        add_log('错误', '小票打印机不存在', 'error');
        return;
    }
    $method = "do" . ucfirst($res['type']);
    $res = $name::$method($res['code'], $res['secret'], $data);
    if ($res) {
        add_log('打印', '发送打印成功', 'success');
    } else {
        add_log('打印', '发送打印失败', 'error');
    }
    return $res;
}

/**
 * 添加打印机
 */
function add_ticket_print($printer_id)
{
    $name = "modules\\ticket\lib\\Printer";
    $res = db_get_one("ticket", '*', ['id' => $printer_id]);
    if (!$res) {
        add_log('错误', '小票打印机不存在', 'error');
        return;
    }
    $method = "add" . ucfirst($res['type']);
    $res = $name::$method($res['code'], $res['secret'],$res['title']);
    if ($res) {
        add_log('打印', '发送打印成功', 'success');
    } else {
        add_log('打印', '发送打印失败', 'error');
    }
    return $res;
}
