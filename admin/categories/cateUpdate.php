<?php

   include_once "../../fn.php";

   //接口：此接口用于更新分类
   $id = $_GET['id'];
   $name = $_GET['name'];
   $slug = $_GET['slug'];

  //  编写sql语句
  $sql = "update categories set name ='$name',slug='$slug' where id=$id";

  my_exec($sql);

?>