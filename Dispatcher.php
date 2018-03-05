<?php

class Dispatcher
{
	public function dispatch()
	{
		// アクセストークンを使いCurlHTTPClientをインスタンス化
		$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));

		// CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
		$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

		$reply = ReplyFactory::create();

		// LINE Messaging APIがリクエストに付与した署名を取得
		$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

		$oseroGame = OseroGameFactory::create();

		$stonesDao = StonesDaoFactory::create();

		//$controllerInstance = new OseroController();
		$controllerInstance = OseroControllerFactory::create();

		if ($_REQUEST['stones']) {
			$controllerInstance->boardImageGenerate();
		}

		// 署名が正当化チェック。政党であればリクエストをパースし配列へ。不正であれば例外の内容を出力。
		try {
			$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
		} catch (\LINE\LINEBot\Exception\InvalidSignatureException $e) {
			error_log(INVALID_SIGNATURE_EXCEPTION . var_export($e, true));
		} catch (\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
			error_log(UNKNOWN_EVENT_TYPE_EXCEPTION . var_export($e, true));
		} catch (\LINE\LINEBot\Exception\unknownMessageTypeException $e) {
			error_log(UNKNOWN_MESSAGE_TYPE_EXCEPTION . var_export($e, true));
		} catch (\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
			error_log(INVALID_EVENT_REQUEST_EXCEPTION . var_export($e, true));
		}

		// 配列に格納された各イベントをループで処理
		foreach ($events as $event) {
			// MessageEventクラスのインスタンスでなければスキップ
			if (!($event instanceOf \LINE\LINEBot\Event\MessageEvent)) {
				error_log(NON_MESSAGE_EVENT);
				continue;
			}
			// TextMessageクラスのインスタンスでなければ処理をスキップ
			if (!($event instanceOf \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
				error_log(NON_TEXT_MESSAGE_EVENT);
				continue;
			}
			// リッチコンテンツがタップされた時
			if(substr($event->getText(), 0, 4) == COMMAND) {
				// 盤面の確認
				if (substr($event->getText(), 4) == CHECK_BOARD) {
					if ($stonesDao->getStonesByUserId($event->getUserId()) != PDO::PARAM_NULL) {
						$controllerInstance->checkBoard($bot, $event);
						break;
					}
				}
				// 情勢の確認
				elseif(substr($event->getText(), 4) == CHECK_COUNT) {
					if ($stonesDao->getStonesByUserId($event->getUserId()) != PDO::PARAM_NULL) {
						$controllerInstance->checkCount($bot, $event);
						break;
					}
				// ゲームを中断し新ゲームを開始
				}elseif(substr($event->getText(), 4) == NEW_GAME) {
					$controllerInstance->newgame($bot, $event);
					break;
				// 遊び方
				}elseif(substr($event->getText(), 4) == HELP) {
					$controllerInstance->help($bot, $event);
					break;
				}
			}
			// ユーザーの情報がデータベースに存在しない場合、
			if ($stonesDao->getStonesByUserId($event->getUserId()) === PDO::PARAM_NULL) {
				$controllerInstance->startGame($bot, $event);
				break;
			// 存在する場合
			} else {
				$controllerInstance->putStone($bot, $event);
				break;
			}
		}
	}
}

?>