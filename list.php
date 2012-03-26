<?php
session_start();

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
$uid = $c->get_uid();
$uid = $uid['uid'];
$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

$page = 1;
$rows_per_page = 50;
$total_rows = intval($user_message['statuses_count']);

$last_page = ceil($total_rows / $rows_per_page);

$ms  = $c->user_timeline_by_id($uid, $page);

?>
<!DOCTYPE html> 
<html> 
  <head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>微博批量删</title> 
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/xweibo.css" />
    <script src="assets/js/jquery-1.7.1.min.js" type="text/javascript"></script>
  </head>

<body>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="http://mapp.cc/xweibo/">微博批量删</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="divider-vertical"></li>
              <li><a href="#">关于</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div><!-- #navbar -->

    <div class="container">
      <div class="row">
        <div class="span8 wblist">

          <div class="row">
            <div class="span7">
              <h1>我的微博列表 <span>(共 <?php echo $total_rows; ?> 条)</span></h1>
            </div>
            <div class="span1">
              <input type="checkbox" class="check_all" /> 全选
            </div>
          </div>
<?php

if (is_array($ms['statuses'])) {
  foreach ($ms['statuses'] as $item) {

    $retweet = '';
    if (isset($item['retweeted_status']) && !empty($item['retweeted_status'])) {

      $retweeted_status = $item['retweeted_status'];

      $retweeted_img = '';
      if (isset($retweeted_status['thumbnail_pic']) && !empty($retweeted_status['thumbnail_pic'])) {
        $retweeted_img = '<img src="'. $retweeted_status['thumbnail_pic'] .'" />';
      }

      $retweet = <<<HTML
              <div class="retweet">
                <p>{$retweeted_status['user']['screen_name']}: {$retweeted_status['text']}</p>
                {$retweeted_img}
              </div>
HTML;
    }

    $thumbnail = '';
    if (isset($item['thumbnail_pic']) && !empty($item['thumbnail_pic'])) {
        $thumbnail = '<img src="'. $item['thumbnail_pic'] .'" />';
    }

    echo <<<HTML
          <div class="row item">
            <div class="span7 content">
              <p>{$item['text']}</p>
              {$thumbnail}
              {$retweet}
            </div>
            <div class="span1 ctrl">
              <input type="checkbox" />
            </div>
          </div>
HTML;
  }
}

?>

          <div class="row">
            <div class="span7">
              &nbsp;
            </div>
            <div class="span1 bottom_ctrl">
              <input type="checkbox" class="check_all" /> 全选
            </div>
          </div>

          <div class="pagination">
            <ul>
              <li class="disabled"><a href="#">«</a></li>
              <li class="active">
              <a href="#">1</a>
              </li>
              <li><a href="#">2</a></li>
              <li><a href="#">3</a></li>
              <li><a href="#">4</a></li>
              <li class="disabled"><a href="#">...</a></li>
              <li><a href="#">10</a></li>
              <li><a href="#">11</a></li>
              <li><a href="#">»</a></li>
            </ul>
          </div>
        </div>

        <div class="span4">
          <button id="del_btn" class="btn btn-danger btn-large" disabled="disabled"><i class="icon-trash icon-white"></i> 批量删除</button>
        </div>
      </div>

      <div class="footer">
        <p>Built with all the love in the world by <a target="_blank" href="http://weibo.com/zhuoqun">@zhuoqun</a>. Powered by <a target="_blank" href="http://twitter.github.com/bootstrap/index.html">Bootstrap</a>.</p>
      </div>

    </div><!-- #container -->

    <script type="text/javascript">
      $(document).ready(function(){
        $('.wblist').on('click', '.item', function(e){
          $(this).toggleClass('selected');

          var isCheckbox = $(e.target).prop('tagName') == 'INPUT';

          if ($(this).find('input').prop('checked')) {
            if (!isCheckbox) {
              $(this).find('input').prop('checked', false);
            }
            $('.check_all').prop('checked', false);
          } else {
            if (!isCheckbox) {
              $(this).find('input').prop('checked', true);
            }
          }

          toggleDelBtn();
        });

        $('.check_all').change(function(){

          if ($(this).prop('checked')) {
            $('.check_all').prop('checked', true);
            $('.item').addClass('selected');
            $('.item input').prop('checked', true);
            toggleDelBtn();
          } else {
            $('.check_all').prop('checked', false);
            $('.item').removeClass('selected');
            $('.item input').prop('checked', false);
            toggleDelBtn();
          }
          });

        function toggleDelBtn() {
          if ($('.wblist .selected').length == 0) {
            $('#del_btn').prop('disabled', true);
          } else {
            $('#del_btn').prop('disabled', false);
          }
        }
      });
    </script>

</body>
</html>
