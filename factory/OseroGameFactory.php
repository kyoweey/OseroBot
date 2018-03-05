<?php

class OseroGameFactory
{
	public static function create()
	{
		if (!isset(OseroGame::$oseroGame)) {
			OseroGame::$oseroGame = new OseroGame();
		}
		return OseroGame::$oseroGame;
	}
}

?>