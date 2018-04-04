<?php
 
class FCWUsers extends FCWDb {
	
	protected $rawData;
	protected $statusCode;
	protected $singularName = "user";
	protected $pluralName = "users";
	protected $tableName = DB_PREFIX . "users";
	protected $fields = array("id","login","email","registered","status");
	protected $fieldsRequired = array("login","email","password","status");
	
	function __construct($method,$request){
		switch($method){
			
			//Get Users
			case "GET":
				$unique = false;
				if(isset($request[1])){
					$where = " Where id = " . $request[1] . " ";
					$unique = true;
				}else{
					$where = "";
				}
				
				$pagination = array();
				
				if(isset($_REQUEST["page"]) && is_numeric($_REQUEST["page"]) && $_REQUEST["page"]>0 ){
					$pagination["page"] = $_REQUEST["page"];
				}

				if(isset($_REQUEST["perpage"]) && is_numeric($_REQUEST["perpage"]) && $_REQUEST["perpage"]>0 ){
					$pagination["perpage"] = $_REQUEST["perpage"];
				}
				
				$return = $this->select($this->tableName,$this->fields,$where, $pagination);
				if(count($return)>0){
					if(is_array($return)){
						$hateoas = $this->getHateoas($this->tableName,$this->fields,$where,$pagination);
						$this->rawData = array("users" => $return);
						if(!$unique){
							$this->rawData = array_merge($this->rawData,$hateoas);
						}
						$this->statusCode = 200;					
					}else{
						$this->rawData = array( "errors"=> 
							array(
								"code" => "00",
								"message" => "Error fetching db data."
							)
						);
						$this->statusCode = 500;					
					}
				}else{
					$this->rawData = array();
					$this->statusCode = 404;								
				}

				break;
				
			case "POST":
				
				
				break;
		}		
		
	}
	
	public function getData(){
		/*
		if(isset($_GET["q"]) && strlen($_GET["q"])>3){
			$query = "Select * from usuarios where (nome like '%" . $_GET["q"] . "%' or email like '%" . $_GET["q"] . "%') and status=1 order by id desc ";
		}elseif(isset($_GET["id"]) && is_numeric($_GET["id"])){
			$query = "Select * from usuarios where id=" . $_GET["id"] . " and status=1 order by id desc ";
		}else{
			$query = "Select * from usuarios where status=1 order by id desc ";
		}
		
		$this->rawData = $this->select($query);

		return $this->rawData;
		*/
		return $_GET;
	}
	
	public function getStatusCode(){
	
		return $this->statusCode;
	
	}
	
	public function getRawData(){
	
		return $this->rawData;
	
	}
	
}