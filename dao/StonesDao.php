<?php

class StonesDao
{
	public static $stonesDao;	
	// ユーザーをデータベースに登録する
	public function registerUser($userId, $stones) {
		$dbh = DbConnectionFactory::create();
		$sql = 'insert into ' . TABLE_NAME_STONES . ' (userid, stone) values
			(pgp_sym_encrypt(?, \'' . getenv(
			'DB_ENCRYPT_PASS') . '\'), ?) ';
		$sth = $dbh->prepare($sql);
		$sth->execute(array($userId, $stones));
	}

	//  ユーザー情報を更新
	public function updateUser($userId, $stones) {
		$dbh = DbConnectionFactory::create();
		$sql = 'update ' . TABLE_NAME_STONES . ' set stone = ? where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
		$sth = $dbh->prepare($sql);
		$sth->execute(array($stones, $userId));
	}

	// ユーザー情報をデータベースから削除
	public function deleteUser($userId) {
		$dbh = DbConnectionFactory::create();
		$sql = 'delete FROM ' . TABLE_NAME_STONES . ' where ? = pgp_sym_decrypt(userid, \'' . getenv('DB_ENCRYPT_PASS') . '\')';
		$sth = $dbh->prepare($sql);
		$flg = $sth->execute(array($userId));
	}

	// ユーザーIDを元にデータベースから情報を取得する
	public function getStonesByUserId($userId) {
		$dbh = DbConnectionFactory::create();
		$sql = 'select stone from ' . TABLE_NAME_STONES . ' where ? = 
			pgp_sym_decrypt(userid, \'' .
			getenv('DB_ENCRYPT_PASS') . '\')';
		$sth = $dbh->prepare($sql);
		$sth->execute(array($userId));
		// レコードが存在しなければ、NULL
		if (!($row = $sth->fetch())) {
			return PDO::PARAM_NULL;
		} else {
			// 石の配置を連想配列に変換して返す
			return json_decode($row['stone']);
		}
	}
}

?>