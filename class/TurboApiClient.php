<?php

class TurboApiException extends Exception {

    public function __construct($message, $code){

        $message = sprintf(__("Error %d: %s"), $code, $message);
        parent::__construct($message);
    }

}

class TurboApiClient {

    protected $username;
    protected $password;
    public $lastError;
    private $serverUrl = "https://api.turbo-smtp.com/api";

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

	public function get($path, $params = array()) {

		$url = sprintf("%s%s", $this->serverUrl, $path);

		$auth = array(
			'authuser' => $this->username,
			'authpass' => $this->password
		);

		$params = array_merge($params, $auth);

		return wp_remote_get($url."?".custom_http_build_query($params, false));

	}

    protected function authorize() {

        $response = $this->get("/");
        $status_code = wp_remote_retrieve_response_code($response);

        if ( $status_code == 412 ) {
            $body = json_decode(wp_remote_retrieve_body($response));
            throw new TurboApiException($body->message, $body->error);
        }

        return $this;

    }

    public function isValid() {

        try {
            $this->authorize();
        }
        catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return true;

    }

}

