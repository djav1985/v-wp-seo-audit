<?php
class Validation {
	private $domain;
	private $htmlValidator = 'https://validator.w3.org/nu/?doc=%s&out=json';

	public function __construct($domain) {
		$this -> domain = $domain;
	}

	public function w3cHTML() {
		$url = sprintf($this -> htmlValidator, urlencode('http://'.$this -> domain));
        $d = array(
            'status'=>false,
            'errors'=>0,
            'warnings'=>0,
        );
        if(!$response = @json_decode(V_WPSA_Utils::curl($url), true)) {
            return $d;
        }
        //var_dump($response);
        //die();

        if(empty($response['messages'])) {
            $d['status'] =  true;
            return $d;
        }
        $numOfErrors = 0;
        $numOfWarnings = 0;
        foreach($response['messages'] as $message) {
            if($message['type'] == 'info' AND isset($message['subType']) AND $message['subType'] == 'warning') {
                $numOfWarnings++;
                continue;
            }
            if($message['type'] == 'error' OR $message['type'] == 'non-document-error') {
                $numOfErrors++;
            }
        }
        $d['status'] = !($numOfErrors > 0);
        $d['errors'] = $numOfErrors;
        $d['warnings'] = $numOfWarnings;
        return $d;
	}

    // OLD API. Not used.
	private function request($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1090.0 Safari/536.6');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		$buffer = (string) curl_exec($ch);
		$curl_info = curl_getinfo($ch);
		if(curl_errno($ch)) {
			return array(
				'status' => '-',
				'errors' => '-',
				'warnings' => '-',
			);
		}
		curl_close($ch);
		$header_size = $curl_info['header_size'];
		$headers = $this -> getHeaders(mb_substr($buffer, 0, $header_size));
		return array(
			'status' => isset($headers['X-W3C-Validator-Status']) ? mb_strtolower($headers['X-W3C-Validator-Status']) == 'valid' : false,
			'errors' => isset($headers['X-W3C-Validator-Errors']) ? (int) $headers['X-W3C-Validator-Errors'] : 0,
			'warnings' => isset($headers['X-W3C-Validator-Warnings']) ? (int) $headers['X-W3C-Validator-Warnings'] : 0,
		);
	}

	private function getHeaders($response) {
		$headers = array();
		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
		foreach (explode("\r\n", $header_text) as $i => $line)
			if ($i === 0)
				$headers['Http Code'] = $line;
			else {
				list ($key, $value) = explode(': ', $line);
				$headers[$key] = $value;
			}
		return $headers;
	}
}