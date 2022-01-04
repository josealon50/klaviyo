<?php
class SOLine extends IDBTable {
	public function __construct($db) {
		parent::__construct($db);
        $this->tablename        = 'SO_LN JOIN ITM ON ITM.ITM_CD = SO_LN.ITM_CD';
		$this->dbcolumns        = array(
									  'DEL_DOC_NUM' => 'DEL_DOC_NUM'
                                    , 'DEL_DOC_LN#' => 'DEL_DOC_LN#'
                                    , 'ITM_CD' => 'ITM_CD'
                                    , 'WAR_EXP_DT' => 'WAR_EXP_DT'
                                    , 'COMM_CD' => 'COMM_CD'
                                    , 'STORE_CD' => 'STORE_CD'
                                    , 'LOC_CD' => 'LOC_CD'
                                    , 'REF_DEL_DOC_LN#' => 'REF_DEL_DOC_LN#'
                                    , 'REF_ITM_CD' => 'REF_ITM_CD'
                                    , 'REF_SER_NUM' => 'REF_SER_NUM'
                                    , 'FAB_DEL_DOC_NUM' => 'FAB_DEL_DOC_NUM'
                                    , 'FAB_DEL_DOC_LN#' => 'FAB_DEL_DOC_NUM#'
                                    , 'OUT_ID_CD' => 'OUT_ID_CD'
                                    , 'OUT_CD' => 'OUT_CD'
                                    , 'SO_DOC_NUM' => 'SO_DOC_NUM'
                                    , 'ID_NUM' => 'ID_NUM'
                                    , 'SER_NUM' => 'SER_NUM'
                                    , 'UNIT_PRC' => 'UNIT_PRC'
                                    , 'QTY' => 'QTY'
                                    , 'SPIFF' => 'SPIFF'
                                    , 'PICKED' => 'PICKED'
                                    , 'OUT_ENTRY_DT' => 'OUT_ENTRY_DT'
                                    , 'FIFL_DT' => 'FIFL_DT'
                                    , 'FIFO_CST' => 'FIFO_CST'
                                    , 'FIFO_DT' => 'FIFO_DT'
                                    , 'FILL_DT' => 'FILL_DT'
                                    , 'VOID_FLAG' => 'VOID_FLAG'
                                    , 'COD_AMT' => 'COD_AMT'
                                    , 'OOC_QTY' => 'OOC_QTY'
                                    , 'UP_CHRG' => 'UP_CHRG'
                                    , 'VOID_DT' => 'VOID_DT'
                                    , 'PRC_CHG_APP_CD' => 'PRC_CHG_APP_CD'
                                    , 'TAKEN_WITH' => 'TAKEN_WITH'
                                    , 'PKG_SOURCE' => 'PKG_SOURCE'
                                    , 'WARRANTED_BY_LN#' => 'WARRANTED_BY_LN#'
                                    , 'WARRANTED_BY_ITM_CD' => 'WARRANTED_BY_ITM_CD'
                                    , 'TREATS_LN#' => 'TREATS_LN#'
                                    , 'TREATS_ITM_CD' => 'TREATS_ITM_CD'
                                    , 'TREATED_BY_LN#' => 'TREATED_BY_LN#'
                                    , 'TREATED_BY_ITM_CD' => 'TREATED_BY_ITM_CD'
                                    , 'DISC_AMT' => 'DISC_AMT'
                                    , 'SER_CD' => 'SER_CD'
                                    , 'SO_LN_CMNT' => 'SO_LN_CMNT'
                                    , 'ADDON_WR_DT' => 'ADDON_WR_DT'
                                    , 'LV_IN_CARTON' => 'LV_IN_CARTON'
                                    , 'EE_COUNT' => 'EE_COUNT'
                                    , 'WARR_ID_1' => 'WARR_ID_1'
                                    , 'WARR_EXP_DT_1' => 'WARR_EXP_DT_1'
                                    , 'WARR_ID_2' => 'WARR_ID_2'
                                    , 'WARR_EXP_DT_2' => 'WARR_EXP_DT_2'
                                    , 'WARR_ID_3' => 'WARR_ID_3'
                                    , 'WARR_EXP_DT_3' => 'WARR_EXP_DT_3'
                                    , 'WARR_ID_4' => 'WARR_ID_4'
                                    , 'WARR_EXP_DT_4' => 'WARR_EXP_DT_4'
                                    , 'WARR_ID_5' => 'WARR_ID_5'
                                    , 'WARR_EXP_DT_5' => 'WARR_EXP_DT_5'
                                    , 'WARR_ID_6' => 'WARR_ID_6'
                                    , 'WARR_EXP_DT_6' => 'WARR_EXP_DT_6'
                                    , 'SE_CAUSE_CD' => 'SE_CAUSE_CD'
                                    , 'SE_STATUS' => 'SE_STATUS'
                                    , 'SO_LN_SEQ' => 'SO_LN_SEQ'
                                    , 'PU_DISC_AMT' => 'PU_DISC_AMT'
                                    , 'COMPLAINT_CD' => 'COMPLAINT_CD'
                                    , 'SO_EMP_SLSP_CD1' => 'SO_EMP_SLSP_CD1'
                                    , 'SO_EMP_SLSP_CD2' => 'SO_EMP_SLSP_CD2'
                                    , 'PCT_OF_SALE1' => 'PCT_OF_SALE1'
                                    , 'PCT_OF_SALE2' => 'PCT_OF_SALE2'
                                    , 'COMM_ON_SETUP_CHG' => 'COMM_ON_SETUP_CHG'
                                    , 'COMM_ON_DEL_CHG' => 'COMM_ON_DEL_CHG'
                                    , 'FRAN_MRKUP_AMT' => 'FRAN_MRKUP_AMT'
                                    , 'ACTIVATION_DT' => 'ACTIVATION_DT'
                                    , 'ACTIVATION_PHONE' => 'ACTIVATION_PHONE'
                                    , 'SHIP_GROUP' => 'SHIP_GROUP'
                                    , 'COMM_PAID_SLSP1' => 'COMM_PAID_SLSP1'
                                    , 'COMM_PAID_SLSP2' => 'COMM_PAID_SLSP2'
                                    , 'SHP_VE_CD' => 'SHP_VE_CD'
                                    , 'SHP_TRACK_ID' => 'SHP_TRACK_ID'
                                    , 'TAX_CD' => 'TAX_CD'
                                    , 'TAX_BASIS' => 'TAX_BASIS'
                                    , 'CUST_TAX_CHG' => 'CUST_TAX_CHG'
                                    , 'STORE_TAX_CHG' => 'STORE_TAX_CHG'
                                    , 'TAX_RESP' => 'TAX_RESP'
                                    , 'DELIVERY_POINTS' => 'DELIVERY_POINTS'
                                    , 'PU_DEL_DT' => 'PU_DEL_DT'
                                    , 'ALT_DOC_LN#' => 'ALT_DOC_LN#'
                                    , 'CUST_TAX_ADJ' => 'CUST_TAX_ADJ'
                                    , 'STORE_TAX_ADJ' => 'STORE_TAX_ADJ'
                                    , 'PRC_OVR_CD' => 'PRC_OVR_CD'
                                    , 'PRC_OVR_CMNT' => 'PRC_OVR_CMNT'
                                    , 'PRC_OVR_DT' => 'PRC_OVR_DT'
									);
        $this->dbcolumns_date = array ( 
                                      'WAR_EXP_DT' => 'WAR_EXP_DT'
                                    , 'OUT_ENTRY_DT' => 'OUT_ENTRY_DT'
                                    , 'FIFL_DT' => 'FIFL_DT'
                                    , 'FIFO_DT' => 'FIFO_DT'
                                    , 'FILL_DT' => 'FILL_DT'
                                    , 'VOID_DT' => 'VOID_DT'
                                    , 'ADDON_WR_DT' => 'ADDON_WR_DT'
                                    , 'WARR_EXP_DT_1' => 'WARR_EXP_DT_1'
                                    , 'WARR_EXP_DT_2' => 'WARR_EXP_DT_2'
                                    , 'WARR_EXP_DT_3' => 'WARR_EXP_DT_3'
                                    , 'WARR_EXP_DT_4' => 'WARR_EXP_DT_4'
                                    , 'WARR_EXP_DT_5' => 'WARR_EXP_DT_5'
                                    , 'WARR_EXP_DT_6' => 'WARR_EXP_DT_6'
                                    , 'ACTIVATION_DT' => 'ACTIVATION_DT'
                                    , 'PU_DEL_DT' => 'PU_DEL_DT'
                                    , 'PRC_OVR_DT' => 'PRC_OVR_DT'
                                    , 'VE_CD' => 'VE_CD'
                                );


        $this->dbcolumns_function = array( 
                                      'TAX_RESP' => 'SO_LN.TAX_RESP'
                                    , 'SPIFF' => 'SO_LN.SPIFF'
                                    , 'COMM_CD' => 'SO_LN.COMM_CD' 
                                    , 'ITM_CD' => 'SO_LN.ITM_CD' 
        );

		$this->errorMsg 			= "";

	}
}

?>
