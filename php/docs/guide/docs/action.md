Action

 

| 名称 | 参数 | 说明 |
| --- | --- |--- |
| index|  | 网站首页 | 
| do_action("has_access",$data) | [ 'url' => $str, 'flag' => false, 'uid'=>$uid,] | flag为true时有权限 |  
| $module.$controller.$action | 控制器init | 控制器初始化 | 
| $module.$controller.$action.view | 调用view函数后 | 调用view函数后 |
| view.start | | 视图开始 |
| view.end | | 视图结束 |
| remove_admin_menu | ['cart'] | 移除后台菜单 |
| admin_menu | $menu | 后台菜单 |
| mime | ['jpg','png'] | 扩展mime类型 |
| login_type | ['username' => lang('用户名登录')] | 登录方式 |
| bind_account | 查看/admin/user-bind | 绑定账号 | 
| admin.change.password | $new_pwd | 管理员修改密码 | 
| header_right | | | 管理界面头部右侧 | 
| admin_tags | $admin_tags <br> ['admin'=>['color'=>'green','title'=>lang('admin_user'),]] | 管理员tag显示 | 
| access_deny | | 无权限访问 |
| admin.welcome.index | | 后台首页 | 
| AppController.init | | 基类控制器HOOK | 
| admin.setting.form | | 后台设置表单 | 
| timezones | 数组 ['Asia/Shanghai' => '中国标准时间 (北京)'] | 设置时区 |  
| lang | $data 有 name value file_name <br>如data中包含key为return的值时将阻止翻译 | 多语言|
| 数据库 | | |
| db_insert.$table.before |$data |写入记录前|
| db_save.$table.before |$data |写入记录前|
| db_insert.$table.after |$data 有 id data |写入记录后|
| db_save.$table.after | $data  有 id data |写入记录后|
| db_update.$table.before ||更新记录前|
| db_save.$table.before | | 更新记录前 |
| db_insert.$table.after | $data 有 id data where |更新记录后|
| db_save.$table.after | $data  有 id data where |更新记录后|
| db_del.$table.before | $where | 删除前|
| db_del.$table.after | $where | 删除前| 

删除菜单

~~~
add_action('remove_admin_menu',function(&$remove_menu){
    $remove_menu = ['webpos'];
});
~~~

数据库如不需要触发Action可用以下方法

~~~
db_insert($table, $data = [], $don_run_action = true) 
db_update($table, $data = [], $where = [], $don_run_action = true)
db_delete($table, $where = [], $don_run_action = true)
~~~



# admin.setting.form

演示代码
~~~
<div class="mb-4 mt-4">
    <h6 class="fw-bold mb-3 border-bottom pb-2">
        <i class="bi bi-display me-2"></i><?=lang('显示设置（修改颜色需刷新页面）')?>
    </h6>
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label"><?=lang('菜单背景颜色')?></label>
            <input
                type="color"
                class="form-control form-control-color"
                v-model="form.menu_bg" 
                title="">
        </div>
        <div class="col-md-3">
            <label class="form-label"><?=lang('菜单选中背景颜色')?></label>
            <input
                type="color"
                class="form-control form-control-color"
                v-model="form.menu_active" 
                title="">
        </div>
        <div class="col-md-3">
            <label class="form-label"><?=lang('菜单选中文字颜色')?></label>
            <input
                type="color"
                class="form-control form-control-color"
                v-model="form.menu_color_active" 
                title="">
        </div>
    </div> 
</div>
~~~

