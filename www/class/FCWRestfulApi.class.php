<?php


class FCWRestfulApi extends SimpleRest{

	private $statusCode;
	private $rawData;
	private $requestContentType = 'application/json';
	private $method;
	private $resource;

	function __construct(){
	
		$this->method = $_SERVER['REQUEST_METHOD'];

		if(isset($_GET["resource"])){

			$this->resource = explode("/",$_GET["resource"]);

			//Validate if get a numeric ID
			if( ($this->method!="GET" || count($this->resource)>1) && !(isset($this->resource[1]) && is_numeric($this->resource[1]))){
			
				$this->badRequest();					

			}else{
			
				//Resources Availables
				switch($this->resource[0]){
					
					//User Resource
					case "users" :

						$user = new FCWUsers($this->method,$this->resource);
						
						$this->statusCode = $user->getStatusCode();
						$this->rawData = $user->getRawData();
						
						break;
					
					//Resource Unavailable
					default :
					
						$this->badRequest();
						break;
				
				}
			}
			
		}else{

			$this->badRequest();

		}
		
		
		
		
		/*
		if(isset($_GET["resource"]))
			$this->resource = $_GET["resource"];
		
		switch($this->resource){
		
			case "users":
			
				$users = new FCWUsers();
				
				if($_SERVER['REQUEST_METHOD'] === 'GET'){
					$this->statusCode = 200;
					$this->rawData = $users->getData();
				
				}elseif($_SERVER['REQUEST_METHOD'] === 'POST'){
					$pesquisas = new FCWPesquisas();

					$postdata = json_decode(file_get_contents("php://input"));
					$save = $pesquisas->addData($postdata);
					if($save==true){
						$this->statusCode = 201;
						$this->rawData = array("success"=>1,"retorno"=>$save);
					}else{
						$this->statusCode = 404;
						$this->rawData = array('erro' => $save,'data'=>json_encode($postdata));							
					}
					
				
				}elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
					$pesquisas = new FCWPesquisas();
					
					$delete = $pesquisas->deletePesquisa();
					if($delete==true){
						$this->statusCode = 201;
						$this->rawData = array("success"=>1);
					}else{
						$this->statusCode = 404;
						$this->rawData = array('erro' => $save,'data'=>json_encode($postdata));							
					}
				
				}else{
					//404 - not found;
					$this->statusCode = 404;
					$this->rawData = array('erro' => 0);		
				}
				

				break;
		
			case "questoes":
				// to handle REST Url /mobile/list/
				$questoes = new FCWQuestoes();
				
				$this->statusCode = 200;
				$this->rawData = array($this->resource => $questoes->getData());

				break;
		
			default :
				//404 - not found;
				$this->statusCode = 404;
				$this->rawData = array('erro' => 0);		

				break;
		}
		*/
		
		$this->showResult();

	}
	
	
	
	private function showResult(){
	
		$this ->setHttpHeaders($this->requestContentType, $this->statusCode);
		$result = $this->rawData;
		$response = json_encode($result,JSON_PRETTY_PRINT);
		echo $response;

	}
	
	private function badRequest(){
		$this->statusCode = 400;
		$this->rawData = array('erro' => 0);
	
	}

}
