<?php
add_css("
.bootstrap-styled-form {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.bootstrap-styled-form .el-input__inner {
  border-radius: 4px;
  border: 1px solid #ced4da;
  padding: 8px 15px;
}

.upload-demo .btn {
  padding: 8px 15px;
}
.img-thumbnail {
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 4px;
}");
view_header('线下门店信息');
global $vue;

$vue->created(['load()']);
$vue->method("load()", "
this.form = " . json_encode($data) . ";
");

$vue->method("save()", "
ajax('/o2o/info/save',this.form,function(res){
    " . vue_message() . "
})

");


?>

<div class="container mt-4" style="width: 700px;">  
    <!-- 使用 Element UI 的表单组件 -->
    <el-form label-width="180px" class="bootstrap-styled-form">
        <h4 class="mb-4 text-center">线下门店信息</h4>
        <!-- 标题字段 -->
        <div class="mb-3">
            <el-form-item label="标题"  required>
                <el-input
                    v-model="form.title"
                    placeholder="请输入标题">
                </el-input>
            </el-form-item>
        </div>

        <!-- 地址字段 -->
        <div class="mb-3">
            <el-form-item label="地址"  required >
                <el-input
                    v-model="form.address"
                    placeholder="请输入地址">
                    <template slot="append" v-if="!form.lat || !form.lng" >
                        <span style="color:red;">坐标异常</span>
                    </template>
                </el-input>
            </el-form-item>
        </div>

        <!-- 联系电话字段 -->
        <div class="mb-3">
            <el-form-item label="联系电话"   required>
                <el-input
                    v-model="form.phone"
                    placeholder="请输入联系电话">
                </el-input>
            </el-form-item>
        </div>

        <!-- 营业状态字段 -->
        <div class="mb-3">
            <el-form-item label="营业状态"   required>
                <el-radio-group v-model="form.status">
                    <el-radio label="1">营业中</el-radio>
                    <el-radio label="-1">未营业</el-radio>
                </el-radio-group>
            </el-form-item>
        </div>

        <!-- 营业时间字段 -->
        <div class="mb-3">
            <el-form-item label="营业开始时间"   required>
                <el-input 
                    type="time"
                    v-model="form.business_start"
                    placeholder="请输入营业时间">
                </el-input>
            </el-form-item>
        </div>

        <!-- 营业时间字段 -->
        <div class="mb-3">
            <el-form-item label="营业结束时间"   required>
                <el-input 
                    type="time"
                    v-model="form.business_end"
                    placeholder="请输入营业时间">
                </el-input>
            </el-form-item>
        </div>

        <!-- 图片上传 -->
        <div class="mb-3">
            <el-form-item label="门头照" prop="image">
                <?= vue_upload_image($name = 'image', $top = 'form', $show_del = false) ?>
            </el-form-item>
        </div>

        <!-- 公告字段 -->
        <div class="mb-3">
            <el-form-item label="公告"   required>
                <el-input
                    v-model="form.notice"
                    placeholder="请输入公告">
                </el-input>
            </el-form-item>
        </div>

        <!-- 提交按钮 -->
        <div class="text-end">
            <el-button
                type="primary"
                @click="save"
                class="btn btn-primary">
                提交
            </el-button>
        </div>
    </el-form>
</div>

<?php view_footer(); ?>