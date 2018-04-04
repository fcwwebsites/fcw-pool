<?php
 
class FCWPesquisas extends FCWDb {
	
	protected $rawData;
	
	function __construct(){
		
	}
	
	public function getData(){

		if(isset($_GET["subclass"])){
			if($_GET["subclass"]=="questoes"){
			
				//Get children off pesquisas
				if(is_numeric($_GET["id"])){
					$query = "Select questoes.* from questoes where pesquisa_id=" . $_GET["id"] . " order by questoes.id, questoes.numero_ordem";
				}
				
				$this->rawData = $this->select($query);
				
				foreach($this->rawData as $key => $value){
					if($value["tipo"] != "1"){
						$query = "Select * from opcoes where questao_id = " . $value["id"];
						$this->rawData[$key]["opcoes"] = $this->select($query);
					}			
				}
				
				return $this->processData($this->rawData);

			}elseif($_GET["subclass"]=="respostas"){
				return ["success"=>true];
			}

			
		}else{
			//Get base query to get pesquisas
			$query = "Select pesquisas.*,'**child-content**autor', usuarios.nome,'**child-content-end**',count(representantes_pesquisas_respondidas.id) as qtd_respostas from (pesquisas left join usuarios on (pesquisas.autor_id = usuarios.id)) LEFT JOIN representantes_pesquisas_respondidas on(representantes_pesquisas_respondidas.pesquisa_respondida_id =pesquisas.id ) where pesquisas.status=1 ";
			
			if(isset($_GET["autor_id"]) && is_numeric($_GET["autor_id"])){
				// filter by autor
				$query .= "and pesquisas.autor_id=" . $_GET["autor_id"] . " ";
			}elseif(isset($_GET["autor_id"]) && strpos($_GET["autor_id"],"|")){
				// filter by autor specifying the operator's type
				foreach(explode(",",$_GET["autor_id"]) as $value){
					$arr = explode("|",$value);
					if(is_numeric($arr[1])){
						$query .= "and pesquisas.autor_id" . self::$operators[$arr[0]] . "'" . $arr[1] . "' ";
					}
				}			
			}
			
			if(isset($_GET["status"]) && isset($_GET["user_id"]) && is_numeric($_GET["user_id"])){
				switch($_GET["status"]){
					case "pending":
						$query .= "and pesquisas.autor_id!='" . $_GET["user_id"] . "' and data_lancamento <= '" . date("Y-m-d") . "' and data_fechamento >= '" . date("Y-m-d") . "' ";
						break;

					case "answered":
						$query .= "and pesquisas.autor_id!='" . $_GET["user_id"] . "' and representantes_pesquisas_respondidas.representante_id ='" . $_GET["user_id"] . "' ";
						break;
					
					case "lost":
						$query .= "and pesquisas.autor_id!='" . $_GET["user_id"] . "' and representantes_pesquisas_respondidas.representante_id !='" . $_GET["user_id"] . "' and data_fechamento <= '" . date("Y-m-d") . "' ";
						break;
				
				}
			
			}
			
			
			if(isset($_GET["id"]) && is_numeric($_GET["id"])){
				// filter by pesquisa id
				$query .= "and pesquisas.id=" . $_GET["id"] . " GROUP by pesquisas.id order by pesquisas.id desc ";
			}else{
				// add the last infos for the tag
				$query .= "GROUP by pesquisas.id order by pesquisas.id desc ";
			}
			
			$this->rawData = $this->select($query);	
			return $this->processData($this->rawData);

		}		

		print($query);


	}
	
	public function addData($obj){
		if(isset($_GET["subclass"])){
			if(is_numeric($_GET["id"])){
				if($_GET["subclass"]=="questoes"){		
					return $this->addQuestao($obj);
				}elseif($_GET["subclass"]=="resposta"){
					return $this->savePesquisa($obj);
				}
			}else{
				return false;
			}

		}else{
			return $this->addPesquisa($obj);
		}	
	
	}
	
