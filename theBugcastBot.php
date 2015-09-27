<?php
set_time_limit(0);
$socket = fsockopen("irc.freenode.net", "6667");
if(!$socket) {
	echo "Cannot connect to freenode";
	exit;
}
fputs($socket, "USER TheBugcastBot TheBugcastBot BugcastBot BugcastBot\r\n");
fputs($socket, "NICK TheBugcastBot\r\n");
fputs($socket, "JOIN #thebugcast\r\n");

while (true) {
	$data = fgets($socket, 1024);
	if ($data != "") {
		echo $data;
		$return = explode(" ", $data);
		$text = explode(":", $data);
		if ($return[0] == "PING") {
			fputs($socket, "PONG :" . $return[1]);
		} else {
			$irc_user = getIrcUser($return[0]);
			$command = $return[1];
			if ($command == "JOIN") {
				telegram("[IRC] " . $irc_user . " joined #thebugcast");
			} elseif ($command == "PART") {
				telegram("[IRC] " . $irc_user . " left #thebugcast");
			} elseif ($command == "PRIVMSG") {
				$channel = $return[2];
				$payload = $text[2];
				if ($channel == "#thebugcast") {
					if ((ord($payload) == 1) && (substr($payload, 1, 6) == "ACTION")) {
						$message = "[IRC] " . $irc_user . " " . trim(substr($payload, 7));
					} else {
						$message = "[IRC] " . $irc_user . ": " . rtrim($payload);
					}
					telegram($message);
				}
			}
		}
	}
	usleep(100);
}

function getIrcUser($tag) {
	$arr = explode("!", $tag);
	$arr = explode(":", $arr[0]);
	return rtrim($arr[1]);
}

function telegram($message) {
	$chat_id = "-19118825"; // Bugcast Crew
	$url = "https://api.telegram.org/bot112562405:AAEV1hvzE_TGUlv52pDYK5vTql3LQ2FqoU0/sendMessage?chat_id=" . $chat_id . "&text=" . urlencode($message);
	$curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
	curl_exec($curl);
	curl_close($curl);
}

function startsWith($haystack, $needle) {
	echo "h: " . ord($haystack) . "\n";
	echo "n: " . ord($needle) . "\n";
	if ($needle != '' && strpos($haystack, $needle) === 0) return true;
	return false;
}

?>
