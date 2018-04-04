<?php
 
class FCWUsers extends FCWDb {
	
	protected $rawData;
	
	function __construct(){
		
	
		if(isset($_GET["q"]) && strlen($_GET["q"])>3){
			$query = "Select * from usuarios where (nome like '%" . $_GET["q"] . "%' or email like '%" . $_GET["q"] . "%') and status=1 order by id desc ";
		}elseif(isset($_GET["id"]) && is_numeric($_GET["id"])){
			$query = "Select * from usuarios where id=" . $_GET["id"] . " and status=1 order by id desc ";
		}else{
			$query = "Select * from usuarios where status=1 order by id desc ";
		}
		
		$this->rawData = $this->select($query);
	
		
	}
	
	public function getData(){
		return $this->rawData;
	}
	
}