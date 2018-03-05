<?php

// テーブル名を定義
define('TABLE_NAME_STONES', 'stones');

// エラーログのメッセージ
define('INVALID_SIGNATURE_EXCEPTION', 'parseEventRequest failed. InvalidSignatureException =>');
define('UNKNOWN_EVENT_TYPE_EXCEPTION', 'parseEventRequest failed. UnknownEventTypeException =>');
define('UNKNOWN_MESSAGE_TYPE_EXCEPTION', 'parseEventRequest failed. UnknownMessageTypeException =>');
define('INVALID_EVENT_REQUEST_EXCEPTION', 'parseEventRequest failed. InvalidEventRequestException =>');
define('NON_MESSAGE_EVENT', 'Non message event has come');
define('NON_TEXT_MESSAGE_EVENT', 'Non text message event has come');

// イベントタイプ
define('COMMAND', 'cmd_');
define('CHECK_BOARD', 'check_board');
define('CHECK_COUNT', 'check_count');
define('NEW_GAME', 'newgame');
define('HELP', 'help');

// help メッセージ
define('HELP_MESSAGE', 'あなたは常に白番です。送られた盤面上の置きたい場所をタップしてね!バグった時はオプションの盤面再送から!');

define('BOARD', '盤面');

define('GD_BASE_SIZE', 700);

?>