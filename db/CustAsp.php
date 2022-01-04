<?php

class CustAsp extends IDBTable {

	public function __construct($db) {
		parent::__construct($db);
		$this->tablename        = 'CUST_ASP';
		$this->dbcolumns        = array(
									  'CUST_CD'=>'CUST_CD'
									, 'AS_CD'=>'AS_CD'
									, 'ACCT_CD'=>'ACCT_CD'
									, 'EXPIR_DT'=>'EXPIR_DT'
									, 'APP_CREDIT_TP'=>'APP_CREDIT_TP'
									, 'AR_TP'=>'AR_TP'
									, 'APP_TP'=>'APP_TP'
									, 'CNTR_CD'=>'CNTR_CD'
									, 'APP_REF_NUM'=>'APP_REF_NUM'
									, 'APP_STAT_CD'=>'APP_STAT_CD'
									, 'APP_CREDIT_LINE'=>'APP_CREDIT_LINE'
									, 'MONTH_INT_RATE'=>'MONTH_INT_RATE'
									, 'APR'=>'APR'
									, 'ASSIGN'=>'ASSIGN'
									, 'EXPIR_DAYS'=>'EXPIR_DAYS'
									, 'PASS_EXP_DT'=>'PASS_EXP_DT'
									, 'PRE_APP_CD'=>'PRE_APP_CD'
									, 'PL_CNTR_CD'=>'PL_CNTR_CD'
									, 'PL_CNTR_SEQ_NUM'=>'PL_CNTR_SEQ_NUM'
									, 'APP_CREDIT_AVAIL'=>'APP_CREDIT_AVAIL'
									, 'ACTIVE'=>'ACTIVE'
                                    , 'ACCT_NUM' => 'ACCT_NUM'
									);

		$this->dbcolumns_date	 = array(
										  'EXPIR_DT'
										, 'PASS_EXP_DT'
										);

 		$this->setAutoIDColumn("CUST_CD");

		$this->errorMsg 			= "";

	}
    
     /***************************************************************************
     ****************************************************************************
     * function: getCustAspByCustCdAndAsCd
     * parameters:
     *  @custCd = Customer Code
     *  @asCd = Finance Company
     *  @postclause = Post Clause
     *
     * return:
     *  CUST_ASP record
     ****************************************************************************
     ****************************************************************************/
    public function getCustAspByCustCdAndAsCd( $custCd, $asCd, $postclause = '' ){
        global $appconfig, $app, $errmsg, $logger;
        
        $where = "WHERE CUST_CD = '" . $custCd . "' AND AS_CD = '" . $asCd . "'";
        $result = $this->query($where, $postclause);
        if($result < 0){
            $errmsg = 'Unable to find customer in  CUST_ASP: ' . $where;

            $return['error'] = true;
            $return['update'] = false;
            $return['msg']   = $errmsg;

            return $return;
        }

        if($row = $this->next()){
            return $this;
        } 

        return NULL;

    }

     /***************************************************************************
     ****************************************************************************
     * function: getCustAspByAcctNumAndAsCdAndCustCd
     * parameters:
     *  @custCd = Customer Code
     *  @asCd = Finance Company
     *  @acctCD = Account Code
     *  @postclause = Post Clause
     *
     * return:
     *  CUST_ASP record
     ****************************************************************************
     ****************************************************************************/
    public function getCustAspByAcctNumAndAsCdAndCustCd( $custCd, $asCd, $acctCD, $postclause = '' ){
        global $appconfig, $app, $errmsg, $logger;
        
        $where = "WHERE CUST_CD = '" . $custCd . "' AND AS_CD = '" . $asCd . "' AND ACCT_CD = '" . $acctCD . "' ";

        $result = $this->query($where, $postclause);
        if($result < 0){
            $errmsg = 'Unable to find customer in  CUST_ASP: ' . $where;

            $return['error'] = true;
            $return['update'] = false;
            $return['msg']   = $errmsg;

            return $return;
        }

        if($row = $this->next()){
            return $this;
        } 

        return NULL;
    }

     /***************************************************************************
     ****************************************************************************
     * function: getCustAspByAsCdAndAcctCd
     * parameters:
     *  @asCd = Finance Company
     *  @acctCD = Account Code
     *  @postclause = Post Clause
     *
     * return:
     *  CUST_ASP record
     ****************************************************************************
     ****************************************************************************/
    public function getCustAspByAsCdAndAcctCd( $asCd, $acctCd, $postclause = '' ){
        global $appconfig, $app, $errmsg, $logger;
        
        $where = "WHERE AS_CD = '" . $asCd . "' AND ACCT_CD = '" . $acctCd . "' ";
        $result = $this->query($where, $postclause);
        if($result < 0){
            $errmsg = 'Unable to find customer in  CUST_ASP: ' . $where;

            $return['error'] = true;
            $return['update'] = false;
            $return['msg']   = $errmsg;

            return $return;
        }

        if($row = $this->next()){
            return $this;
        } 

        return NULL;


    }

