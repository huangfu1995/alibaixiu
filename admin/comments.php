<?php

   include_once "../fn.php";
   is_login();

     //页面标识
     $page = "comments";
?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Comments &laquo; Admin</title>
  <link rel="stylesheet" href="../assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="../assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="../assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="../assets/vendors/pagination/pagination.css">
  <script src="../assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
  <?php include_once "./inc/navbar.php"?>
    <div class="container-fluid">
      <div class="page-title">
        <h1>所有评论</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <div class="btn-batch" style="display: none">
          <button class="btn btn-info btn-sm btn-approveds">批量批准</button>
          <button class="btn btn-danger btn-sm btn-dels">批量删除</button>
        </div>
       <!-- 准备自己的容器，实现分页 -->
        <div class="page-box pull-right">
        
        </div>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox" class="th-chk"></th>
            <th>作者</th>
            <th>评论</th>
            <th>评论在</th>
            <th>提交于</th>
            <th>状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
          <!-- <tr>
            <td class="text-center"><input type="checkbox"></td>
            <td>大大</td>
            <td>楼主好人，顶一个</td>
            <td>《Hello world》</td>
            <td>2016/10/07</td>
            <td>未批准</td>
            <td class="text-center">
              <a href="javascript:;" class="btn btn-info btn-xs">批准</a>
              <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>
            </td>
          </tr> -->
         
        </tbody>
      </table>
    </div>
  </div>

  <?php include_once "./inc/aside.php"?>

  <script src="../assets/vendors/jquery/jquery.js"></script>
  <script src="../assets/vendors/pagination/jquery.pagination.js"></script>
  <script src="../assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="../assets/vendors/template/template-web.js"></script>
  <script>NProgress.done()</script>

  <script type="text/html" id="tpl">
        {{each list v i}}
           <tr>
            <td class="text-center"><input type="checkbox" class="tb-chk" data-id="{{v.id}}"></td>
            <td>{{v.author}}</td>
            <td>{{v.content.substr(0,20)}}...</td>
            <td>《{{v.title}}》</td>
            <td>{{v.created}}</td>
            <td>{{state[v.status]}}</td>
            <td class="text-right" data-id="{{v.id}}">
            {{if v.status === 'held'}}
              <a href="javascript:;" class="btn btn-info btn-xs btn-approved">批准</a>
              {{/if}}
              <a href="javascript:;" class="btn btn-danger btn-xs btn-del">删除</a>
            </td>
          </tr>
          {{/each}}
  </script>


  <script>
  
      $(function(){

      var currentPage = 1;   //默认当前页第一页
      // 待审核（held）/ 准许（approved）/ 拒绝（rejected）/ 回收站（trashed）
      var state = {
          held:"待审核",
          approved:"准许",
          rejected:"拒绝",
          trashed: "回收站"
      }


      // 功能1：进行页面的基本渲染，发送请求获取数据，通过模板引擎渲染
      function render(page,pageSize){
        $.ajax({
          url:"./comments/comGet.php",
          data: {
            page: page||1,
            pageSize: pageSize||10
          },
          dataType:"json",
          success:function(info){
            //需要将info数组包装在一个对象中
            var obj = {
              list: info,
              state:state
            };
            console.log(obj);
            //template(模板id，数据对象) 要求是一个对象
            var htmlStr = template('tpl',obj);    //生成到htmlStr
            $('tbody').html(htmlStr);   //渲染到页面中
          }

        })

      }

      //请求第1页的数据，每页10条
      render();

    

     //功能2：实现分页功能，进行分页插件初始化
     function setPage(page){
        //获取当前总评论条数
        $.ajax({
           url:"./comments/comTotal.php",
           dataType:"json",
           success:function(info){
             //初始化分页
             $('.page-box').pagination(info.total,{
              prev_text: "« 上一页",
              next_text: "下一页 »",
              items_per_page: 10,  // 每页 10 条
              num_edge_entries: 1,   // 两侧首尾分页条目数
              num_display_entries: 5,    // 连续分页主体部分分页条目数
              current_page: page - 1 || 0,   // 当前页索引, 控制按钮亮
              load_first_page: false,  // 一进入页面不执行回调
            
             // 每次页码被点击都会被调用的函数(插件提供的)
              callback:function(index){
                // 利用封装的render方法, 渲染点击对应的页面数据
                render(index + 1);

                // 记录当前页
                currentPage = index+1;
              }
             })
           }
        })
     }
 
    setPage();


    
      // 功能3: 批准功能
      // 1. 在渲染的时候, 将需要的 id 存在 父级 td 中
      // 2. 给按钮注册点击事件
      // 3. 获取id, 发请求
      // 4. 后台接收到请求, 进行批准
      // 5. 页面重新渲染

      // 通过模板动态生成的结构, 一开始页面中没有, 不能直接绑定事件
      // 给存在的父级元素绑定事件, 由子元素触发事件即可 (事件委托)
      
      // 事件委托语法: $('父级元素').on( "事件类型", "子代元素", function() { ... })
      // 1. 可以给动态生成的结构绑定点击事件
      // 2. 因为事件是给父辈元素绑定的, 所以事件只需要绑定一次, 所有子元素都可以有事件了, 效率高
       $('tbody').on("click",".btn-approved",function(){
        //获取id,发送请求
        var id = $(this).parent().attr("data-id");

        $.ajax({
           url:"./comments/comApproved.php",
           data:{
             id: id
           },
           success:function(info){
            // 接口没有返回值
            console.log(info);
            //渲染当前页面
            render(currentPage);
           }
        })
     });
     

    
     // 功能4: 删除功能
      // 1. 在渲染的时候, 将需要的 id 存在 父级 td 中
      // 2. 给按钮注册点击事件
      //   由于按钮是通过模板动态生成的, 不能直接绑定
      //   需要通过事件委托来进行绑定事件
      // 3. 获取id, 发请求
      // 4. 后台接收到请求, 进行删除
      // 5. 页面重新渲染

    $('tbody').on("click",".btn-del",function(){
       //获取id发送请求
       var id = $(this).parent().attr("data-id");

       $.ajax({
         url:"./comments/comDel.php",
         data:{
           id: id
         },
         dataType:"json",
         success:function(info){
           console.log(info);

          //最大多少页
          var maxPage = Math.ceil(info.total/10);
          if(currentPage > maxPage){
            currentPage = maxPage;
          }
      

          //重新渲染当前页
          render(currentPage);
          //重新渲染分页标签
          setPage(currentPage);
         }
       })
    })


  // 功能5: 全选功能
  // 1. 点击全选反选按钮, 下面的复选框进行同步
  // 2. 根据全选反选按钮的状态, 显示隐藏批量处理按钮
   $('.th-chk').on("click",function(){
     //获取选中状态
     var flag = $(this).prop("checked");
    //  下面的复选框同步
    $('.tb-chk').prop("checked",flag);
    //控制批量按钮的状态
    if(flag){
      $('.btn-batch').show();
    }
    else{
      $('.btn-batch').hide();
    }
   })
  

   // 功能6：选中同步
   // 1. 只要有按钮没有被选中,  全选按钮就不被选中,  判断选中的个数和总个数的关系
   // 2. 只有两个以上的按钮被选中, 批量按钮就要显示
    $('tbody').on("click",".tb-chk",function(){
      var total = $('.tb-chk').length;   //得到总数
      var select = $('.tb-chk:checked').length;   //得到选中的全部

      //控制全选按钮的状态
      $('.th-chk').prop("checked",total===select);

      if(select >= 2){
        $('.btn-batch').show();
      }
      else{
        $('.btn-batch').hide();
      }
    })


    // 封装一个方法, 专门用于获取所有被选中的复选框的 id, 并拼接成 1,3,5 格式
    function getId(){
      var arr = [];
      //遍历被选中的复选框，将id获取，存到数组中
      $('.tb-chk:checked').each(function(){
        var id = $(this).attr("data-id");
        arr.push(id);
      })
      return arr.join(",");
    }




      // 功能7: 批量批准功能
      // 1. 点击批量批准按钮
      // 2. 获取所有被选中的按钮的 id, 拼接成  1,3,5 这种格式, 用于批量操作
      //    将id存在自定义属性中
      // 3. 请求后台接口, 进行批量批准
      // 4. 页面重新渲染
      $('.btn-approveds').on("click",function(){
        //获取到ids，传递给后台
        var ids = getId();
        $.ajax({
          url:"./comments/comApproved.php",
          data: {
            id:ids
          },
          success:function(info){
             // 页面重新渲染, 重新渲染当前页
             render(currentPage);
              // 批量操作完成, 隐藏批量操作按钮
              $('.btn-batch').hide();
              // 将全选按钮, 重置
              $('.th-chk').prop("checked",false);
          }
        })
      });

    // 功能8: 批量删除功能
      // 1. 点击批量删除按钮
      // 2. 获取所有被选中的按钮的 id, 拼接成  1,3,5 这种格式, 用于批量操作, 将id存在自定义属性中
      // 3. 请求后台接口, 进行批量删除
      // 4. 页面重新渲染
    $('.btn-dels').on("click",function(){
      // 获取选中的 id, 将值传给后台, 进行批量操作
      var ids = getId();    //3,5,6
      $.ajax({
         url:"./comments/comDel.php",
         data:{
           id:ids
         },
         dataType:"json",
         success: function(info){
           var maxPage = Math.ceil(info.total/10);
           if(currentPage>maxPage){
             currentPage = maxPage;
           }
           render(currentPage);
            setPage(currentPage);

            // 隐藏批量按钮
            $('.btn-batch').hide();
            // 重置全选按钮
            $('.th-chk').prop("checked",false);
         }
            
      })
    });







 })
  </script>
</body>
</html>
