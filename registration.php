<?php

$sup = new Registration();

class Registration{

	/**
	*  MySQL connection.
	*/
	private $connection = null;

	/**
	* Array to hold errors;
	*/
	private $errors = array();

  private $messages = array();

  private $registration_successful = false;


	public function __construct(){
		$this->databaseConnection();
	}

	/**
	* Checks if the database is connected. If not it will connect.
	*/
	private function databaseConnection(){
		//connection is already open
  	if($connection != null){
  		return;
  	} else {
  		$connection = mysqli_connect("127.0.0.1", "user", "password", "database") or die("Error " . mysqli_error($connection));
  	}
  }

  	/**
	* Disconnects MySQL connection and sets it to null.
	*/
  private function databaseDisconnect(){
  	mysqli_close($connection);
  	$connection = null;
  }

 	private function registerNewUser($user_name, $user_email, $user_password, $user_password_repeat){
  	// Clean up the user inputs
  	$user_name = $this->sanatize($user_email);
  	$user_email = $this->sanatize($user_email);
 		if(empty($user_name)){
      $this->errors[] = MESSAGE_USERNAME_EMPTY;
 		} else if(empty($user_password) || empty($user_password_repeat)){
      $this->errors[] = MESSAGE_PASSWORD_EMPTY;
    } else if ($user_password !== $user_password_repeat){
      $this->errors[] = MESSAGE_PASSWORD_BAD_CONFIRM;
    } else if(strlen($user_name) < 6 ){
      $this->errors[] = MESSAGE_PASSWORD_TOO_SHORT;
    } else if(!preg_match('/^[a-z\d]{2,64}$/i', $user_name)){
      $this->errors[] = MESSAGE_USERNAME_INVALID;
    } else if(empty($user_email)){
      $this->errors[] = MESSAGE_EMAIL_EMPTY;
    } else if(strlen($user_email) > 64){
      $this->errors[] = MESSAGE_EMAIL_TOO_LONG;
    } else if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)){
      $this->errors[] = MESSAGE_EMAIL_INVALID;
    } else if($this->$connection){

      //check if username or email exist
      $query_username_email = "SELECT user_name, user_email FROM users WHERE user_name=$user_name or user_email=$user_email;";
      $result = mysqli_query($connection, $query_username_email);
      $row = mysqli_fetch_array($result);

      //If username or email already exist, set error.
      if(count($row) > 0){
        $this->errors[] = "Existing username or email";
      } else {

      //data submitted should be valid at this point.

      //hash users password
        $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
        //generate random hash for email verification
        $user_activation_hash = sha1(uniqid(mt_rand(), true));
        //write users data into table
        $query_insert_user = "INSERT INTO users (user_name, user_password_hash, user_email, user_activation_hash, user_registration_ip) VALUES ($user_name, $user_password_hash, $user_email, $user_activation_hash, $_SERVER["REMOTE_ADDR"]);";
       
        $insert = mysqli_query($connection, $query_insert_user);

        $user_id = mysqli_insert_id();

        if($insert){
          //send email verification
          if($this->sendVerficationEmail($user_id, $user_email, $user_activation_hash)){ //still need to make email verification
            $this->messages[] = MESSAGE_VERIFICATION_MAIL_SENT;
            $this->registration_successful = true;
          } else {
            //delete users info since we could not send verification email
            $query_delete_user = "DELETE FROM users WHERE ID=$user_id;";
            $delete = mysqli_query($connection, $query_insert_user);
            $this->errors[] = MESSAGE_VERIFICATION_MAIL_ERROR;
          }
        } else {
          $this->errors[] = MESSAGE_REGISTRATION_FAILED;
        }
      }
    }
 	}


    /*
    * sends an email 
    * if successful, it will return true, otherwise false
    */
    private function sendVerficationEmail($user_id, $user_email, $user_activation_hash){

      $url = "http://127.0.0.1/php-login-advanced/register.php".urlencode($user_id).'&verification_code='.urlencode($user_activation_hash);
      $email_message = "Please Activate account at $url";

      $sent_email = mail($user_email, "Blog Activation", $url);
      if($sent_email){
        return true;
      } else {
        $this->errors[] = MESSAGE_VERIFICATION_MAIL_NOT_SENT;
        return false;
      }
    }

  	private function sanatize($string){
  		$string = trim($string);
  		$string = mysqli_real_escape_string($string)
  		$string = htmlentities($string);
  		return $string;
  	}

    /*
    * checks the id and hash combination to activate the user, sets activation status to true(1) in the db.
    */
    public function verifyNewUser($user_id, $user_activation_hash){
      $query_update_user = "UPDATE users SET user_active = 1, user_activation_hash = NULL WHERE user_id = $user_id AND user_activation_hash = $user_activation_hash;";
      $result = mysqli_query($connection,$query_update_user);
      $query_rows = mysqli_fetch_array($result);

      if(count($query_rows) > 0){
        //successful
        $this->verifcation_successful = true;
        $this->messages[] = MESSAGE_REGISTRATION_ACTIVATION_SUCCESSFUL;
      } else {
        //failed
        $this->errors[] = MESSAGE_REGISTRATION_ACTIVATION_NOT_SUCCESSFUL;
      }
    }
}
?>