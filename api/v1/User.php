<?php
   /**
    * User class
    * the user can login with username and password, this action will create a
    * new token with an expiration date, the token is used for authorize the user
    * until it will expire
    */
  class User {
    protected $db = null;
    protected $id = null;
    protected $token = null;
    protected $username = null;
    protected $token_expire = null;
    protected $encrypted_password = null;
    const TOKEN_LENGTH = 10;

    /**
     * Method:      __construct
     * Parameters:  Action: (string) the action to perform with the $data passed
                      allowed action are: Fetch to construct a user with the data
                      from the db, Register: to register a new user, Login to login
                      the user and check that will check if the token provided is
                      correct valid and not expired
                    Data: (array) data to be used in the action
     * Description: Constructor method for the class
     */
    public function __construct($action = null, $data = null) {

      require_once "DbConnection.php";
      $this->db = new DbConnection();

      // filter the data if provided
      if($data){
        $data = $this->filter_data($data);
      }

      // retrive a user from the db
      if( "fetch" == $action ){

        $db_user = $this->db->read("user", $data);

        if ("ok" == $db_user["status"]){
          $this->init($db_user["result"]);
          return;
        } else {
          throw new Exception("user not found");
        }

      }

      // register a new user
      if ("register" == $action){
        // check that the needed data are passed
        if(!array_key_exists ( "username" , $data ) || $data["username"] == null ){
          throw new Exception("username missing");
        }
        if(!array_key_exists ( "encrypted_password" , $data ) || $data["encrypted_password"] == null ){
          throw new Exception("password missing");
        }

        // check if the username is unique
        $check_user = $this->db->search("user", array("username" => $data["username"]));
        if("ok" == $check_user["status"]){
          throw new Exception("username not unique");
        }

        // add a random token to the data to be writte in the db
        $data["token"] = $this->generate_token();
        // add the token expiration date to the data to be writte in the db
        $data["token_expire"] = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s") . "+2 days"));
        // create the user
        $new_user = $this->db->create("user", $data);

        // check if the user is been created
        if ("ok" == $new_user["status"]){
          // fetch the created user
          $created_user = $this->db->read("user", $new_user["result"]);
          // iniialize the current instance with the data of user just created
          $this->init($created_user["result"]);
        } else {
          throw new Exception($new_user["message"]);
        }
      }

      // login the user to the app
      if ("login" == $action){
        // search the user in the DB
        $db_user = $this->db->search("user", array("username" => $data["username"], "encrypted_password" => $data["encrypted_password"]));
        // if a user is found means that username and password where correct
        if("ok" != $db_user["status"]){
          throw new Exception("Your username or password was incorrect.");
        }

        $this->init($db_user["result"][0]);

        $this->update_token();

        return;

      }

      // check if the username and token are correct and if the token is not expired
      if ("check" == $action){
        // check that all the needed data are passed
        if(!array_key_exists ( "username" , $data ) || $data["username"] == null ){
          throw new Exception("username missing");
        }
        if(!array_key_exists ( "token" , $data ) || $data["token"] == null ){
          throw new Exception("token missing");
        }
        $db_user = $this->db->search("user", array("username" => $data["username"], "token" => $data["token"]));

        if("ok" != $db_user["status"]){
          throw new Exception("Your username or token was incorrect.");
        }

        $user_token_expire = new DateTime($db_user["result"][0]["token_expire"]);
        $current_date = new DateTime();

        if ($user_token_expire < $current_date){
          throw new Exception("Token expired");
        }

        // if all is correct a new user is initializec with the data from the db
        $this->init($db_user["result"][0]);
        return;
      }
    }

    /**
     * Method:      To Array
     * Parameters:  None
     * Description: for security reason all the properties of the class
     *                are private and this function is used to output the
     *                instance as an array
     */
    public function to_array() {
      return array(
        "id" => $this->id,
        "username" => $this->username,
        "token" => $this->token
        );
    }

    /**
     * Method:      Logout
     * Parameters:  $data: (array) associated array containing the username
     *                of the user to logout
     *               array(
     *                "username" => <username>
     *              )
     * Description: function to logout the user, it will nullify the token and
     *              token_expire
     */
    public function logout($data) {

      $data = $this->filter_data($data);

      $read_user = $this->db->search("user", array("username" => $data["username"]));

      if("ok" == $read_user["status"]){

        $this->init($read_user["result"][0]);

        $update = array(
          "token" => null,
          "token_expire" => null
        );

        $this->db->update("user", $this->id, $update);

        return array("status" => "ok", "message" => "user logged out");

      }

      return array("status" => "error", "message" => "user not found");
    }

    /**
     * Method:      Get ID
     * Parameters:  None
     * Description: Return the Id of the current user
     */
    public function get_id(){
      return $this->id;
    }

    /**
     * Method:      Updatre Token
     * Parameters:  None
     * Description: generate a new token and an expiration date, two day fron now
     */
    private function update_token() {
      $update = array(
        "token" => $this->generate_token(),
        "token_expire" => date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s") . "+2 days"))
      );
      $this->init($this->db->update("user", $this->id, $update)["result"]);
    }

    /**
     * Method:      Generate Token
     * Parameters:  None
     * Description: generate a random string that will ber used as token
     */
    private function generate_token() {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $characters_length = strlen($characters);
      $token = '';
      for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
        $token .= $characters[rand(0, $characters_length - 1)];
      }
      return $token;
    }

    /**
     * Method:      Init
     * Parameters:  Db User: (Array) an associated with the data read from the db
     * Description: function for inizialize the user properties with the data passed
     */
    private function init($db_user) {
      $this->id = $db_user["id"];
      $this->username = $db_user["username"];
      $this->token = $db_user["token"];
      $this->token_expire = $db_user["token_expire"];
      $this->encrypted_password = $db_user["encrypted_password"];
    }

    /**
     * Method:      Filter Data
     * Parameters:  Data: (Array) an associated with the data to be written on the Db
     * Description: function used to filter the data passed to the DbConnection class
     */
    private function filter_data($data) {

      $filtered_data = array();

      if(array_key_exists("username", $data)){
        $filtered_data["username"] = $data["username"];
      }

      if(array_key_exists("encrypted_password", $data)){
        $filtered_data["encrypted_password"] = $data["encrypted_password"];
      }

      if(array_key_exists("token", $data)){
        $filtered_data["token"] = $data["token"];
      }

      return $filtered_data;
    }

  }
