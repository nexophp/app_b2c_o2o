<?php 

$path = db_get_one("module","path",['name'=>'order']);

include PATH.$path.'/view/admin/index.php';


