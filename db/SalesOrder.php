<?php

class SalesOrder extends IDBTable {
	public function __construct($db) {
		parent::__construct($db);
		$this->tablename = 'SO LEFT JOIN CUST ON SO.CUST_CD = CUST.CUST_CD LEFT JOIN EMP ON EMP.EMP_CD = SO_EMP_SLSP_CD1 AND TERMDATE IS NULL';
		$this->dbcolumns = array(
								'DEL_DOC_NUM' => 'DEL_DOC_NUM'
							,	'CUST_CD' => 'CUST_CD'
							,	'STAT_CD' => 'STAT_CD'
							,	'TAX_CHG' => 'TAX_CHG'
							,	'SETUP_CHG' => 'SETUP_CHG'
							,	'SO_WR_DT' => 'SO_WR_DT'
							,	'USR_FLD_2' => 'USR_FLD_2'
							,	'SHIP_TO_ADDR1' => 'SHIP_TO_ADDR1'
							,	'SHIP_TO_ADDR2' => 'SHIP_TO_ADDR2'
							,	'SHIP_TO_CITY' => 'SHIP_TO_CITY'
							,	'SHIP_TO_ST_CD' => 'SHIP_TO_ST_CD'
							,	'SHIP_TO_ZIP_CD' => 'SHIP_TO_ZIP_CD'
							,	'SHIP_TO_H_PHONE' => 'SHIP_TO_H_PHONE'
							,	'SHIP_TO_B_PHONE' => 'SHIP_TO_B_PHONE'
							,	'SO_STORE_CD' => 'SO_STORE_CD'
							,	'SO_EMP_SLSP_CD1' => 'SO_EMP_SLSP_CD1'
							,	'SHIP_TO_B_PHONE' => 'SHIP_TO_B_PHONE'
							,	'SHIP_TO_B_PHONE' => 'SHIP_TO_B_PHONE'
							,	'PU_DEL_DT'=>'PU_DEL_DT'
							,   'EMAIL_ADDR'=>'EMAIL_ADDR'
                            ,   'FNAME'=>'FNAME'
                            ,   'LNAME'=>'LNAME'
                            ,   'EMP_NAME' => 'EMP_NAME'
                            ,   'TERMDATE'=>'TERMDATE'
						);
		$this->dbcolumns_date = array(
								'SO_WR_DT'
							,	'PU_DEL_DT'
						);
        $this->dbcolumns_function = array( 
                                "CUST_CD"  => "CUST.CUST_CD CUST_CD"    
                            ,   "FNAME"  => "CUST.FNAME FNAME"  
                            ,   "LNAME"  => "CUST.LNAME LNAME"  
                            ,   "EMAIL_ADDR"  => "CUST.EMAIL_ADDR EMAIL_ADDR" 
                            ,   'EMP_NAME' => "EMP.FNAME || ' ' || EMP.LNAME EMP_NAME"
        );  
    } 
} 
?>
