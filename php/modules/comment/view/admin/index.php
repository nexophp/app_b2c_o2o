<?php
view_header('评论管理');
global $vue;
$commentUrl = '/comment/admin/list';
$replyUrl = '/comment/admin/reply-list';

use modules\comment\model\CommentModel;
use modules\comment\model\CommentReplyModel;

$vue->data("height", "");
$vue->data("activeTab", "comments");
$vue->data("commentDialogVisible", false);
$vue->data("replyDialogVisible", false);
$vue->data("statusDialogVisible", false);
$vue->data("replyStatusDialogVisible", false);
$vue->data("addReplyDialogVisible", false);
$vue->data("commentDetail", []);
$vue->data("replyDetail", []);
$vue->data("statusForm", [
    'id' => '',
    'status' => ''
]);
$vue->data("replyStatusForm", [
    'id' => '',
    'status' => ''
]);
$vue->data("replyForm", [
    'comment_id' => '',
    'content' => '',
    'reply_id' => '',
    'to_user_id' => ''
]);

// 使用Model的状态选项
$vue->data("statusOptions", CommentModel::model()->getStatusOptions());
$vue->data("replyStatusOptions", CommentReplyModel::model()->getStatusOptions());

$vue->created(["load()"]);

$vue->method("load()", "
this.height = 'calc(100vh - 40px - " . get_config('admin_table_height') . "px)';
");

// 查看评论详情
$vue->method("viewCommentDetail(row)", "
ajax('/comment/admin/detail', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.commentDetail = res.data;
        app.commentDialogVisible = true;
    }
});
");

