<?php

class Item extends IDBTable {

	public function __construct($db) {
		parent::__construct($db);
		$this->tablename        = 'ITM';
		$this->dbcolumns        = array(
										  'ITM_CD'=>'SKU' 
										, 'COMM_CD'=>'COMM_CD' 
										, 'VE_CD'=>'VENDOR' 
										, 'RELATED_ITM_CD'=>'RELATED_ITM_CD' 
										, 'MNR_CD'=>'MNR_CD' 
										, 'CAT_CD'=>'CAT_CD' 
										, 'SUPER_CD'=>'SUPER_CD' 
										, 'ITM_TP_CD'=>'TYPE' 
										, 'SPEC_ORD_FLAG'=>'SPEC_ORD_FLAG' 
										, 'MEAS_CD'=>'MEAS_CD' 
										, 'RET_PRC'=>'RETAIL' 
										, 'REPL_CST'=>'REPL_CST' //11 
										, 'LST_ACT_DT'=>'LST_ACT_DT' 
										, 'VSN'=>'VSN' 
										, 'DES'=>'DESCRIPTION' 
										, 'SIZ'=>'SIZ' 
										, 'SIZ_ID'=>'SIZ_ID' 
										, 'SIZ_CD'=>'SIZ_CD' 
										, 'FINISH'=>'FINISH' 
										, 'FINISH_ID'=>'FINISH_ID' 
										, 'FINISH_CD'=>'FINISH_CD' 
										, 'COVER'=>'COVER' 
										, 'COVER_ID'=>'COVER_ID' 
										, 'COVER_CD'=>'COVER_CD' 
										, 'GRADE'=>'GRADE' 
										, 'GRADE_ID'=>'GRADE_ID' 
										, 'GRADE_CD'=>'GRADE_CD' 
										, 'UDF5'=>'UDF5' 
										, 'UDF5_ID'=>'UDF5_ID' 
										, 'UDF5_CD'=>'UDF5_CD' 
										, 'UDF6'=>'UDF6' 
										, 'UDF6_ID'=>'UDF6_ID' 
										, 'UDF6_CD'=>'UDF6_CD' 
										, 'UDF7'=>'UDF7' 
										, 'UDF7_ID'=>'UDF7_ID' 
										, 'UDF7_CD'=>'UDF7_CD' 
										, 'DROP_DT'=>'DROP_DT' 
										, 'PTAG_PRINT_QTY'=>'PTAG_PRINT_QTY' 
										, 'CRPT_WID'=>'CRPT_WID' 
										, 'SER_PCT'=>'SER_PCT' 
										, 'SMR_PCT'=>'SMR_PCT' 
										, 'VOL'=>'VOL' //41
										, 'WEIGHT'=>'WEIGHT' 
										, 'PALLET_QTY'=>'PALLET_QTY' 
										, 'PO_LEAD_TIME'=>'PO_LEAD_TIME' 
										, 'STAT_CD'=>'STATUS' 
										, 'STAT_DT'=>'STAT_DT' 
										, 'FAMILY_CD'=>'FAMILY' //47
										, 'STYLE_CD'=>'STYLE CODE' 
										, 'SPIFF'=>'SPIFF' 
										, 'RET_PRC_CHNG_DT'=>'RET_PRC_CHNG_DT' 
										, 'ADV_PRC'=>'ADV_PRC' 
										, 'IVC_CST'=>'IVC_CST' 
										, 'FRT_FAC'=>'FRT_FAC' //53
										, 'DAYS_WAR'=>'DAYS_WAR' 
										, 'WARRANTABLE'=>'WARRANTABLE' 
										, 'EXC_DT'=>'EXC_DT' 
										, 'LABEL_TP_CD'=>'LABEL_TP_CD' 
										, 'PKG_SPLIT_METHOD'=>'PKG_SPLIT_METHOD' 
										, 'PKG_CMPNT'=>'PKG_CMPNT' 
										, 'RCV_LABEL_CD'=>'RCV_LABEL_CD' 
										, 'INVENTORY'=>'INVENTORY' 
										, 'FGN_REPL_CST'=>'FGN_REPL_CST' 
										, 'FGN_DUTY_RATE'=>'FGN_DUTY_RATE' 
										, 'TREATABLE'=>'TREATABLE' 
										, 'DROP_CD'=>'DROP_CD' 
										, 'PRC1'=>'PRC1' 
										, 'PRC1_CHNG_DT'=>'PRC1_CHNG_DT' 
										, 'PRC3_CHNG_DT'=>'PRC3_CHNG_DT' 
										, 'SETUP_REQ'=>'SETUP_REQ' 
										, 'IN_CARTON'=>'IN_CARTON' 
										, 'GENERIC_SKU'=>'GENERIC_SKU' 
										, 'VSAL_QTY'=>'VSAL_QTY' 
										, 'POINT_SIZE'=>'POINT_SIZE' 
										, 'CALC_AVAIL'=>'CALC_AVAIL' 
										, 'CMDTY_CD'=>'CMDTY_CD' 
										, 'SHOW_RLP_PRICES'=>'SHOW_RLP_PRICES' 
										, 'ALT_DES'=>'ALT_DES' 
										, 'PU_DISC_PCNT'=>'PU_DISC_PCNT' 
										, 'FRAN_MRKUP_PCNT'=>'FRAN_MRKUP_PCNT' 
										, 'BATCH_TP_ITM'=>'BATCH_TP_ITM' 
										, 'BULK_TP_ITM'=>'BULK_TP_ITM' 
										, 'USER_QUESTIONS'=>'USER_QUESTIONS' 
										, 'MASTER_PACK_QTY'=>'MASTER_PACK_QTY' 
										, 'PRE_TREATED'=>'PRE_TREATED' 
										, 'CELL_SAVE'=>'CELL_SAVE' 
										, 'CELL_INC_CALC'=>'CELL_INC_CALC' 
										, 'CELL_PHONE'=>'CELL_PHONE' 
										, 'STOP_TIME'=>'STOP_TIME' 
										, 'FRAME_TP_ITM'=>'FRAME_TP_ITM' 
										, 'OPTION_LIST_GRP'=>'OPTION_LIST_GRP' 
										, 'AVAIL_PAD_DAYS'=>'AVAIL_PAD_DAYS' 
										, 'BLOCK_RETAIL'=>'BLOCK_RETAIL' 
										, 'ALLOW_CREATE_CUST_ORD_OPTION'=>'ALLOW_CREATE_CUST_ORD_OPTION' 
										, 'WARR_DAYS'=>'WARR_DAYS' 
										, 'SERIAL_TP'=>'SERIAL_TP' 
										, 'CELL_TYPE'=>'CELL_TYPE' 
										, 'RET_PLAN_CD'=>'RET_PLAN_CD' 
										, 'RET_PLAN_EFF_DT'=>'RET_PLAN_EFF_DT' 
										, 'USED_MERCH'=>'USED_MERCH' 
										, 'PUR_SLSP_CD'=>'PUR_SLSP_CD' 
										, 'CREATE_DT'=>'CREATE_DT' 
										, 'NSP_AMT'=>'NSP_AMT' 
										, 'TAX_RESP'=>'TAX_RESP' 
										, 'EXT_DES'=>'EXT_DES' 
										, 'LANDED_COST' => 'LANDED COST' //105
										, 'MARGIN' => 'MARGIN'
										, 'CLOSE_DT' => 'Close Date'
										, 'ITM_STATUS' => 'Item Status'
										, 'ITM_STATE' => 'ITM_STATE'
										);

		$this->dbcolumns_date  = array(
										  'LST_ACT_DT'
										, 'DROP_DT'
										, 'STAT_DT'
										, 'RET_PRC_CHNG_DT'
										, 'EXC_DT'
										, 'PRC1_CHNG_DT'
										, 'PRC3_CHNG_DT'
										, 'RET_PLAN_EFF_DT'
										, 'CREATE_DT'
										, 'CLOSE_DT'
										);

		$this->dbcolumns_function = array(
										  'LANDED_COST' => 'REPL_CST + ((REPL_CST * FRT_FAC)/100) LANDED_COST'
										, 'MARGIN' => 'case ITM.RET_PRC when 0 then -1 else ((ITM.RET_PRC - (ITM.REPL_CST + (.01 * ITM.REPL_CST * ITM.FRT_FAC)))/ITM.RET_PRC) end MARGIN'
										, 'ITM_STATUS' => 'CLOSE_DT ITM_STATUS'
										);

/* original
select ITM.ITM_CD, VE_CD, DES, ITM.RET_PRC, ITM_TP_CD, ALT_DES, QTY, TO_CHAR(DROP_DT, 'DD-MON-YYYY') DROP_DT, REPL_CST, VSN, FAMILY_CD, FRT_FAC, VOL, ITM.REPL_CST + ((ITM.REPL_CST * ITM.FRT_FAC)/100) LANDED_COST, ((ITM.RET_PRC - (ITM.REPL_CST + (.01 * ITM.REPL_CST * ITM.FRT_FAC)))/ITM.RET_PRC) MARGIN, ITM.CLOSE_DT ITM_STATUS, TO_CHAR(CLOSE_DT, 'DD-MON-YYYY') CLOSE_DT 
from itm, package  
WHERE PACKAGE.PKG_ITM_CD = '528202541' and PACKAGE.CMPNT_ITM_CD = ITM.ITM_CD 

*/

		/*
		select
			ITM.ITM_CD,
			VE_CD,
			DES,
			ITM.RET_PRC,
			ITM_TP_CD,
			ALT_DES,
			QTY,
			TO_CHAR(DROP_DT, 'DD-MON-YYYY') DROP_DT,
			REPL_CST,
			VSN,
			FAMILY_CD,
			FRT_FAC,
			VOL,
			ITM.REPL_CST + ((ITM.REPL_CST * ITM.FRT_FAC) / 100) LANDED_COST,
			case ITM.RET_PRC when 0 then -1 else ((ITM.RET_PRC - (ITM.REPL_CST + (.01 * ITM.REPL_CST * ITM.FRT_FAC)))/ITM.RET_PRC) end MARGIN,
			ITM.CLOSE_DT ITM_STATUS,
			TO_CHAR(CLOSE_DT, 'DD-MON-YYYY') CLOSE_DT
		from itm, package
		where PACKAGE.PKG_ITM_CD = '528202541' and PACKAGE.CMPNT_ITM_CD = ITM.ITM_CD
		*/

 		$this->setAutoIDColumn("ITM_CD");

	}

