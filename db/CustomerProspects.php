<?php

class CustomerProspects extends IDBTable {

	public function __construct($db) {
		parent::__construct($db);
		$this->tablename        = 'CUST_PROSPECT';
		$this->dbcolumns        = array(
										  'ID' => 'ID' 
										, 'CREATED_AT' => 'CREATED_AT' 
										, 'UPDATED_AT' => 'UPDATED_AT' 
										, 'EMP_CD' => 'EMP_CD' 
										, 'FNAME' => 'FNAME' 
										, 'LNAME' => 'LNAME' 
										, 'EMAIL' => 'EMAIL' 
										, 'PHONE' => 'PHONE' 
										, 'EMP_CD2' => 'EMP_CD2' 
										, 'ORDER_PLACED' => 'ORDER_PLACED' 
										);

		$this->dbcolumns_date  = array(
										  'CREATED_AT'
										, 'UPDATED_AT'
										);

 		$this->setAutoIDColumn("ID");
    }
}

?>


