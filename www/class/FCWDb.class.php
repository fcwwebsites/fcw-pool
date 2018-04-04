<?php
 
class FCWDb {
	// The database connection
	protected static $connection;
	protected static $perpage = 10;
	protected static $page = 1;
	protected static $operators = array("neq" => "!=", "gt" => ">", "gte" => ">=", "lt" => "<", "lte" => "<=");
	
	/**
	 * Connect to the database
	 * 
	 * @return bool false on failure / mysqli MySQLi object instance on success
	 */
	protected function connect() {    
		// Try and connect to the database
		if(!isset(self::$connection)) {
		    self::$connection = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		}
		
		// If connection was not successful, handle the error
		if(self::$connection === false) {
		    // Handle error - notify administrator, log to a file, show an error screen, etc.
		    return false;
		}
		
		self::$connection->set_charset("utf8");
		return self::$connection;
	}

	/**
	 * Query the database
	 *
	 * @param $query The query string
	 * @return mixed The result of the mysqli::query() function
	 */
	protected function query($query) {
		// Connect to the database
		$connection = $this -> connect();
		
		// Query the database
		$result = $connection -> query($query);
		
		return $result;
	}
	
	/**
	 * Fetch rows from the database (SELECT query)
	 *
	 * @param $query The query string
	 * @return bool False on failure / array Database rows on success
	 */
	protected function select($table, $fields, $where, array $pagination = array()) {
		$page = 1;
		$perpage = 10;

		if(isset($pagination["page"])){
			$page = $pagination["page"];
		}

		if(isset($pagination["perpage"])){
			$perpage = $pagination["perpage"];
		}
		
		$query = "Select " . implode(",",$fields) . " from " . $table . " " . $where . " Limit " . (($page-1)*$perpage) . "," . $perpage;
		
		$rows = array();
		$result = $this -> query($query);
		
		if($result === false) {
			return self::$connection->error;
		}else{

			while ($row = $result -> fetch_assoc()) {
				$arr_temp = array();
				foreach($row as $key=>$value){
					$arr_temp[$key] = urldecode($value);
				}
				$rows[] = $arr_temp;			
			}
			
			return $rows;
		
		}
	}

	/**
	 * Get the pagination info and create the HATEOAS object
	 * 
	 * @return string Database error message
	 */
	protected function getHateoas($table, $fields, $where, array $pagination = array()) {
		$page = 1;
		$perpage = 10;

		if(isset($pagination["page"])){
			$page = $pagination["page"];
		}

		if(isset($pagination["perpage"])){
			$perpage = $pagination["perpage"];
		}
		
		$query =  "Select count(*) as total from " . $table . " " . $where;
		$result = $this -> query($query);
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		$arr["totalPages"] = ceil($arr["total"]/$perpage);
		$path = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REDIRECT_URL"];
		$links = array();
		
		$links["self"] = array("href"=>$path . "?page=" . $page);
		
		$links["first"] = array("href"=>$path);
		if( $page > 1 && $arr["totalPages"] >1){
			$links["prev"] = array("href"=>$path . "?page=" . ($page-1));
		}
		if( $page < $arr["totalPages"] && $arr["totalPages"] > 1 ){
			$links["next"] = array("href"=>$path . "?page=" . ($page+1));
		}
		$links["last"] = array("href"=>$path . "?page=" . $arr["totalPages"]);
		
		$arr["links"] = $links;		
		return $arr;
	}
	

	/**
	 * Fetch the last error from the database
	 * 
	 * @return string Database error message
	 */
	public function error() {
		$connection = $this -> connect();
		return $connection -> error;
	}
	
	/**
	 * Quote and escape value for use in a database query
	 *
	 * @param string $value The value to be quoted and escaped
	 * @return string The quoted and escaped string
	 */
	public function quote($value) {
		$connection = $this -> connect();
		return "'" . $connection -> real_escape_string($value) . "'";
	}

}
 