// 查看回复详情
$vue->method("viewReplyDetail(row)", "
ajax('/comment/admin/reply-detail', {id: row.id}, function(res) {
    if (res.code == 0) {
        app.replyDetail = res.data;
        app.replyDialogVisible = true;
    }
});
");

// 更新评论状态
$vue->method("updateCommentStatus(row)", "
app.statusForm.id = row.id;
app.statusForm.status = row.status;
app.statusDialogVisible = true;
");

// 提交评论状态更新
$vue->method("submitCommentStatus()", "
ajax('/comment/admin/update-status', app.statusForm, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.statusDialogVisible = false;
        app.load_comment_list();
    }
});
");

// 更新回复状态
$vue->method("updateReplyStatus(row)", "
app.replyStatusForm.id = row.id;
app.replyStatusForm.status = row.status;
app.replyStatusDialogVisible = true;
");

// 提交回复状态更新
$vue->method("submitReplyStatus()", "
ajax('/comment/admin/update-reply-status', app.replyStatusForm, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.replyStatusDialogVisible = false;
        app.load_comment_list();
    }
});
");

// 删除评论
$vue->method("deleteComment(row)", "
app.\$confirm('" . lang('确认删除该评论？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    ajax('/comment/admin/delete', {id: row.id}, function(res) {
        " . vue_message() . "
        if (res.code == 0) {
            app.load_comment_list();
        }
    });
}).catch(() => {});
");

// 删除回复
$vue->method("deleteReply(row)", "
app.\$confirm('" . lang('确认删除该回复？') . "', '" . lang('提示') . "', {
    confirmButtonText: '" . lang('确定') . "',
    cancelButtonText: '" . lang('取消') . "',
    type: 'warning'
}).then(() => {
    ajax('/comment/admin/delete-reply', {id: row.id}, function(res) {
        " . vue_message() . "
        if (res.code == 0) {
            app.load_comment_list();
        }
    });
}).catch(() => {});
");

// 添加回复
$vue->method("addReply(commentId, replyId, toUserId)", "
app.replyForm = {
    comment_id: commentId || '',
    content: '',
    reply_id: replyId || '',
    to_user_id: toUserId || ''
};
app.addReplyDialogVisible = true;
");

// 提交回复
$vue->method("submitReply()", "
ajax('/comment/admin/add-reply', app.replyForm, function(res) {
    " . vue_message() . "
    if (res.code == 0) {
        app.addReplyDialogVisible = false;
        app.load_comment_list();
        if (app.commentDialogVisible) {
            // 如果评论详情窗口打开，刷新评论详情
            app.viewCommentDetail({id: app.commentDetail.id});
        }
    }
});
");

// 处理下拉菜单命令
$vue->method("handleCommentCommand(action,row)", " 
switch(action) {
    case 'viewDetail':
        this.viewCommentDetail(row);
        break;
    case 'updateStatus':
        this.updateCommentStatus(row);
        break;
    case 'deleteComment':
        this.deleteComment(row);
        break;
    case 'addReply':
        this.addReply(row.id);
        break;
}
");

$vue->method("handleReplyCommand(command)", "
let action = command.action;
let row = command.row;
switch(action) {
    case 'viewDetail':
        this.viewReplyDetail(row);
        break;
    case 'updateStatus':
        this.updateReplyStatus(row);
        break;
    case 'deleteReply':
        this.deleteReply(row);
        break;
}
");

// 权限控制
$vue->data("can_view", has_access('comment/admin/detail'));
$vue->data("can_edit", has_access('comment/admin/update-status'));
$vue->data("can_delete", has_access('comment/admin/delete'));
$vue->data("can_reply", has_access('comment/admin/add-reply'));

$vue->method("handleTabClick(tab,eve)", "
 if(tab.name == 'comments'){
    this.load_comment_list();
 }
 if(tab.name == 'replies'){
    this.load_reply_list();
 }
// 在数据加载完成后调用
this.\$nextTick(() => {
  this.\$refs.commentTable && this.\$refs.commentTable.doLayout();
  this.\$refs.replyTable && this.\$refs.replyTable.doLayout();
});
");
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <el-tabs v-model="activeTab" @tab-click="handleTabClick">
                <!-- 评论列表 -->
                <el-tab-pane label="<?php echo lang('评论列表'); ?>" name="comments">
                    <?php
                    echo element("filter", [
                        'data' => 'comment_list',
                        'url' => $commentUrl,
                        'is_page' => true,
                        'init' => true,
                        [
                            'type' => 'input',
                            'name' => 'content',
                            'attr_element' => ['placeholder' => lang('评论内容')],
                        ],
                        [
                            'type' => 'select',
                            'name' => 'status',
                            'value' => [
                                ['label' => lang('全部'), 'value' => ''],
                                ['label' => lang('待审核'), 'value' => 'wait'],
                                ['label' => lang('已通过'), 'value' => 'complete'],
                                ['label' => lang('已拒绝'), 'value' => 'error']
                            ],
                            'attr_element' => ['placeholder' => lang('状态')],
                        ],
                    ]);
                    ?>

                    <?php
                    echo element('table', [
                        ['name' => 'open', ':data' => 'comment_list', 'ref' => 'commentTable', ':height' => 'height'],
                        ['name' => 'column', 'prop' => 'content', 'label' => lang('评论内容'), 'width' => '300', 'show-overflow-tooltip'],
                        ['name' => 'column', 'prop' => 'images', 'label' => lang('图片'), 'width' => '', 'tpl' => [
                            ['type' => 'html', 'html' => '
                                <el-image v-if="scope.row.images && scope.row.images.length > 0" style="width: 80px; height: 80px;"

                                 v-for="v in scope.row.images" :src="v" fit="fill" :preview-src-list="scope.row.images" :preview-fullscreen="true" />

                            ']
                        ]],
                        [
                            'name' => 'column',
                            'prop' => 'status',
                            'label' => lang('状态'),
                            'width' => '120',
                            'tpl' => [
                                ['type' => 'html', 'html' => '
                                    <el-tag v-if="scope.row.status == \'wait\'" type="warning">' . lang('待审核') . '</el-tag>
                                    <el-tag v-else-if="scope.row.status == \'approved\'" type="success">' . lang('已通过') . '</el-tag>
                                    <el-tag v-else-if="scope.row.status == \'rejected\'" type="danger">' . lang('已拒绝') . '</el-tag>
                                    <el-tag v-else>{{ scope.row.status_text }}</el-tag>
                                ']
                            ]
                        ],
                        ['name' => 'column', 'prop' => 'created_at_text', 'label' => lang('创建时间'), 'width' => '180'],
                        [
                            'name' => 'column',
                            'label' => lang('操作'),
                            'fixed' => 'right',
                            'width' => '230',
                            'tpl' => [
                                ['type' => 'html', 'html' => ' 
                                    <el-button type="primary" size="mini" v-if="can_view" @click="handleCommentCommand(\'viewDetail\', scope.row)">
                                        ' . lang("查看") . '
                                    </el-button>
                                    <el-button type="danger" size="mini" v-if="can_edit" @click="handleCommentCommand(\'updateStatus\', scope.row)">

                                            ' . lang("状态") . '
                                    </el-button>  
                                ']
                            ]
                        ],
                        ['name' => 'close'],
                    ]);
                    ?>

                    <?php
                    echo element("pager", [
                        'data' => 'comment_list',
                        'per_page' => get_config('per_page'),
                        'per_page_name' => 'per_page',
                        'url' => $commentUrl,
                        'reload_data' => []
                    ]);
                    ?>
                </el-tab-pane>

                <!-- 回复列表 -->
                <el-tab-pane label="<?php echo lang('回复列表'); ?>" name="replies">
                    <?php
                    echo element("filter", [
                        'data' => 'reply_list',
                        'url' => $replyUrl,
                        'is_page' => true,
                        'init' => true,
                        [
                            'type' => 'input',
                            'name' => 'content',
                            'attr_element' => ['placeholder' => lang('回复内容')],
                        ],
                        [
                            'type' => 'select',
                            'name' => 'status',
                            'value' => [
                                ['label' => lang('全部'), 'value' => ''],
                                ['label' => lang('待审核'), 'value' => 'wait'],
                                ['label' => lang('已通过'), 'value' => 'complete'],
                                ['label' => lang('已拒绝'), 'value' => 'error']
                            ],
                            'attr_element' => ['placeholder' => lang('状态')],
                        ],
                    ]);
                    ?>

                    <?php
                    echo element('table', [
                        ['name' => 'open', ':data' => 'reply_list', 'ref' => 'replyTable', ':height' => 'height'],
                        ['name' => 'column', 'prop' => 'content', 'label' => lang('回复内容'), 'width' => '', 'show-overflow-tooltip'],
                        ['name' => 'column', 'prop' => 'images', 'label' => lang('图片'), 'width' => '', 'tpl' => [
                            ['type' => 'html', 'html' => '
                                <el-image v-if="scope.row.images && scope.row.images.length > 0" :src="scope.row.images" fit="fill" :preview-src-list="scope.row.images" :preview-fullscreen="true" />
                            ']
                        ]],

                        [
                            'name' => 'column',
                            'prop' => 'status',
                            'label' => lang('状态'),
                            'width' => '120',
                            'tpl' => [
                                ['type' => 'html', 'html' => '
                                    <el-tag v-if="scope.row.status == \'wait\'" type="warning">' . lang('待审核') . '</el-tag>
                                    <el-tag v-else-if="scope.row.status == \'approved\'" type="success">' . lang('已通过') . '</el-tag>
                                    <el-tag v-else-if="scope.row.status == \'rejected\'" type="danger">' . lang('已拒绝') . '</el-tag>
                                    <el-tag v-else>{{ scope.row.status_text }}</el-tag>
                                ']
                            ]
                        ],
                        ['name' => 'column', 'prop' => 'created_at_format', 'label' => lang('创建时间'), 'width' => '180'],
                        [
                            'name' => 'column',
                            'label' => lang('操作'),
                            'fixed' => 'right',
                            'width' => '120',
                            'tpl' => [
                                ['type' => 'html', 'html' => '
                                    <el-dropdown @command="handleReplyCommand" trigger="click">
                                        <span class="el-dropdown-link">
                                            <el-button type="text" size="small">
                                                <i class="el-icon-more"></i>
                                            </el-button>
                                        </span>
                                        <el-dropdown-menu slot="dropdown">
                                            <el-dropdown-item v-if="can_view" :command="{action: \'viewDetail\', row: scope.row}">
                                                <i class="el-icon-view"></i> ' . lang("查看") . '
                                            </el-dropdown-item>
                                            <el-dropdown-item v-if="can_edit" :command="{action: \'updateStatus\', row: scope.row}">
                                                <i class="el-icon-refresh"></i> ' . lang("状态") . '
                                            </el-dropdown-item>
                                            <el-dropdown-item v-if="can_delete" :command="{action: \'deleteReply\', row: scope.row}">
                                                <i class="el-icon-delete"></i> ' . lang("删除") . '
                                            </el-dropdown-item>
                                        </el-dropdown-menu>
                                    </el-dropdown>
                                ']
                            ]
                        ],
                        ['name' => 'close'],
                    ]);
                    ?>

                    <?php
                    echo element("pager", [
                        'data' => 'reply_list',
                        'per_page' => get_config('per_page'),
                        'per_page_name' => 'per_page',
                        'url' => $replyUrl,
                        'reload_data' => []
                    ]);
                    ?>
                </el-tab-pane>
            </el-tabs>
        </div>
    </div>
</div>

<!-- 评论详情弹窗 -->
<el-dialog title="<?php echo lang('评论详情'); ?>" top="20px" :visible.sync="commentDialogVisible" width="80%">
    <div v-if="commentDetail.id">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td width="150"><?php echo lang('评论ID'); ?></td>
                        <td>{{ commentDetail.id }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('关联ID'); ?></td>
                        <td>{{ commentDetail.nid }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('类型'); ?></td>
                        <td>{{ commentDetail.type }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('用户ID'); ?></td>
                        <td>{{ commentDetail.user_id }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('状态'); ?></td>
                        <td>
                            <el-tag v-if="commentDetail.status == 'wait'" type="warning"><?php echo lang('待审核'); ?></el-tag>
                            <el-tag v-else-if="commentDetail.status == 'approved'" type="success"><?php echo lang('已通过'); ?></el-tag>
                            <el-tag v-else-if="commentDetail.status == 'rejected'" type="danger"><?php echo lang('已拒绝'); ?></el-tag>
                            <el-tag v-else>{{ commentDetail.status_text }}</el-tag>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td width="150"><?php echo lang('创建时间'); ?></td>
                        <td>{{ commentDetail.created_at_text }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <h5><?php echo lang('评论内容'); ?></h5>
                <div class="p-3 border rounded mt-2">{{ commentDetail.content }}</div>
            </div>
        </div>

        <div class="row mt-3" v-if="commentDetail.images_array && commentDetail.images_array.length > 0">
            <div class="col-12">
                <h5><?php echo lang('图片'); ?></h5>
                <div class="mt-2">
                    <img v-for="(image, index) in commentDetail.images_array" :key="index" :src="image" class="img-thumbnail me-2" style="max-height: 100px;">
                </div>
            </div>
        </div>

        <div class="row mt-3" v-if="commentDetail.replies && commentDetail.replies.length > 0">
            <div class="col-12">
                <h5><?php echo lang('回复列表'); ?></h5>
                <div class="mt-2">
                    <div v-for="reply in commentDetail.replies" :key="reply.id" class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>用户ID: {{ reply.user_id }}</strong>
                                <span v-if="reply.to_user_id" class="text-muted"> 回复 用户ID: {{ reply.to_user_id }}</span>
                            </div>
                            <div>
                                <el-tag v-if="reply.status == 'wait'" type="warning" size="small"><?php echo lang('待审核'); ?></el-tag>
                                <el-tag v-else-if="reply.status == 'approved'" type="success" size="small"><?php echo lang('已通过'); ?></el-tag>
                                <el-tag v-else-if="reply.status == 'rejected'" type="danger" size="small"><?php echo lang('已拒绝'); ?></el-tag>
                                <span class="text-muted ms-2">{{ reply.created_at_text }}</span>
                            </div>
                        </div>
                        <div class="mt-2">{{ reply.content }}</div>
                        <div v-if="reply.images_array && reply.images_array.length > 0" class="mt-2">
                            <img v-for="(image, index) in reply.images_array" :key="index" :src="image" class="img-thumbnail me-2" style="max-height: 60px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div slot="footer">
        <el-button v-if="can_reply" type="primary" @click="addReply(commentDetail.id)"><?php echo lang('添加回复'); ?></el-button>
        <el-button @click="commentDialogVisible = false"><?php echo lang('关闭'); ?></el-button>
    </div>
</el-dialog>

<!-- 回复详情弹窗 -->
<el-dialog title="<?php echo lang('回复详情'); ?>" top="20px" :visible.sync="replyDialogVisible" width="70%">
    <div v-if="replyDetail.id">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td width="150"><?php echo lang('回复ID'); ?></td>
                        <td>{{ replyDetail.id }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('评论ID'); ?></td>
                        <td>{{ replyDetail.comment_id }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('回复ID'); ?></td>
                        <td>{{ replyDetail.reply_id || '-' }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('用户ID'); ?></td>
                        <td>{{ replyDetail.user_id }}</td>
                    </tr>
                    <tr>
                        <td><?php echo lang('回复用户ID'); ?></td>
                        <td>{{ replyDetail.to_user_id || '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td width="150"><?php echo lang('状态'); ?></td>
                        <td>
                            <el-tag v-if="replyDetail.status == 'wait'" type="warning"><?php echo lang('待审核'); ?></el-tag>
                            <el-tag v-else-if="replyDetail.status == 'approved'" type="success"><?php echo lang('已通过'); ?></el-tag>
                            <el-tag v-else-if="replyDetail.status == 'rejected'" type="danger"><?php echo lang('已拒绝'); ?></el-tag>
                            <el-tag v-else>{{ replyDetail.status_text }}</el-tag>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo lang('创建时间'); ?></td>
                        <td>{{ replyDetail.created_at_text }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <h5><?php echo lang('回复内容'); ?></h5>
                <div class="p-3 border rounded mt-2">{{ replyDetail.content }}</div>
            </div>
        </div>

        <div class="row mt-3" v-if="replyDetail.images_array && replyDetail.images_array.length > 0">
            <div class="col-12">
                <h5><?php echo lang('图片'); ?></h5>
                <div class="mt-2">
                    <img v-for="(image, index) in replyDetail.images_array" :key="index" :src="image" class="img-thumbnail me-2" style="max-height: 100px;">
                </div>
            </div>
        </div>
    </div>
</el-dialog>

<!-- 评论状态更新弹窗 -->
<el-dialog title="<?php echo lang('更新评论状态'); ?>" :visible.sync="statusDialogVisible" width="400px">
    <el-form :model="statusForm">
        <el-form-item label="<?php echo lang('状态'); ?>">
            <el-select v-model="statusForm.status" placeholder="<?php echo lang('请选择状态'); ?>">
                <el-option v-for="item in statusOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
            </el-select>
        </el-form-item>
    </el-form>
    <div slot="footer">
        <el-button @click="statusDialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submitCommentStatus()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<!-- 回复状态更新弹窗 -->
<el-dialog title="<?php echo lang('更新回复状态'); ?>" :visible.sync="replyStatusDialogVisible" width="400px">
    <el-form :model="replyStatusForm">
        <el-form-item label="<?php echo lang('状态'); ?>">
            <el-select v-model="replyStatusForm.status" placeholder="<?php echo lang('请选择状态'); ?>">
                <el-option v-for="item in replyStatusOptions" :key="item.value" :label="item.label" :value="item.value"></el-option>
            </el-select>
        </el-form-item>
    </el-form>
    <div slot="footer">
        <el-button @click="replyStatusDialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submitReplyStatus()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<!-- 添加回复弹窗 -->
<el-dialog title="<?php echo lang('添加回复'); ?>" :visible.sync="addReplyDialogVisible" width="600px">
    <el-form :model="replyForm" label-width="120px">
        <el-form-item label="<?php echo lang('回复内容'); ?>" required>
            <el-input v-model="replyForm.content" type="textarea" :rows="4" placeholder="<?php echo lang('请输入回复内容'); ?>"></el-input>
        </el-form-item>
    </el-form>
    <div slot="footer">
        <el-button @click="addReplyDialogVisible = false"><?php echo lang('取消'); ?></el-button>
        <el-button type="primary" @click="submitReply()"><?php echo lang('确定'); ?></el-button>
    </div>
</el-dialog>

<?php view_footer() ?>