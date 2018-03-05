<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/factory/OseroControllerFactory.php';
require_once __DIR__ . '/factory/ReplyFactory.php';
require_once __DIR__ . '/factory/DispatcherFactory.php';
require_once __DIR__ . '/factory/OseroGameFactory.php';
require_once __DIR__ . '/factory/StonesDaoFactory.php';
require_once __DIR__ . '/factory/DbConnectionFactory.php';
require_once __DIR__ . '/view/Reply.php';
require_once __DIR__ . '/utility/DbConnection.php';
require_once __DIR__ . '/dao/StonesDao.php';
require_once __DIR__ . '/controller/OseroController.php';
require_once __DIR__ . '/biz/OseroGame.php';
require_once __DIR__ . '/Dispatcher.php';

$dispatcher = DispatcherFactory::create();
$dispatcher->dispatch();

?>