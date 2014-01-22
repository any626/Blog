<?php

/**
* handles the user login/logout/session
*/
class Login{

	private $connection = null;

	private $user_name = null;

	private $user_email = null;

	private $user_is_logged_in = null;

	public function __construct (){

	}

	private function databaseConnection(){
		//connection is already open
  		if($this->connection != null){
  			return;
  		} else {
  			$this->connection = mysqli_connect("127.0.0.1", "user", "password", "database") or die("Error " . mysqli_error($this->connection));
  		}

	}

	private function getUserData($user_name){

		if($this->connection != null){
			$query_user = "SELECT * FROM users WHERE user_name = $user_name;";
			$result = mysqli_query($this->connection, $query_user);
			return mysqli_fetch_array($result);
		} else {
			return false;
		}
	}

	private function loginWithSessionData(){
		$this->user_name = $_SESSION['user_name'];
		$this->user_email= $_SESSION['user_email'];

		$this->user_is_logged_in = true;
	}

	private function loginWithCookieData(){
		if(isset($_COOKIE['rememberme'])){
			//extract data from the cookie
			list($user_id, $token, $hash) = explode(':', $_COOKIE['rememberme']);
			//check cookie hash validity
			if($hash == hash('sha256', $user_id . ':' . $token . COOKIE_SECRET_KEY) && !empty($token)){

				if($this->connection != null){

					$query_token = "SELECT user_id, user_name, user_email FROM users WHERE user_id= $user_id AND user_rememberme_token= $user_rememberme_token AND user_rememberme_token IS NOT NULL;";
					$result = mysqli_query($connection, $query_token);
					$result_row = mysqli_fetch_assoc($result);
				}
			}

		}
	}

}

?>