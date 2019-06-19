<?php

//此接口，用于获取文章数据 支持分页，且要求联合查询（联合用户表和分类表）

include_once "../../fn.php";

$page =$_GET['page'];   //页码
$pageSize = $_GET['pageSize'];    //每页条数

//起始索引
$start = ($page-1) * $pageSize;

//编写sql语句

$sql ="select posts.*,users.nickname,categories.name from posts
       join users on posts.user_id = users.id
       join categories on posts.category_id = categories.id
       order by id desc  -- 按照id进行排序
       limit $start, $pageSize";

//执行sql
$res = my_query($sql);

//返回结果，需要转成json数据
echo json_encode($res);


?>