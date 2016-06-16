<?php
class ShoutAPI
{
  protected $method = "";
  protected $endpoint = "";
  protected $verb = "";
  protected $args = Array();
  protected $headers = Array();

  /**
   * Constructor: __construct
   * Allow for CORS, assemble and pre-process the data
   */
  public function __construct($request) {
    require_once "User.php";
    require_once "Shout.php";

    header("Access-Control-Allow-Orgin: *");
    header("Access-Control-Allow-Methods: *");
    header("Content-Type: application/json");

    $this->args = explode("/", rtrim($request, "/"));
    $this->headers = getallheaders();
    $this->endpoint = array_shift($this->args);
    if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
      $this->verb = array_shift($this->args);
    }

    $this->method = $_SERVER["REQUEST_METHOD"];
    if ("POST" == $this->method && array_key_exists("HTTP_X_HTTP_METHOD", $_SERVER)) {
      if ("DELETE" == $_SERVER["HTTP_X_HTTP_METHOD"]) {
        $this->method = "DELETE";
      } else if ("PUT" == $_SERVER["HTTP_X_HTTP_METHOD"]) {
        $this->method = "PUT";
      } else {
        throw new Exception("Unexpected Header");
      }
    }

    switch($this->method) {
      case "DELETE":
      case "POST":
        $this->request = $this->_cleanInputs($_POST);
        break;
      case "GET":
        $this->request = $this->_cleanInputs($_GET);
        break;
      case "PUT":
        $this->request = $this->_cleanInputs($_GET);
        $this->file = file_get_contents("php://input");
        break;
      default:
        $this->_response("Invalid Method", 405);
        break;
      }
  }

  public function processAPI() {
    if (method_exists($this, $this->endpoint)) {
      return $this->_response($this->{$this->endpoint}($this->args));
    }
    return $this->_response("No Endpoint: $this->endpoint", 404);
  }

  private function _response($data, $status = 200) {
    header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
    return json_encode($data);
  }

  private function _cleanInputs($data) {
    $clean_input = Array();
    if (is_array($data)) {
      foreach ($data as $k => $v) {
        $clean_input[$k] = $this->_cleanInputs($v);
        }
    } else {
      $clean_input = trim(strip_tags($data));
    }
    return $clean_input;
  }

  private function _requestStatus($code) {
    $status = array(
      200 => "OK",
      404 => "Not Found",
      405 => "Method Not Allowed",
      500 => "Internal Server Error",
    );
    return ($status[$code])?$status[$code]:$status[500];
  }

  public function login(){
    if ("POST" == $this->method) {
      try{
        $user = new User("login", $this->request);
      } catch  (Exception $e) {
        return array( "status" => "error", "message" => $e->getMessage() );
      }
      return $user->to_array();
    } else {
      return "Only accepts POST requests";
    }
  }

  public function logout(){
    if ("POST" == $this->method) {
      try{
        $user = new User();
      } catch  (Exception $e) {
        return array( "status" => "error", "message" => $e->getMessage() );
      }
      return $user->logout($this->headers);
    } else {
      return "Only accepts POST requests";
    }
  }

  public function register() {
    if ("POST" == $this->method) {
      try{
        $user = new User("register", $this->request);
      } catch  (Exception $e) {
        return array( "status" => "error", "message" => $e->getMessage() );
      }
      return $user->to_array();
    } else {
      return "Only accepts POST requests";
    }
  }

  public function shout(){
    if ("POST" == $this->method) {
      try {
        $user = new User("check", $this->headers);
        $shout = new Shout("create", array( "shout" => $this->request, "user" => $user));
        return $shout->to_array();
      } catch  (Exception $e) {
        return array( "status" => "error", "message" => $e->getMessage() );
      }
    } elseif ("GET" == $this->method && count($this->args) == 0 ) {
      $shout = new Shout();
      $shouts = $shout->get_all();
      return $shouts;
    } elseif ("GET" == $this->method && is_numeric($this->args[0]) ) {
      try{
        $shout = new Shout("fetch", $this->args[0] );
      } catch  (Exception $e) {
        return array( "status" => "error", "message" => $e->getMessage() );
      }

      if(count($this->args) > 1 && "upvote" == $this->args[1]){
        $shout->upvote();
      }

      if(count($this->args) > 1 && "downvote" == $this->args[1]){
        $shout->downvote();
      }

      return $shout->to_array();
    } elseif ("DELETE" == $this->method && is_numeric($this->args[0])) {
      try{
        $user = new User("check", $this->headers);
        $shout = new Shout("fetch", $this->args[0] );
        $delete = $shout->delete($user->get_id());
        if( "ok" == $delete["status"]){
          return array("status" => "ok", "message" => "Shout deleted");
        } else {
          return $delete;
        }
      } catch  (Exception $e) {
        return array( "status" => "error", "message" => $e->getMessage() );
      }
    }
  }
}
