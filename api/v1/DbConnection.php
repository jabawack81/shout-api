<?php
/*
 * Class used to abstract the connection with the Database
 * the class implements a basic CRUD system
 * the two $new_values parameters of the functions create and update are
 * designed to be in the same format: an associated array whith the table
 * column name as key and the column valueas value this introduce some
 * complexity in the create function but this will help to reduce the
 * complexity in the class using it.
 * The configuration of the databse is stored in a separate file to that is
 * ignored from git to avoid any security problem of aving credential in the
 * repository and this allow to have different configuration for each
 * environment: dev / staging / production
 */
class DbConnection
{

  private $link = null;
  private $db_name = null;
  private $db_server = null;
  private $db_username = null;
  private $db_password = null;

  /**
   * Method:      __construct
   * Parameters:  None
   * Description: Constructor method for the class
   */
  public function __construct() {
    // read all the configuration of the database from the SecretConfig file
    require "SecretConfig.php";

    // store all the database credential from the secret file to a class variable
    $this->db_name = $db_name;
    $this->db_server = $db_server;
    $this->db_username = $db_username;
    $this->db_password = $db_password;
  }

  /**
   * Method:      Connect
   * Parameters:  None
   * Description: private method used to open the connection with the Database
   */
  private function connect(){
    // file with the database credential
    $this->link = new PDO("mysql:dbname=$this->db_name;host=$this->db_server", $this->db_username, $this->db_password);
  }

  /**
   * Method:      Connect
   * Parameters:  None
   * Description: private method used to close the connection with the Database
   */
  private function disconnect(){
    $this->link = null;
  }

  /**
   * Method:      Create
   * Parameters:  $table: (string) the table where the new record will be inserted
   *              $new_values: (array) associated array containing the values
                               for the new record:
                               array(
                                column1 => value1,
                                column2 => value2,
                                ......
                                columnn => valuen
                              )
   * Description: public method used to create a new recod in the $table
   */
  public function create($table, $new_values){

    $this->connect();

    // extract the column names from the $new_values array
    $columns = join(array_keys($new_values), ", ");
    // extract the column values from the $new_values array
    $values = "'" . join(array_values($new_values), "', '") . "'";

    $statement = $this->link->prepare("INSERT INTO ". $table. "(" . $columns . ") VALUES(" . $values . ");");

    $result = $statement->execute();

    // check if the SQL it's been executed
    if(true == $result){
      // return the id of the new record
      $ret = array("status" => "ok", "result" => $this->link->lastInsertId());
    } else {
      // return the error
      $ret = array("status" => "error", "message" => $statement->errorInfo());
    }

    $this->disconnect();

    return $ret;
  }

  /**
   * Method:      Read
   * Parameters:  $table: (string) the table from where the record will be read
   *              $id: (int) Id of the record to read
   * Description: public method used to create a new recod in the $table
   */
  public function read($table, $id){

    $this->connect();

    $statement = $this->link->prepare("SELECT * FROM `". $table ."` WHERE `id`='" . $id . "';");

    $result = $statement->execute();

    // check the result of the query
    if(true == $result && $statement->rowCount() > 0){
      // return the fetched record
      $ret = array("status" => "ok", "result" => $statement->fetch(PDO::FETCH_ASSOC));
    } elseif (true == $result && $statement->rowCount() == 0) {
      // return a warning if the record is not found
      $ret = array("status" => "error", "message" => "record not found");
    } else {
      // return the error
      $ret = array("status" => "error", "message" => $statement->errorInfo());
    }

    $this->disconnect();

    return $ret;
  }

