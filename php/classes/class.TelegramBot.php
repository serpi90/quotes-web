<?php

class TelegramBot {
	private $token;
	private $apiURL;

	public function __construct( $token ) {
		$this->token = $token;	
		$this->apiURL = 'https://api.telegram.org/bot'.$this->token.'/';
	}

	public function sendMessage( $chatId, $message, $markdown = false ) {
		$parameters = array('chat_id' => $chatId, "text" => $message );
		if( $markdown ) {
			$parameters['parse_mode'] = 'Markdown';
		} 
		return $this->apiRequest("sendMessage", $parameters );
	}

	private function exec_curl_request($handle) {
		$response = curl_exec($handle);

		if ($response === false) {
			$errno = curl_errno($handle);
			$error = curl_error($handle);
			error_log("Curl returned error $errno: $error\n");
			curl_close($handle);
			return false;
		}

		$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
		curl_close($handle);

		if ($http_code >= 500) {
			// do not wat to DDOS server if something goes wrong
			sleep(10);
			return false;
		} else if ($http_code != 200) {
			echo $response;
			$response = json_decode($response, true);
			error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
			if ($http_code == 401) {
				throw new Exception('Invalid access token provided');
			}
			return false;
		} else {
			$response = json_decode($response, true);
			if (isset($response['description'])) {
				error_log("Request was successfull: {$response['description']}\n");
			}
			$response = $response['result'];
		}

		return $response;
	}

	private function apiRequest($method, $parameters=array() ) {
		if (!$parameters) {
			$parameters = array();
		}

		foreach ($parameters as $key => &$val) {
			if (!is_numeric($val) && !is_string($val)) {
				$val = json_encode($val);
			}
		}
		$url = $this->apiURL.$method.'?'.http_build_query($parameters);

		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($handle, CURLOPT_TIMEOUT, 60);

		return $this->exec_curl_request($handle);
	}
}
?>

