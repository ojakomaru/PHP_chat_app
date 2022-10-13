<?php

// データファイルの読み込み
$data_file = 'keijiban.txt';
$ext       = file_exists($data_file);
// ファイルが存在していたら読み込み各行を配列に格納する
$lines     = $ext ? file($data_file) : array();
/*================================
array(2) { [0]=> string(36) "test,ojako,最初のメッセージ " [1]=> string(37) "test2, haerChan,次のメッセージ" }
===================================*/
$errMsg    = "";
date_default_timezone_set('Asia/Tokyo');


// 「書き込む」ボタンが押されたとき
if (isset($_POST['submit'])) {
  // エラーチェック
  if (empty($_POST['name'])) {
    $errMsg = '名前を入力してください<br>';
  } elseif (empty($_POST['free'])) {
    $errMsg .= '記事を入力してください<br>';
  }

  // エラーメッセージが設定されなければ新規データを追加
  if (!$errMsg) {
    // バリデーション用の関数作成
    function convert_str($str) {
      $str = htmlspecialchars($str);
      // 改行を置換
      $str = preg_replace("/\r\n/", "<br>", $str);
      $str = preg_replace("/\r|\n/", "<br>", $str);
      return $str;
    }

    // 最初の要素だけを配列として変換
    $ln = explode(",", $lines[0]);
    // 整数３桁を指定 最新No.に＋１して追加する
    $no = $ext ? sprintf("%03d", $ln[0]+1) : "001";
    $name = convert_str($_POST['name']);
    $free = convert_str($_POST['free']);
    $delkey = !empty($_POST['delkey']) ? convert_str($_POST['delkey']) : '#####';
    $time = date("Y/m/d H:i:s");

    // 新規データをカンマ区切りテキストとして配列の先頭に追加
    $data = "$no,$name,$free,$delkey,$time\n";
    //string(62) "001, 田中　おジャコ, test2, #####, 2021/10/29 17:09:53 "
    array_unshift($lines, $data);
  }
}

// 「削除」ボタンが押されたら、指定のNo.と削除キーが既存のデータとそれぞれ一致したものを探して削除する
if (isset($_POST['delbtn']) && $ext ) {
  // 記事の数だけループを回す
  for ($i=0; $i < count($lines); $i++) {
    // 配列に変換
    $ln = explode(",", $lines[$i]);
    // カンマ区切りの０番目、３番目をそれぞれNo.削除キーと比較する
    if ($ln[0] == $_POST['no'] && $ln[3] == $_POST['Rdkey']) {
      // 配列の指定要素を削除して他の要素で置換する関数
      //array_splice(配列, 抽出開始位置, 取り出す要素数, 削除箇所に挿入する配列)
      array_splice($lines, $i, 1);
      break;
    }
  }
}

// 「書き込む」または「削除」のいずれかのボタンが押されていたら、上記の配列をファイルに書き込む
if(isset($_POST['submit']) || isset($_POST['delbtn'])) {
  $fk = fopen($data_file, "w");
  foreach ($lines as $line)
    fputs($fk, $line);
  fclose($fk);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>絵本のPHP掲示板</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div id="wrapper">
    <form action="#" method="POST">
      <div class="center">
        <div id="title">絵本の掲示板</div>
        <p>
          No：<input type="text" name="no" size="5">
          削除キー：<input type="text" name="Rdkey" size="20">
          <input type="submit" value="削除" name="delbtn">
        </p>
      </div>

      <?php
      if ($errMsg) {
        echo "<div id='errMsg'>".$errMsg."</div>";
      }
      ?>

      <div id="edit_area">
        <p>
          名前：<input type="text" name="name" size="26">
          削除キー：<input type="text" name="delkey" size="20">
        </p>
        <p>
          記事：<textarea name="free"></textarea>
        </p>
        <p class="center">
          <input type="submit" name="submit" value="書き込む">
          <input type="reset" value="取り消す">
        </p>
      </div>

      <?php
      // ファイルから一行づつ読み込みテーブルにセットする
      foreach ($lines as $line) {
        $ln = explode(",", $line);
        echo "<div><p class='entry_ID'>[No.".$ln[0]."]名前：".$ln[1]."&nbsp;";
        echo "書き込み日付：".$ln[4]."</p>";
        echo "<p>".$ln[2]."</p>";
        echo "</div><hr>";
      }
      ?>
    </form>
  </div><!-- <div id="wrapper"> -->
</body>
</html>