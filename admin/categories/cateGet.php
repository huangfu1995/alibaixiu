<?php

  // 此接口，专门用来获取分类数据
  // 1.准备sql
  // 2.执行sql
  // 3.返回结果
  include_once "../../fn.php";

  $sql = "select * from categories order by id desc";

  //执行
  $res = my_query($sql);

  echo json_encode($res);



?>