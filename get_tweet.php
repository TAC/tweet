<?php
  require_once './Twitter.php';

  // 引数を取得
  if (!isset($_GET['user'])){
    die('user is none.');
  }
  $user = $_GET['user'];

  // サニタイズ
  $user = htmlentities($user, ENT_QUOTES);

  // twitterクラス生成
  $twitter = new Twitter();

  // 指定ユーザの最新のタイムラインをRSSから取得しDBに保存
  if(!$twitter->getRSS($user)) {
    // RSSの取得失敗
    die('RSSの取得に失敗');
  }

  // DBに保存してあるタイムラインを表示
  header('Content-type: application/json');
  echo $twitter->showTimeline($user);
?>
