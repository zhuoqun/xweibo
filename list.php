<?php
session_start();

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

// if not access the auth
if (!isset($_SESSION['token']) || empty($_SESSION['token']) || empty($_SESSION['token']['access_token']))
{
  header('Location:http://mapp.cc/xweibo/');
  die;
}

$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
$uid = $c->get_uid();
$uid = $uid['uid'];
$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息

// Delete weibo by ids
$tip = '';
if (isset($_POST['del_ids']) && !empty($_POST['del_ids']))
{
  $id_arr = explode(',', $_POST['del_ids']);
  foreach ($id_arr as $id)
  {
    $result = $c->delete($id);

    if (isset($result['error']) && !empty($result['error']))
    {
      // fail
      $err_content = "错误原因:" .$result['error']. " -- 错误代码:" .$result['error_code'];
      error_log("id:". $id ." -- error:" .$result['error']. " -- error_code:" .$result['error_code']. "\n\n");
      $tip =<<<HTML
          <div id="tip_error" class="row">
            <div class="span7">
              <div class="error">
                删除失败：{$err_content}
              </div>
            </div>
            <div class="span1">
            </div>
          </div>
HTML;
    }
    else
    {
      //success
      $tip =<<<HTML
          <div id="tip_success" class="row">
            <div class="span7">
              <div class="success">
                删除成功！
              </div>
            </div>
            <div class="span1">
            </div>
          </div>
HTML;
    }
  }
}

$form_action = 'http://mapp.cc/xweibo/list.php';

/* for paginame bug
if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
{
  $form_action .= '?'.$_SERVER['QUERY_STRING'];
}
 */

define('SHOW_PAGES', 5);

$rows_per_page = 50;
$total_rows = intval($user_message['statuses_count']);
$last_page = ceil($total_rows / $rows_per_page);

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

if ($page < 1) $page = 1;
if ($page > $last_page) $page = $last_page;

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
          <a class="brand" href="http://mapp.cc/xweibo/list.php">微博批量删</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="divider-vertical"></li>
              <li><a href="http://www.douban.com/note/207231362/" target="_blank">关于</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div><!-- #navbar -->

    <div class="container">
      <div class="row">
        <div class="span8 wblist">

<?php echo $tip; ?>

          <div class="row">
            <div class="span7">
              <h1>我的微博列表 <span>(共 <?php echo $total_rows; ?> 条)</span></h1>
            </div>
            <div class="span1">
              <input type="checkbox" class="check_all" /> 全选
            </div>
          </div>
<?php

if (is_array($ms['statuses']))
{
  foreach ($ms['statuses'] as $item)
  {

    $retweet = '';
    if (isset($item['retweeted_status']) && !empty($item['retweeted_status']))
    {

      $retweeted_status = $item['retweeted_status'];

      $retweeted_img = '';
      if (isset($retweeted_status['thumbnail_pic']) && !empty($retweeted_status['thumbnail_pic']))
      {
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
    if (isset($item['thumbnail_pic']) && !empty($item['thumbnail_pic']))
    {
        $thumbnail = '<img src="'. $item['thumbnail_pic'] .'" />';
    }

    echo <<<HTML
          <div id="{$item['id']}" class="row item">
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
<?php
$pagination = '';

$first_page_class = ($page == 1) ? ' class="disabled"' : '';
$last_page_class = ($page == $last_page) ? ' class="disabled"' : '';
$second_to_last = $last_page - 1;

$pagination .=<<<HTML
              <li{$first_page_class}><a href="?page=1">«</a></li>
HTML;

if ($last_page <= SHOW_PAGES + 4)
{
  for ($i=1; $i <= $last_page; $i++)
  {
    $active_class = ($i == $page) ? ' class="active"' : '';
    $pagination .=<<<HTML
              <li{$active_class}><a href="?page={$i}">{$i}</a></li>
HTML;
  }
}
else
{
  if ($page <= (SHOW_PAGES - 1)/2 + 3)
  {
    for ($i=1; $i <= SHOW_PAGES + 2; $i++)
    {
      $active_class = ($i == $page) ? ' class="active"' : '';
      $pagination .=<<<HTML
              <li{$active_class}><a href="?page={$i}">{$i}</a></li>
HTML;
    }

    $pagination .=<<<HTML
              <li class="disabled"><a>...</a></li>
              <li><a href="?page={$second_to_last}">{$second_to_last}</a></li>
              <li><a href="?page={$last_page}">{$last_page}</a></li>
HTML;
  } 
  else
  {
    $pagination .=<<<HTML
              <li><a href="?page=1">1</a></li>
              <li><a href="?page=2">2</a></li>
              <li class="disabled"><a>...</a></li>
HTML;

    $section_tail = $last_page - 3 - (SHOW_PAGES - 1)/2;
    if ($page > $section_tail) 
    {
      for ($i = $last_page - SHOW_PAGES - 1; $i <= $last_page; $i++)
      {
        $active_class = ($i == $page) ? ' class="active"' : '';
        $pagination .=<<<HTML
              <li{$active_class}><a href="?page={$i}">{$i}</a></li>
HTML;
      }
    }
    else
    {
      for ($i = $page - (SHOW_PAGES - 1)/2; $i <= $page + (SHOW_PAGES - 1)/2; $i++)
      {
        $active_class = ($i == $page) ? ' class="active"' : '';
        $pagination .=<<<HTML
              <li{$active_class}><a href="?page={$i}">{$i}</a></li>
HTML;
      }

      $pagination .=<<<HTML
              <li class="disabled"><a>...</a></li>
              <li><a href="?page={$second_to_last}">{$second_to_last}</a></li>
              <li><a href="?page={$last_page}">{$last_page}</a></li>
HTML;
    }
  }
}

$pagination .=<<<HTML
              <li{$last_page_class}><a href="?page={$last_page}">»</a></li>
HTML;

echo $pagination;
?>
            </ul>
          </div>
        </div>

        <div class="span4">
          <form id="del_form" method="post" action="<?php echo $form_action; ?>">
            <input type="hidden" id="del_ids" name="del_ids" />
          </form>
          <button id="del_btn" class="btn btn-danger btn-large" disabled="disabled"><i class="icon-trash icon-white"></i> 批量删除</button>
        </div>
      </div>

      <div class="footer">
        <p>Built with all the love in the world by <a target="_blank" href="http://weibo.com/zhuoqun">@zhuoqun</a>. Powered by <a target="_blank" href="http://twitter.github.com/bootstrap/index.html">Bootstrap</a>.</p>
      </div>

    </div><!-- #container -->

    <script type="text/javascript">
      $(document).ready(function(){

        var id_arr = [];

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
          var selected_items = $('.wblist .selected');
          if (selected_items.length == 0) {
            $('#del_btn').prop('disabled', true);
          } else {
            id_arr = [];

            selected_items.each(function(){
              id_arr.push($(this).attr('id'));
            });

            $('#del_btn').prop('disabled', false);
          }
        }

        $('#del_btn').click(function(e){
          e.preventDefault();
          $('#del_ids').val(id_arr.join(','));
          $('#del_form').submit();
        });

        if($('#tip_success')) {
          setTimeout(function(){$('#tip_success').remove();}, 3000);
        }
      });
    </script>

</body>
</html>
