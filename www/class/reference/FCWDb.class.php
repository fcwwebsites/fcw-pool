<?php
 
class FCWDb {
	// The database connection
	protected static $connection;
	protected static $perpage = 10;
	protected static $page = 1;
	protected static $operators = array("neq" => "!=", "gt" => ">", "gte" => ">=", "lt" => "<", "lte" => "<=");
	
	protected $child_split = "**child-content**";
	protected $child_split_end = "**child-content-end**";
	
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
	protected function select($query, int $page = 1, int $perpage = 10000) {
		$query .= " Limit " . (($page-1)*$perpage) . "," . $perpage;
		$rows = array();
		$result = $this -> query($query);
		if($result === false) {
			return $connection->error;
		}
		while ($row = $result -> fetch_assoc()) {
			$keys = implode("|",array_keys($row));
			$has_child = strstr($keys,$this->child_split);
			if( strlen($has_child) ){
				//count number off childs
				$childs = substr_count($has_child,$this->child_split);
				
				$child_name = array();
				
				for ($i = 0; $i<$childs; $i++){
					array_push($child_name, substr($has_child,strlen($this->child_split),strpos($has_child,"|")-strlen($this->child_split)));
					
					$has_child = strstr($has_child,$this->child_split,strlen($this->child_split));
					
				}
			
			
				$arr_temp = array();
				
				$on_child =false;
				foreach($row as $key=>$value){
					if(isset($child_name[0]) && $key == $this->child_split . $child_name[0]){
						$on_child = true;
						$arr_temp[$child_name[0]] = array();
					}elseif($key == $this->child_split_end){
						$on_child = false;
						array_shift($child_name);
					}else{
						if($on_child){
							 $arr_temp[$child_name[0]][$key] = urldecode($value);
						}else{
							$arr_temp[$key] = urldecode($value);
						}					
					}
				}
								
				$rows[] = $arr_temp;
								
			}else{
				$arr_temp = array();
				foreach($row as $key=>$value){
					$arr_temp[$key] = urldecode($value);
				}
				$rows[] = $arr_temp;
			}
			
		}
		return $rows;
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
 

