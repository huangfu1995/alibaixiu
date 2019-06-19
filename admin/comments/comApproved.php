<?php

include_once "../../fn.php";

//专门用户批准的接口，需要传评论的id
$id = $_GET['id'];

//编写sql语句 update
// update 表名 set 字段=值 where 条件
//改造成批量操作
$sql = "update comments set status = 'approved' where id in($id)";

//执行sql
my_exec($sql);

?>