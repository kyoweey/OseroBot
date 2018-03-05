<?php

class StonesDaoFactory
{
	public static function create()
	{
		if (!isset(StonesDao::$stonesDao)) {
			StonesDao::$stonesDao = new StonesDao();
		}
		return StonesDao::$stonesDao;
	}
}

?>