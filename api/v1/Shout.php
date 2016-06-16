<?php
  class Shout {
    protected $db = null;
    protected $id = null;
    protected $body = null;
    protected $user_id = null;
    protected $vote = null;
    protected $shouted_on = null;

    /**
     * Method:      __construct
     * Parameters:  Action: (string) the action to perform with the $data passed
                      allowed action are: Fetch to construct a shout with the data
                      from the db, create: to create a new shout.
                    Data: (array) data to be used in the action
     * Description: Constructor method for the class
     */
    public function __construct($action = null, $data = null) {

      require_once "DbConnection.php";
      $this->db = new DbConnection();

      // retrive a shout from the DB
      if("fetch" == $action){

        $db_shout = $this->db->read("shout", $data);

        if ("ok" == $db_shout["status"]){
          $this->init($db_shout["result"]);
          return;
        } else {
          throw new Exception("Shout not found");
        }
      }

      // create a new shout
      if("create" == $action){

        // check that the needed data are passed
        if(!array_key_exists ( "shout" , $data ) || !array_key_exists ( "body" , $data["shout"] ) || $data["shout"]["body"] == null ){
          throw new Exception("body missing");
        }

        $shout_data = array(
          "body" => $data["shout"]["body"],
          "user_id" => $data["user"]->get_id(),
          "vote" => 0,
          "shouted_on" => date("Y-m-d H:i:s")
          );

        $new_shout = $this->db->create("shout", $shout_data);

        // check if the shout is been created
        if ("ok" == $new_shout["status"]){
          // fetch the created shout
          $created_shout = $this->db->read("shout", $new_shout["result"]);
          // iniialize the current instance with the data of shout just created
          $this->init($created_shout["result"]);
        } else {
          throw new Exception($new_shout["message"]);
        }
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
        "body" => $this->body,
        "user_id" => $this->user_id,
        "vote" => $this->vote,
        "shouted_on" => $this->shouted_on
        );
    }

    /**
     * Method:      Upvote
     * Parameters:  None
     * Description: Increase the property vote of the shout
     */
    public function upvote() {
      $this->vote += 1;
      $updated = $this->db->update("shout", $this->id, array("vote" => $this->vote));
    }

    /**
     * Method:      Downvote
     * Parameters:  None
     * Description: Decrease the property vote of the shout
     */
    public function downvote() {
      $this->vote -= 1;
      $updated = $this->db->update("shout", $this->id, array("vote" => $this->vote));
    }

    /**
     * Method:      Delete
     * Parameters:  User Id: (integer) the id of the user performing the action
     * Description: Delete a shout
     */
    public function delete($user_id){
      if($user_id == $this->user_id){
        return $this->db->delete("shout", $this->id);
      } else {
        throw new Exception("User not allowed to delete this shout");
      }
    }

    /**
     * Method: Get All
     * Parameters:  None
     * Description: Retrive all The Shout
     */
    public function get_all(){
      return $this->db->search("shout");
    }

    /**
     * Method:      Init
     * Parameters:  Db Shout: (Array) an associated with the data read from the db
     * Description: function for inizialize the shout properties with the data passed
     */
    private function init($db_shout) {
      $this->id = $db_shout["id"];
      $this->body = $db_shout["body"];
      $this->user_id = $db_shout["user_id"];
      $this->vote = $db_shout["vote"];
      $this->shouted_on = $db_shout["shouted_on"];
    }

    /**
     * Method:      Filter Data
     * Parameters:  Data: (Array) an associated with the data to be written on the Db
     * Description: function used to filter the data passed to the DbConnection class
     */
    private function filter_data($data) {

      $filtered_data = array();

      if(array_key_exists("body", $data)){
        $filtered_data["body"] = $data["body"];
      }

      if(array_key_exists("user_id", $data)){
        $filtered_data["user_id"] = $data["user_id"];
      }

      if(array_key_exists("vote", $data)){
        $filtered_data["vote"] = $data["vote"];
      }

      return $filtered_data;
    }

  }