     /***************************************************************************
     ****************************************************************************
     * function: getCustAspByAsCdAndAcctCdAndCustCd
     * parameters:
     *  @asCd = Finance Company
     *  @acctCD = Account Code
     *  @custCd = Customer Code
     *  @postclause = Post Clause
     *
     * return:
     *  CUST_ASP record
     ****************************************************************************
     ****************************************************************************/
    public function getCustAspByAsCdAndAcctCdAndCustCd( $asCd, $acctCd, $custCd, $postclause = '' ){
        global $appconfig, $app, $errmsg, $logger;
        
        $where = "WHERE AS_CD = '" . $asCd . "' AND ACCT_CD = '" . $acctCd . "' AND CUST_CD =  '" . $custCd . "' ";
        $result = $this->query($where, $postclause);
        if($result < 0){
            $errmsg = 'Unable to find customer in  CUST_ASP: ' . $where;

            $return['error'] = true;
            $return['update'] = false;
            $return['msg']   = $errmsg;

            return $return;
        }

        if($row = $this->next()){
            return $this;
        } 

        return NULL;


    }
     /***************************************************************************
     ****************************************************************************
     * function: getCustAspByCustCdAndAsCdAndAcctNum
     * parameters:
     *  @custCd = Customer Code
     *  @asCd = Finance Company
     *  @postclause = Post Clause
     *
     * return:
     *  CUST_ASP record
     ****************************************************************************
     ****************************************************************************/
    public function getCustAspByCustCdAndAsCdAndAcctNum( $custCd, $asCd, $acctNum, $postclause = '' ){
        global $appconfig, $app, $errmsg, $logger;
        
        $where = "WHERE CUST_CD = '" . $custCd . "' AND AS_CD = '" . $asCd . "'";
        if( $acctNum == 'null' ){
            $where .= " AND ACCT_NUM IS NULL ";
        }
        else{
            $where .= " AND ACCT_NUM = '" . $acctNum . "' ";
        }

        $result = $this->query($where, $postclause);
        if($result < 0){
            $errmsg = 'Unable to find customer in  CUST_ASP: ' . $where;

            $return['error'] = true;
            $return['update'] = false;
            $return['msg']   = $errmsg;

            return $return;
        }

        if($row = $this->next()){
            return $this;
        } 

        return NULL;

    }
     /***************************************************************************
     ****************************************************************************
     * function: updateCustAsp
     * parameters:
     *  @where = Update Array
     *  @updt = Upate Array
     *  
     * return:
     *  CUST_ASP record
     ****************************************************************************
     ****************************************************************************/
    public function updateCustAsp( $whereClause, $updt ){
        global $appconfig, $app, $errmsg, $logger;

        $where = 'WHERE ';
        $first = true;
        foreach( $whereClause as $key => $value ){
            if( $first ) {
                $first = false;
            }
            else{
                $where .= ' AND '; 
            }

            if ( $value == 'NULL' ){
                $where .= $key . " is " . $value . " ";
            }
            else{
                $where .= $key . " = '" . $value . "' ";
            }
        }

        foreach( $updt as $key => $value ){
            $col = "set_" . $key;
            $this->$col( $value );
        }

        $result = $this->update($where, false);

        if($result < 0){
            $errmsg = 'Unable to update CUST_ASP: ' . $where;

            $return['error'] = true;
            $return['update'] = false;
            $return['msg']   = $errmsg;
            echo json_encode($return);
                
            return false; 

        }

        return $this;
    }
     /***************************************************************************
     ****************************************************************************
     * function: getCustAspByAcctNumAndAsCdAndCustCdAndAcctCd
     * parameters:
     *  @custCd = Customer Code
     *  @asCd = Finance Company
     *  @acctCD = Account Code
     *  @acctNum = Account Number
     *  @postclause = Post Clause
     *
     * return:
     *  CUST_ASP record
     ****************************************************************************
     ****************************************************************************/
    public function getCustAspByAcctNumAndAsCdAndCustCdAndAcctCd( $custCd, $asCd, $acctCD, $acctNum, $postclause = '' ){
        global $appconfig, $app, $errmsg, $logger;
        
        $where = "WHERE CUST_CD = '" . $custCd . "' AND AS_CD = '" . $asCd . "' AND  ";

        if ( $acctCD == 'null' ){
            $where .= " ACCT_CD IS NULL ";
        }
        else{
            $where .= " ACCT_CD = '" . $acctCD . "' ";

        }
        $where .= ' AND ';
        if ( $acctNum == 'null' ){
            $where .= " ACCT_NUM IS NULL ";
        }
        else{
            $where .= " ACCT_NUM = '" . $acctNum . "' ";

        }

        $result = $this->query($where, $postclause);
        if($result < 0){
            $errmsg = 'Unable to find customer in  CUST_ASP: ' . $where;

            $return['error'] = true;
            $return['update'] = false;
            $return['msg']   = $errmsg;

            return $return;
        }

        if($row = $this->next()){
            return $this;
        } 

        return NULL;
    }

}

?>
