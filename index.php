<?php
//  phpinfo();
//mb_language("uni");
//mb_internal_encoding("UTF-8");
//mb_http_input("UTF-8");
//mb_http_output("UTF-8");


  require_once './Twitter.php';

  // twitterクラス生成
  $twitter = new Twitter();

  // 指定ユーザの最新のタイムラインをRSSから取得
  $twitter->getRSS("tac28");

  $json = $twitter->showTimeline("tac28");
echo "<pre>";
  print_r($json);
echo "</pre>";

  exit;

$dbh = new SQLite3('test.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
if (!$dbh) {
  die("接続失敗です。<br>\n");
}

print("接続に成功しました。<br>\n");

$table_name = "test_table_o";
$ch_sql = "select count(*) from %s;";
$cr_sql = "create table %s (id int, name varchar(10))";
$sql = sprintf($ch_sql, $table_name);
$results = $dbh->querySingle($sql);
print("[". $results . "]<br>\n");
if ($results >= 0) {
  print("テーブルがある。<br>\n");
} else{
  print('テーブルがない。'."<br>\n");
  print("新規作成<br>\n");
  $sql = sprintf($cr_sql, $table_name);
  $dbh->exec($sql);
}

// SQLiteに対する処理
sqlite_close($link);

print('切断しました。<br>');


  exit;

?>
