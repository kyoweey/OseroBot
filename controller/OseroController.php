<?php

class oseroController
{

	public function checkBoard($bot, $event)
	{
		$reply = ReplyFactory::create();
		$stonesDao = StonesDaoFactory::create();
		$stones = $stonesDao->getStonesByUserId($event->getUserId());
		$reply->replyImagemap($bot, $event->getReplyToken(), BOARD, $stones, null);
	}

	public function checkCount($bot, $event)
	{
		$reply = ReplyFactory::create();
		$stonesDao = StonesDaoFactory::create();
		$oseroGame = OseroGameFactory::create();
		$stones = $stonesDao->getStonesByUserId($event->getUserId());
		$resultCountStones = $oseroGame->countStones($stones);
		$reply->replyTextMessage($bot, $event->getReplyToken(), sprintf('白： %d、黒： %d', $resultCountStones['white'], $resultCountStones['black']));
	}

	public function newGame($bot, $event)
	{
		$reply = ReplyFactory::create();
		$oseroGame = OseroGameFactory::create();
		$stonesDao = StonesDaoFactory::create();
		$stonesDao->deleteUser($event->getUserId());
		$stones = $oseroGame->setStonesForStart($event);
		$reply->replyImagemap($bot, $event->getReplyToken(), BOARD, $stones, null);
	}

	public function help($bot, $event)
	{
		$reply = ReplyFactory::create();
		$reply->replyTextMessage($bot, $event->getReplyToken(), HELP_MESSAGE);
	}

	public function startGame($bot, $event)
	{
		$reply = ReplyFactory::create();
		$oseroGame = OseroGameFactory::create();
		$stones = $oseroGame->setStonesForStart($event);
		$reply->replyImagemap($bot, $event->getReplyToken(), BOARD, $stones, null);
	}

	public function putStone($bot, $event)
	{
		$reply = ReplyFactory::create();
		$oseroGame = OseroGameFactory::create();
		$stonesDao = StonesDaoFactory::create();
		$stones = $stonesDao->getStonesByUserId($event->getUserId());
		$lastStones = $oseroGame->setStonesForGame($bot, $event, $stones);
		$reply->replyImagemap($bot, $event->getReplyToken(), BOARD, $stones, $lastStones);
	}
}

?>