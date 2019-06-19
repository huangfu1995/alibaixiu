<?php

   include_once "../fn.php";
   is_login();

      //页面标识
      $page = "post-add";

?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Add new post &laquo; Admin</title>
  <link rel="stylesheet" href="../assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="../assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="../assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <script src="../assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
  <?php include_once "./inc/navbar.php"?>
    <div class="container-fluid">
      <div class="page-title">
        <h1>写文章</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <!-- 三要素 action method name -->
      <!-- 文件上传，必须指定enctype="multipart/form-data" -->
      <form class="row" action="./posts/postsAdd.php" method="post" enctype="multipart/form-data">
        <div class="col-md-9">
          <div class="form-group">
            <label for="title">标题</label>
            <input id="title" class="form-control input-lg" name="title" type="text" placeholder="文章标题">
          </div>
          <div class="form-group">
            <label for="content">标题</label>
            <textarea id="content" class="form-control input-lg" name="content" cols="30" rows="10" placeholder="内容" style="display:none;"></textarea>
            <!-- 准备一个容器，用于初始化富文本编辑器 -->
            <div id="content-box"></div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="slug">别名</label>
            <input id="slug" class="form-control" name="slug" type="text" placeholder="slug">
            <p class="help-block">https://zce.me/post/<strong id="strong">slug</strong></p>
          </div>
          <div class="form-group">
            <label for="feature">特色图像</label>
            <!-- show when image chose -->
            <img class="help-block thumbnail" style="display: none; width:60px;" id="img">
            <input id="feature" class="form-control" name="feature" type="file"
                   accept="image/gif,image/jpeg,image/jpg,image/png">
          </div>
          <div class="form-group">
            <label for="category">所属分类</label>
            <select id="category" class="form-control" name="category">
              <!-- <option value="1">未分类</option> -->
            </select>
          </div>
          <div class="form-group">
            <label for="created">发布时间</label>
            <input id="created" class="form-control" name="created" type="datetime-local">
          </div>
          <div class="form-group">
            <label for="status">状态</label>
            <select id="status" class="form-control" name="status">
              <!-- <option value="drafted">草稿</option> -->
            </select>
          </div>
          <div class="form-group">
            <button class="btn btn-primary" type="submit">保存</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php include_once "./inc/aside.php"?>

  <script src="../assets/vendors/jquery/jquery.js"></script>
  <script src="../assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="../assets/vendors/wangEditor/wangEditor.min.js"></script>
  <script src="../assets/vendors/moment/moment.js"></script>
  <script src="../assets/vendors/template/template-web.js"></script>
  <script>NProgress.done()</script>

<!-- select, option, 
       name 设置给 select
       value 设置给 option
       将来进行提交, 提交的就是选中的 option的 value 值
-->
<script type="text/html" id="cateTpl">
{{each list v i}}
     <option value="{{v.id}}">{{v.name}}</option>
{{/each}}
</script>

<!-- v表示每项的值，k表示键 -->
<script type="text/html" id="stateTpl">
   {{each state v k}}
      <option value="{{k}}">{{v}}</option>
      {{/each}}
</script>


  <script>
      $(function(){

      // 大思路:
      // 1. 前端将基本文本信息, 文件信息, 进行提交
      // 2. 后台进行保存处理

      // 对页面进行优化处理
      // 1.使用富文本编辑器
     var E = window.wangEditor;   //起了别名
     var editor = new E('#content-box');    //创建实例
    //  监听富文本编辑器的内容变化，同步到textarea将来用于提交
     editor.customConfig.onchange = function(html){
       $('#content').val(html)
     }
    editor.create();    //创建一个文本编辑器
 

    // 2.别名同步事件
    // input是html5新增的事件，监听表单的输入
    $('#slug').on("input",function(){
      //获取input框中的值，如果为空，初始化为slug
      var txt = $(this).val()||'slug';
      $('#strong').text(txt);
    });

    // 3.图片实时预览
    $('#feature').on("change",function(){

      // console.log(this.files);
      //获取文件对象
      var file = this.files[0];
      //根据文件对象生成地址
      var imgUrl = URL.createObjectURL(file);
      //赋值到img src中
      $('#img').attr("src",imgUrl).show();

    })


  //  4.时间日期格式化
  // 2017-07-03T02:05
  $('#created').val(moment().format("YYYY-MM-DDTHH:mm"));


    
    // 5.分类动态渲染
    // 发送请求，获取数据，进行渲染
    $.ajax({
      url:"./categories/cateGet.php",
      dataType:"json",
      success:function(info){
        //将数组包装成对象，用于模板渲染
        var obj ={
          list:info
        };
        console.log(obj);
        var htmlStr = template("cateTpl",obj);
        $('#category').html(htmlStr);   //进行渲染
      }
    })

    // 6.状态动态渲染
     // 草稿（drafted）/ 已发布（published）/ 回收站（trashed）
      var state ={
        drafted:"草稿",
        published:"已发布",
        trashed:"回收站"
      }
      // 在需要遍历的对象外层包一层对象，将来在模板中才能使用state这个变量
      var obj = {
        state: state
      }
      // 模板中 each 方法可以遍历数组或者对象
      // 在模板中可以使用传进去的数据对象的属性
      var htmlStr = template("stateTpl",obj);
      $('#status').html(htmlStr);

})
  
   
  </script>
</body>
</html>
