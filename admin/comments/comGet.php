<?php

//此接口专门用户获取评论数据
// 1.前端将请求页数，以及每页多少条，应该传递给后台
// 2.获取参数，准备sql
// 3.执行sql
// 4.返回结果

include_once "../../fn.php";

//页数
$page = $_GET['page'];
//每页多少条
$pageSize = $_GET['pageSize'];

//起始索引：（页数-1）*每页多少条
$start = ($page - 1)* $pageSize;

//准备sql,需要联合查询，查询posts的标题，并且要求分页
$sql = "select comments.*,posts.title from comments
        join posts on comments.post_id = posts.id
        limit $start,$pageSize";

//执行sql语句
$res = my_query($sql);


//转成json格式输出给前端
echo json_encode($res);

?>