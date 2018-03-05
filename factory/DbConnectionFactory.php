<?php

class DbConnectionFactory
{
	public static function create()
	{
		if (!DbConnection::$db) {
			new DbConnection();
		}
		return DbConnection::$db;
	}
}

?>