<?php 
add_css("
.float-right{
    display: none !important;
}
#app{
margin:0 !important;
padding:0 !important;
}
 
.swagger-ui .info {
    display: none !important;
}
/* 隐藏标题和描述 */
.swagger-ui .info .title {
    display: none !important;
}
.swagger-ui .info .description {
    display: none !important;
}
.swagger-ui .info .version {
    display: none !important;
}
/* 调整内容区域 */
.swagger-ui .swagger-container {
    padding-top: 0 !important;
}
");
view_header('API文档'); ?>

  
<div id="swagger-ui"></div>

<script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-bundle.js"></script>
<script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-standalone-preset.js"></script>
<link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui.css" />

<script>
function generateDocs() {
    ajax('/swagger_ui/site/generate', {}, function(res) {
        if (res.code === 0) {
            layer.msg('文档生成成功', {icon: 1});
            // 重新加载Swagger UI
            initSwaggerUI();
        } else {
            layer.msg('文档生成失败', {icon: 2});
        }
    });
}

function initSwaggerUI() {
    SwaggerUIBundle({
        url: '/openapi.json',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl,
        ],
        layout: "StandaloneLayout",
    });
}

// 页面加载时初始化
document.addEventListener('DOMContentLoaded', function() {
    initSwaggerUI();
});
</script>
  



<?php view_footer(); ?>