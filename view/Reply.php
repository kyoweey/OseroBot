<?php

class Reply 
{
	public static $reply;
		// 盤面のImagemapを返信
	public function replyImagemap($bot, $replyToken, $alternativeText, $stones, $lastStones) {
		// アクションの配列
		$actionArray = array();
		// 1つ以上のエリアが必要なためダミーのタップ可能エリアを追加
		array_push($actionArray, new LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder('-', new LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(0, 0, 1, 1)));
		$oseroGame = OseroGameFactory::create();
		// すべてのマスに対して
		for($i = 0; $i < 8; $i++) {
			// 意思が置かれていない、かつ、そこに置くと相手の石が一つでもひっくり返る場合
			for($j = 0; $j < 8; $j++) {
				if($stones[$i][$j] == 0 && $oseroGame->getFlipCountByPosAndColor($stones, $i, $j, true) > 0) {
					// タップ可能エリアとアクションを作成し配列に追加
					array_push($actionArray, new LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder('[' . ($i + 1) . ',' . ($j + 1) . ']',
						new LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(130 * $j, 130 * $i, 130, 130)));
				}
			}
		}
		// ImagemapMessageBuilderの引数は画像のURL、代替テキスト、基本比率サイズ(幅は1040固定)、アクションの配列
		$imagemapMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder('https://' . $_SERVER['HTTP_HOST'] . '/images/' . urlencode(json_encode($stones) . '|' . json_encode($lastStones)) . '/' . uniqid(), $alternativeText, new LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder(1040, 1040), $actionArray);
		$response = $bot->replyMessage($replyToken, $imagemapMessageBuilder);
		error_log('URLLL：　' . 'https://' . $_SERVER['HTTP_HOST'] . '/images/' . urlencode(json_encode($stones) . '|' . json_encode($lastStones)) . '/' . uniqid());
		error_log(json_encode($lastStones));
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}
	}

	// テキストを返信。引数はLINEBot、返信先、テキスト
	public function replyTextMessage($bot, $replyToken, $text) {
		// 返信を行いレスポンスを取得
		// TextMessageBuilderの引数はテキスト
		$response = $bot -> replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}
	}

	// 画像を返信。引数はLINEBot、返信先、画像URL、サムネイルURL
	public function replyImageMassage($bot, $replyToken, $originalImageUrl, $previewImageUrl) {
		// ImageMessageBulderの引数は、画像URL、サムネイルURL
		$response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalImageUrl, $previewImageUrl));
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}
	}

	// 位置情報を返信。引数はLINEBot、返信先、タイトル、住所、緯度、経度
	public function replyLocationMessage($bot, $replyToken, $title, $address, $lat, $lon) {
		// LocationMessageBuiderの引数はダイアログのタイトル、住所、経度、緯度
		$response = $bot -> replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title, $address, $lat, $lon));
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}	
	}

	// スタンプを返信。引数はLINEBot、返信先、スタンプのパッケージID、スタンプID
	public function replyStickerMessage($bot, $replyToken, $packageId, $stickerId) {
		// StickerMessageBuilderの引数は　スタンプのパッケージID、スタンプID
		$response = $bot -> replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\StickerMessageBuider($packageId, $stickerId));
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}
	}

	// 動画を返信。引数は　LINEBot、返信先、動画URL、サムネイルURL
	public function replyVideoMessage($bot, $replyToken, $originalContentUrl, $previewImageUrl) {
		// VideoMessageBuilderの引数は、動画URL、サムネイルURL
		$response = $bot -> replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder($originalContentUrl, $previewImageUrl));
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}	
	}

	// オーディオファイルを返信。引数はLINEBot、返信先、ファイルのURL、ファイルの再生時間
	public function replyAudioMessage($bot, $replyToken, $originalContentUrl, $audioLength) {
		// AudioMessageBuilderの引数はファイルのURL、ファイルの再生時間
		$response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\AudioMessageBuilder($originalContentUrl, $audioLength));
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}
	}

	// 複数のメッセージをまとめて返信。引数はLINEBot、返信先、メッセージ(可変長引数)
	public function replyMultiMessage($bot, $replyToken, ...$msgs) {
		// MultiMessageBuilderをインスタンス化
		$builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
		//ビルダーにメッセージを全て追加
		foreach ($msgs as $value) {
			$builder->add($value);
		}
		$response = $bot->replyMessage($replyToken, $builder);
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}		
	}

	// Buttonsテンプレートを返信。引数はLINEBot、返信先、テキスト、画像URL、タイトル、本文、アクション(可変長引数)
	public function replyButtonsTemplate($bot, $replyToken, $alternativeText, $imageUrl, $title, $text, ...$actions) {
		// アクションを格納する配列
		$actionArray = array();
		// アクションを全て追加
		foreach ($actions as $value) {
			array_push($actionArray, $value);
		}
		// TemplateMessageBuilderの引数は代替テキスト、ButtonTemplateBuilder
		$builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($alternativeText,
			// ButtonTemplateBuilderの引数はタイトル、本文、画像URL、アクションの配列
			new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder($title, $text, $imageUrl, $actionArray)
		);
		$response = $bot->replyMessage($replyToken, $builder);
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}		
	}

	// Confirmテンプレートを返信。引数はLINEBot、返信先、代替テキスト、本文、アクション(可変長)
	public function replyConfirmTemplate($bot, $replyToken, $alternativeText, $text, ...$actions) {
		$actionArray = array();
		foreach ($actions as $value) {
			array_push($actionArray, $value);
		}
		$builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($alternativeText,
			// Confirmテンプレートの引数はテキスト、アクションの配列
			new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder($text, $actionArray)
		);
		$response = $bot->replyMessage($replyToken, $builder);
		if (!$response->isSucceeded()) {
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}	
	}

	// Carouselテンプレートを返信。引数はLINEBot、返信先、代替テキスト、ダイアログの配列
	public function replyCarouselTemplate($bot, $replyToken, $alternativeText, $coulmnArray) {
		$builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($alternativeText,
			// Carouselテンプレートの引数はダイアログの配列
			new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder ($coulmnArray)
		);
		$response = $bot->replyMessage($replyToken, $builder);
		// レスポンスが以上な場合
		if (!$response->isSucceeded()) {
			// エラー内容を出力
			error_log('Failed! '. $response->getHTTPStatus . ' ' . $response->getRawBody());
		}	
	}
}

?>