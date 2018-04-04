<?php


class FCWRestfulApi extends SimpleRest{

	private $statusCode;
	private $rawData;
	private $requestContentType;
	private $class;

	function __construct(){
	
		$this->requestContentType = 'application/json';
	
		if(isset($_GET["class"]))
			$this->class = $_GET["class"];
		
		switch($this->class){
		
			case "usuarios":
				// to handle REST Url /mobile/list/
				$users = new FCWUsers();
				
				$this->statusCode = 200;
				$this->rawData = array($this->class => $users->getData());

				break;
		
			case "pesquisas":
				// to handle REST Url /mobile/list/
				
				if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
					$pesquisas = new FCWPesquisas();
					
					$delete = $pesquisas->deletePesquisa();
					if($delete==true){
						$this->statusCode = 201;
						$this->rawData = array("success"=>1);
					}else{
						$this->statusCode = 404;
						$this->rawData = array('erro' => $save,'data'=>json_encode($postdata));							
					}

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
					
				
				}else if($_SERVER['REQUEST_METHOD'] === 'GET'){
					$pesquisas = new FCWPesquisas();
					
					$this->statusCode = 200;
					$this->rawData = array($this->class => $pesquisas->getData());
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
				$this->rawData = array($this->class => $questoes->getData());

				break;
		
			default :
				//404 - not found;
				$this->statusCode = 404;
				$this->rawData = array('erro' => 0);		

				break;
		}
		
		$this->showResult();

	}
	
	
	
	private function showResult(){
	
		$this ->setHttpHeaders($this->requestContentType, $this->statusCode);
		$result = $this->rawData;
		$response = json_encode($result);
		echo $response;

	}

}