	private function savePesquisa($obj){
		$date = date('Y-m-d H:i:s');
		
		//Pega as respostas, e salva no banco as de texto
		$ids = [];
		foreach($obj->respostas as $resposta){
			if(isset($resposta->text)){
				$query = "Insert into textos (corpo,created) values (";
				$query .= "'" . urlencode($resposta->text) . "',";
				$query .= "'" . $date . "')";
				$result = $this->query($query);
				$result = $this->select("SELECT LAST_INSERT_ID() as inserted_id");
				$ids[] = [$resposta->id,$result[0]["inserted_id"]];
			}elseif(is_array($resposta->id_resposta)){
				foreach($resposta->id_resposta as $value){
					$ids[] = [$resposta->id,$value];
				}
			
			}else{
				$ids[] = [$resposta->id,$resposta->id_resposta];
			}
		}
		
		//Salva as respostas no banco
		foreach($ids as $value){
			$query = "Insert into respostas(questao_id,autor_id,texto_id,created) values (";
			$query .= $value[0] . ",";
			$query .= "'" . urlencode($obj->user_id) . "',";
			$query .= $value[1] . ",";
			$query .= "'" . $date . "')";
			$result = $this->query($query);
		}
		
		//Salva que o usu‡rio respondeu a essa pesquisa
		
		$query = "Insert into representantes_pesquisas_respondidas(pesquisa_respondida_id,representante_id,created) values (";
		$query .= "'" . urlencode($obj->id) . "',";
		$query .= "'" . urlencode($obj->user_id) . "',";
		$query .= "'" . $date . "')";
		$this->rawData = $this->query($query);
		
		return $this->rawData;
		
	}
	
	private function addQuestao($obj){
		$date = date('Y-m-d H:i:s');
		$query = "Insert into questoes(enunciado,pesquisa_id,tipo,numero_ordem,created,modified,status) values (";
		$query .= "'" . urlencode($obj->enunciado) . "',";
		$query .= "'" . $_GET["id"] . "',";
		$query .= "'" . urlencode($obj->tipo) . "',";
		$query .= "'" . urlencode($obj->numero_ordem) . "',";
		$query .= "'" . $date . "',";
		$query .= "'" . $date . "',";
		$query .= "1)";
		
		$result = $this->query($query);
		$result = $this->select("SELECT LAST_INSERT_ID() as inserted_id");
		
		$questao_id = $result[0];
		
		if(urlencode($obj->tipo) != 1 && is_numeric($questao_id["inserted_id"])){
	
			foreach($obj->opcoes as $opcao){
				$query = "Insert into opcoes (descricao,questao_id,created,modified,status) values (";
				$query .= "'" . urlencode($opcao->label) . "',";
				$query .= "'" . $questao_id["inserted_id"] . "',";
				$query .= "'" . $date . "',";
				$query .= "'" . $date . "',";
				$query .= "1);";
				$this->query($query);
			}
			$this->rawData = is_numeric($questao_id["inserted_id"]);
		}elseif(is_numeric($questao_id["inserted_id"])){
			$this->rawData = is_numeric($questao_id["inserted_id"]);
		}else{
			$this->rawData = false;
		}
					
		return $this->rawData;
		
		
	}
	
	
	private function addPesquisa($obj){
		$date = date('Y-m-d H:i:s');

		$dt_temp = explode("/",$obj->data_lancamento);
		$date_start = implode("-",array_reverse($dt_temp)) . " 00:00:00";
		
		$dt_temp = explode("/",$obj->data_encerramento);
		$date_end = implode("-",array_reverse($dt_temp)) . " 00:00:00";

		$query = "Insert into pesquisas(titulo, codinome,filtro,autor_id,mostra_empresa,created,modified, data_lancamento,data_fechamento) values (";
		$query .= "'" . urlencode($obj->titulo) . "',";
		$query .= "'" . urlencode($obj->descricao) . "',";
		$query .= "'',";
		$query .= "'" . urlencode($obj->autor_id) . "',";
		$query .= "'" . urlencode($obj->mostra_empresa) . "',";
		$query .= "'" . $date . "',";
		$query .= "'" . $date . "',";
		$query .= "'" . $date_start . "',";
		$query .= "'" . $date_end . "')";
		
		$this->rawData = $this->query($query);
				
		return $this->rawData;

	}
	
	public function deletePesquisa(){
		if(is_numeric($_GET["id"])){
			$query = "Delete from pesquisas where id=" . $_GET["id"];
			$this->rawData = $this->query($query);
			
			return $this->rawData;
		}
	}
	
	
	private function processData($content){
		for($i=0;$i < sizeof($content); $i++ ){
			if(isset($content[$i]["mostra_empresa"]) && $content[$i]["mostra_empresa"] == 0){
				$content[$i]["autor"]["nome"] = null;
				$content[$i]["autor_id"] = null;
			}
		}
		
		return $content;
		
	}
	
}