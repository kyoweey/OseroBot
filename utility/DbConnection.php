<?php

// データベースへの接続を管理するクラス
class DbConnection {
	// インスタンス
	public static $db;
	//　コンストラクタ
	public function __construct()
	{
		$this->initDb();		

	}

	public function initDb()
	{
		try {
			// 環境変数からデータベースへの接続情報を取得
			$url = parse_url(getenv('DATABASE_URL'));
			// データソース
			$dsn = sprintf(
				'pgsql:host=%s;dbname=%s',
				$url['host'],
				substr($url['path'],
				1
			));
			// 接続
			self::$db = new PDO($dsn, $url['user'], $url['pass']);
			// エラー時例外を投げるように設定
			self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOExcenption $e){
			echo 'Connection Error: ' . $e->getMessage();
		}
	}
}

?>