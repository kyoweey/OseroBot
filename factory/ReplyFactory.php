<?php

class ReplyFactory
{
	public static function create()
	{
		if (!isset(Reply::$reply)) {
			Reply::$reply = new Reply();
		}
		return Reply::$reply;
	}
}

?>