  /**
   * Method:      Update
   * Parameters:  $table: (string) the table with the record to update
   *              $new_values: (array) associated array containing the values
                               for the new record:
                               array(
                                column1 => value1,
                                column2 => value2,
                                ......
                                columnn => valuen
                              )
   *              $id: (int) Id of the record to update
   * Description: public method used to update a recod of the $table
   */
  public function update($table, $id, $new_values){

    // empty array to hold the string with column name and value to be updated
    $update = array();

    // fill the array to hold all the strings with column name and value to be updated
    foreach($new_values as $key => $value){
      $update[] = $key . "='" . $value . "'";
    }

    // first I check if the new value are different from the current
    //  get the current record
    $current_status = $this->read($table, $id)["result"];
    // ecxtract only the targetted value for the comparsion
    $target_value = array_intersect_key ( $current_status , $new_values );
    // check how many fields are to be updated
    $differences = array_diff($target_value, $new_values);
    // return a error message if no fields are changed
    if(0 == count($differences)){
      return array("status" => "error", "message" => "no data to update");
    }

    $this->connect();
    $sql = "UPDATE `". $table ."` SET ". join($update, ", ") ." WHERE `id`='" . $id . "';";
    $statement = $this->link->prepare($sql);

    $result = $statement->execute();

    $this->disconnect();

    // check the result of the query
    if(true == $result && $statement->rowCount() > 0){
      // return the fetched record
      $ret = $this->read($table, $id);
    } elseif (true == $result && $statement->rowCount() == 0) {
      // return a warning if the record is not found
      $ret = array("status" => "error", "message" => "record not found");
    } else {
      // return the error
      $ret = array("status" => "error", "message" => $statement->errorInfo());
    }

    return $ret;
  }

  /**
   * Method:      Delete
   * Parameters:  $table: (string) the table from where the record will be deleted
   *              $id: (int) Id of the record to delete
   * Description: public method used to delete a recod of the $table
   */
  public function delete($table, $id){

    $this->connect();

    $statement = $this->link->prepare("DELETE FROM `". $table ."` WHERE `id`='" . $id . "';");

    $result = $statement->execute();

    $this->disconnect();

    // check the result of the query
    if(true == $result && $statement->rowCount() > 0){
      // return the fetched record
      $ret = array("status" => "ok", "message" => "record deleted");
    } elseif (true == $result && $statement->rowCount() == 0) {
      // return a warning if the record is not found
      $ret = array("status" => "error", "message" => "record not found");
    } else {
      // return the error
      $ret = array("status" => "error", "message" => $statement->errorInfo());
    }

    return $ret;
  }

  /**
   * Method:      Search
   * Parameters:  $table: (string) the table from where the record will be read
   *              $search_criteria: (array) associated array containing the values
                                    for the where statment:
                                     array(
                                      column1 => value1,
                                      column2 => value2,
                                      ......
                                      columnn => valuen
                                    )
                  $search_value: (array) the column to be returnde
   * Description: public method used to create a new recod in the $table
   */
  public function search($table, $search_criteria = null, $search_value = array("*")){

    // check if $search_criteria is an array
    // if is an array it's transformed in a array of strings to be joined in the SQL
    if(is_array($search_criteria)){
      // empty array to hold the string with column name and value to be searched
      $criteria = array();

      // fill the array to hold all the strings with column name and value to be searched
      foreach($search_criteria as $key => $value){
        $criteria[] = $key . " = '" . $value . "'";
      }
    // if $search_criteria is not an array true is passed to the SQL so all the row
    // will be returned
    } else {
      $criteria = array("TRUE");
    }

    $value = join($search_value, ", ");

    $this->connect();

    $statement = $this->link->prepare("SELECT " . $value . " FROM " . $table ." WHERE ". join($criteria, " AND ") . ";");

    $result = $statement->execute();

    $this->disconnect();

    // check the result of the query
    if(true == $result && $statement->rowCount() > 0){
      // return the fetched record
      $ret = array("status" => "ok", "result" => $statement->fetchAll(PDO::FETCH_ASSOC));
    } elseif (true == $result && $statement->rowCount() == 0) {
      // return a warning if the record is not found
      $ret = array("status" => "error", "message" => "record not found");
    } else {
      // return the error
      $ret = array("status" => "error", "message" => $statement->errorInfo());
    }

    return $ret;
  }
}
