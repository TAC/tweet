<?php
  require_once './Twitter.php';

  // twitterクラス生成
  $twitter = new Twitter();

  // ユーザの一覧を出力
  header('Content-type: application/json');
  echo $twitter->getUserList();
?>
