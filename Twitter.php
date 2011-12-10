<?php

class Twitter
{
  // TwitterのRSSのURL
  var $twitter_url = "http://twitter.com/statuses/user_timeline/%s.rss";

  // DBH
  var $dbh;

  // コンストラクタ
  public function __construct()
  {
    // DBへ接続
    $this->dbh = new SQLite3('twitter.db', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    if (!$this->dbh) {
        die("DB接続失敗です。");
    }

    // tweet保管用テーブルの存在確認
    $sql = "select count(*) from tweet";
    $result = $this->dbh->exec($sql);
    if(!$result){
      // テーブルがないので作成
      $sql = "create table tweet (user, guid, date, title, link)";
      $this->dbh->exec($sql);
    }
  }

  // 指定ユーザの最新のRSSを取得し、DBへ保存する
  public function getRSS($user){

    // 指定ユーザのGUIDの最大値を取得
    $sql = sprintf("select max(guid) from tweet where user = '%s'",
                   $user);
    $maxguid = $this->dbh->querySingle($sql);
    if($maxguid === ""){
      $maxguid = 0;
    }

    // RSSのURLを作成
    $rss_url = sprintf($this->twitter_url, $user);

    // RSSからデータを取得
    $rssdata = simplexml_load_file($rss_url);

    // 取得済みより後のguidのtweetを取得する
    foreach ($rssdata->channel->item as $rssitem) { 
          $words = explode("/", $rssitem->guid);
          $guid = end($words);
          if($maxguid >= $guid){
            continue;
          }
          $tweets[] = array('guid'  => $guid,
                            'date'  => $rssitem->pubDate,
                            'title' => $rssitem->title,
                            'link'  => $rssitem->link);
    }

    // 取得したtweetをDBへ保存
    $stmt = $this->dbh->prepare('insert into tweet (user, guid, date, title, link) values (:user, :guid, :date, :title, :link)');
    foreach ($tweets as $tweet) {
      // パラメータを設定
      $stmt->bindValue(':user',  $user,           SQLITE3_TEXT);
      $stmt->bindValue(':guid',  $tweet['guid'],  SQLITE3_TEXT);
      $stmt->bindValue(':date',  $tweet['date'],  SQLITE3_TEXT);
      $stmt->bindValue(':title', $tweet['title'], SQLITE3_TEXT);
      $stmt->bindValue(':link',  $tweet['link'],  SQLITE3_TEXT);

      // SQL文を実行
      $stmt->execute();

      // 設定された値をクリア
      $stmt->clear();
    }

    // DB切断
    sqlite_close($dbh);

    return true;
  }

  // タイムラインを表示する
  public function showTimeline($user){
    // DBから指定ユーザのtweetを取得
    $sql = sprintf("select * from tweet where user = '%s' order by guid desc",
                   $user);
    $result = $this->dbh->query($sql);

    // tweetを配列に変換
    if($result){
      while($row = $result->fetchArray()) {
        $tweets[] = array('guid'  => $row[guid],
                          'date'  => $row[date],
                          'title' => $row[title],
                          'link'  => $row[link]);
      }
    }

    // 配列をJSONに変換して返す
    return json_encode($tweets);
  }

  // DBにtweetを保管しているユーザの一覧を取得する
  public function getUserList(){
    // DBからユーザ一覧を取得する
    $sql = "select distinct user from tweet order by user";
    $result = $this->dbh->query($sql);

    // ユーザ一覧を配列へ入れる
    if($result){
      while($row = $result->fetchArray()) {
        $user_list[] = array('user'=> $row[user]);
      }
    }

    // 配列をJSONに変換して返す
    return json_encode($user_list);
  }
}

?>
