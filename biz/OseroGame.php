<?php

class OseroGame
{
	public static $oseroGame;

	// ゲームオーバー
	public function endGame($bot, $replyToken, $userId, $stones) {
		// それぞれの石の数をカウント
		$white = 0;
		$black = 0;
		for($i = 0; $i < count($stones); $i++) {
			for($j = 0; $j < count($stones[$i]); $j++) {
				if ($stones[$i][$j] == 1) {
					$white++;
				}else if ($stones[$i][$j] == 2) {
					$black++;
				}
			}
		}
		// 送るテキスと
		if ($white == $black) {
			$message = '引き分け! ' . sprintf('白： %d、黒： %d', $white, $black);
		} else {
			$message = ($white > $black ? 'あなた' : 'AI') . 'の勝ち! ' . sprintf('白： %d、黒： %d', $white, $black);
		}
		// 盤面とダミーエリアのみのImagemapを生成
		$actionArray = array();
		array_push($actionArray, new LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder('-', new LINE\LINEBot\ImagemapActionBuilder\AreaBuilder(0, 0, 1, 1)));
		$imagemapMessageBuilder = new LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder ('https://' . $_SERVER['HTTP_HOST'] . '/images/' . urlencode(json_encode($stones) . '/' . uniqid()), $message, new LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder(1040, 1040), $actionArray);
		// テキストメッセージ
		$textMessage = new LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
		// スタンプのメッセージ
		$stickerMessage = ($white >= $black)
			? new LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, 114)
			: new LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, 111);
		// データベースから削除
		$stonesDao = StonesDaoFactory::create();
		$stonesDao->deleteUser($userId);
		$reply = ReplyFactory::create();
		// Imagemap、　テキスト、　スタンプを送信
		$reply->replyMultiMessage($bot, $replyToken, $imagemapMessageBuilder, $textMessage, $stickerMessage);
	}

	// 石が置ける場所があるかを調べる。引数は現在の石の配置、石の色
	public function getCanPlaceByColor($stones, $isWhite) {
		for($i = 0; $i < count($stones); $i++) {
			for($j = 0; $j < count($stones[$i]); $j++) {
				if ($stones[$i][$j] == 0) {
					if ($this->getFlipCountByPosAndColor($stones, $i, $j, $isWhite) > 0) {
						return true;
					}
				}
			}
		}
		return false;
	}

	// 敵の石を置く
	public function placeAIStone(&$stones) {
		// 強い場所の配列。強い順。
		$strongArray = [0, 7, 56, 63, 2, 5, 16, 18, 21, 23, 40, 42, 45, 47, 58, 61];
		// 弱い場所の配列。強い順。
		$weakArray = [1, 6, 8, 15, 48, 57, 55, 62, 9, 14, 49, 54];
		// どちらにも属さない場所の配列
		$otherArray = [];
		for($i = 0; $i < count($stones) * count($stones[0]); $i++) {
			if (!in_array($i, $strongArray) && !in_array($i, $weakArray)) {
				array_push($otherArray, $i);
			}
		}
		// ランダム性を持たせるためシャッフル
		shuffle($otherArray);
		// すべてのマスの強い+普通+弱い
		$posArray = array_merge($strongArray, $otherArray, $weakArray);
		// 1つずつそこにおけるかをチェックし、可能なら置いて処理を終える
		for($i = 0; $i < count($posArray); ++$i) {
			$pos = [$posArray[$i] / 8, $posArray[$i] % 8];
			if ($stones[$pos[0]][$pos[1]] == 0) {
				if ($this->getFlipCountByPosAndColor($stones, $pos[0], $pos[1], false)) {
					$this->placeStone($stones, $pos[0], $pos[1], false);
					break;
				}
			}
		}
	}

	// 石の数を数える
	public function countStones($stones) {
		$white = 0;
		$black = 0;
		for ($i = 0; $i < count($stones); $i++) {
			for ($j = 0; $j < count($stones[$i]); $j++) {
				if ($stones[$i][$j] == 1) {
					$white++;
				}elseif($stones[$i][$j] == 2) {
					$black++;
				}
			}
		}
		return array ('white' => $white, 'black' => $black);
	}

	// ゲーム開始時の石の配置
	public function setStonesForStart($event) {
		$stonesDao = StonesDaoFactory::create();
		// ゲーム開始時の石の配置
		$stones =
		[
			[0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 1, 2, 0, 0, 0],
			[0, 0, 0, 2, 1, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0],
			[0, 0, 0, 0, 0, 0, 0, 0],
		];
		// ユーザーをデータベースに登録
		$stonesDao->registerUser($event->getUserId(), json_encode($stones));
		return $stones;
	}

	public function setStonesForGame($bot, $event, &$stones) {
		$lastStones = $stones;
		$stonesDao = StonesDaoFactory::create();
		// 入力されたテキストを[行, 列]の配列に変換
		$tappedArea = json_decode($event->getText());
		// ユーザーの石を置く
		$this->placeStone($stones, $tappedArea[0] - 1, $tappedArea[1] - 1, true);
		// 相手の意思を置く
		$this->placeAIStone($stones);
		// ユーザーの情報を更新
		$stonesDao->updateUser($event->getUserId(), json_encode($stones));
		// ユーザーも相手も意思を置くことができない時
		if (!$this->getCanPlaceByColor($stones, true) && !$this->getCanPlaceByColor($stones, false)) {
			//ゲームオーバー
			$this->endGame($bot, $event->getReplyToken(), $event->getUserId(), $stones);
		// 相手のみが置ける場合
		} elseif (!$this->getCanPlaceByColor($stones, true) && $this->getCanPlaceByColor($stones, false)) {
			// ユーザーが置けるようになるまで相手が意思を置く
			while (!$this->getCanPlaceByColor($stones, true)) {
				$this->placeAIStone($stones);
				$this->updateUser($bot, json_encode($stones));
				// どちらもおけなくなったらゲームオーバー
				if (!$this->getCanPlaceByColor($stones, true) && !$this->getCanPlaceByColor($stones, false)) {
					$this->endGame($bot, $event->getReplyToken(), $event->getUserId(), $stones);
				}
			}
		}
		return $lastStones;
	}

	// 石を置く。石の配置は参照渡し
	public function placeStone(&$stones, $row, $col, $isWhite) {
		// ひっくり返す。処理の流れは getFlipCountByPosAndColor とほぼ同じ
		$directions = [[-1, 0], [-1, 1], [0, 1], [1, 0], [1, 1], [1, 0], [1, -1], [0, -1], [-1, -1]];
		// すべての方向をチェック
		for ($i = 0; $i < count($directions); ++$i) {
			// 置く場所からの距離。1つずつ進みながらチェックしていく
			$cnt = 1;
			// 行の距離
			$rowDiff = $directions[$i][0];
			// 列の距離
			$colDiff = $directions[$i][1];
			// 狭める可能性がある数
			$flipCount = 0;

			while (true) {
				// 盤面の外に出たらループを出る
				if (!isset($stones[$row + $rowDiff * $cnt]) || !isset($stones[$row + $rowDiff * $cnt][$col + $colDiff * $cnt])) {
					$flipCount = 0;
					break;
				}
				// 相手の石なら$flipCountを加算
				if ($stones[$row + $rowDiff * $cnt][$col + $colDiff * $cnt] == ($isWhite ? 2 : 1)) {
					$flipCount++;
				// 自分の意思ならループを抜ける
				} elseif ($stones[$row + $rowDiff * $cnt][$col + $colDiff * $cnt] == ($isWhite ? 1 : 2)) {
					if ($flipCount > 0) {
						// ひっくり返す
						for($i = 0; $i < $flipCount; ++$i) {
							$stones[$row + $rowDiff * ($i + 1)][$col + $colDiff * ($i + 1)] = ($isWhite ? 1 : 2);
						}
					}
					break;
				// どちらの石も置かれていなければループを抜ける
				} elseif ($stones[$row + $rowDiff * $cnt][$col + $colDiff * $cnt] == 0) {
					$flipCount = 0;
					break;
				}
				// 1個進める
				$cnt++;
			}
		}
		// 新たに石を置く
		$stones[$row][$col] = ($isWhite ? 1 : 2);
	}

	// そこに置くと相手の意思が何個ひっくり返るかを返す
	// 引数は現在の配置、行、列、石の色
	public function getFlipCountByPosAndColor($stones, $row, $col, $isWhite) {
		$total = 0;
		// 石から見た各方向への行、列の数の差
		$directions = [[-1, 0], [-1, 1], [0, 1], [1, 0], [1, 1], [1, 0], [1, -1], [0, -1], [-1, -1]];

		// すべての方向をチェック
		for ($i = 0; $i < count($directions); ++$i) {
			// 置く場所からの距離。1つずつ進みながらチェックしていく
			$cnt = 1;
			// 行の距離
			$rowDiff = $directions[$i][0];
			// 列の距離
			$colDiff = $directions[$i][1];
			// 狭める可能性がある数
			$flipCount = 0;

			while (true) {
				// 盤面の外に出たらループを出る
				if (!isset($stones[$row + $rowDiff * $cnt]) || !isset($stones[$row + $rowDiff * $cnt][$col + $colDiff * $cnt])) {
					$flipCount = 0;
					break;
				}
				// 相手の石なら$flipCountを加算
				if ($stones[$row + $rowDiff * $cnt][$col + $colDiff * $cnt] == ($isWhite ? 2 : 1)) {
					$flipCount++;
				// 自分の意思ならループを抜ける
				} elseif ($stones[$row + $rowDiff * $cnt][$col + $colDiff * $cnt] == ($isWhite ? 1 : 2)) {
					break;
				// どちらの石も置かれていなければループを抜ける
				} elseif ($stones[$row + $rowDiff * $cnt][$col + $colDiff * $cnt] == 0) {
					$flipCount = 0;
					break;
				}
				// 1個進める
				$cnt++;
			}
			// 加算
			$total += $flipCount;
		}
		// ひっくり返る総数を返す
		return $total;
	}
}

?>