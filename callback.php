<!DOCTYPE html> 
<html> 
  <head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>微博批量删</title> 
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/xweibo.css" />
  </head>

  <body>
    <div class="container">
      <div class="row">
        <div class="span12 auth">
<?php
session_start();

include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

if (isset($_REQUEST['code'])) {
  $keys = array();
  $keys['code'] = $_REQUEST['code'];
  $keys['redirect_uri'] = WB_CALLBACK_URL;
  try {
    $token = $o->getAccessToken( 'code', $keys ) ;
  } catch (OAuthException $e) {
  }
}

if ($token) {
  $_SESSION['token'] = $token;
  setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );
?>
          <h1>授权成功。马上为您跳转到微博列表页面。</h1>
<?php
} else {
?>
          <h1>授权失败，请返回首页重试。</h1>
<?php
}
?>
        </div>
      </div>

      <div class="footer">
        <p>Built with all the love in the world by <a target="_blank" href="http://weibo.com/zhuoqun">@zhuoqun</a>. Powered by <a target="_blank" href="http://twitter.github.com/bootstrap/index.html">Bootstrap</a>.</p>
      </div>
    </div>

  </body>
</html>