    /*-----------------------------------------------------------------------------------
     *------------------------------------ getItemByItemCode ----------------------------
     *-----------------------------------------------------------------------------------
     *
     * Function getItemByItemCode: 
     *      Routine will query table ITM by ITM_CD
     *
     * Parameters:
     *      (int) Item Code 
     *
     * Return:
     *      (IDBT Cursor) Cursor to table ITM
     *      
     */
    public function getItemByItemCode( $itemCD, $postclause ){
        global $appconfig, $app, $errmsg, $logger;
        $where = "WHERE ITM_CD = '" . $itemCD . "' ";


        $result = $this->query($where, $postclause);

        if($result < 0){
            $errmsg = 'Unable to query ITM: ' . $where;

            $return['error'] = true;
            $return['update'] = false;
            $return['msg']   = $errmsg;
            echo json_encode($return);

            return $return;

        }
        if ( $row = $this->next() ){
            return $this;
        }

        return NULL;
    }

    public function updateItemByItemCode( $itmCd, $updt ){
        global $appconfig, $app, $logger, $errmsg;
        $where = " WHERE ITM_CD = '" . $itmCd . "' ";

        foreach( $updt as $key => $value ){
            if( $key === 'ITM_STATUS' ){
                continue;
            }
            $col = "set_" . $key;
            $this->$col($value);
        }
        $result = $this->update( $where, false );

        if($result < 0){
            return false; 

        }
        return $this;

    }

    public function removeItemFunctionColumnsAndAddColumns(){
        unset($this->dbcolumns['ITM_STATUS']);
        unset($this->dbcolumns['MARGIN']);
        unset($this->dbcolumns['LANDED_COST']);
        $this->dbcolumns['FLOOR_DESC'] = 'Floor Description';
    }
}

?>


