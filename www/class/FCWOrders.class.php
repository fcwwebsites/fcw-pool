<?php
 
class FCWOrders extends FCWFirebird{
	protected $rawData;
	protected $statusCode;
	protected $singularName = "order";
	protected $pluralName = "orders";
	protected $tableName = "os";
	protected $fields = array("*");
	protected $fieldsRequired = array();
	protected $orderDefault = " order by DATACRIA desc, DATAALT desc";

	
	public function getData(){
		return($this->select($this->tableName,$this->fields, "", $this->orderDefault));
	}
}
