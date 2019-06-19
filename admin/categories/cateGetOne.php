<?php
  //此接口，根据id返回单条分类数据

  // 1.获取id
  // 2.编写sql
  // 3.执行sql
  // 4.返回结果

  include_once "../../fn.php";
  $id = $_GET['id'];
  $sql ="select * from categories where id= $id";


 $res = my_query($sql)[0];
// 转成 json 返回给前端
 echo json_encode($res);

?>