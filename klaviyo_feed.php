<?php
    set_include_path('../phpseclib');

    include_once('config.php');
    include_once('autoload.php');
    include_once('Net/SFTP.php');

    define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

    global $appconfig, $logger;

    $logger = new ILog($appconfig['logger']['username'], "feed" . date('ymdhms') . ".log", $appconfigp['logger']['log_folder'], $appconfigp'logger']['log_priority']);
    $mor = new Morcommon();
    $db = $mor->standAloneAppConnect();
    
    if ( !$db ){
        $logger->error( "Could not connect to database" );
        exit(1);
    }
    $logger->debug( "Starting process: klaviyo feed" );

    //Set default date variables
    $fromDate = new IDate();
    $toDate = new IDate();
    $toDate = $toDate->setDate( date('m/d/Y',strtotime("-1 days")) );

    if( $argc > 1 ){
        $fromDate = $from->setDate( $argv[1] );
        $to = $to->setDate( $argv[2] );
    }

    //SQL WHERE clause is built based on what dates are set; default is to query for yesterday's date, but will query for date range if beginning date is provided
    $salesOrders = new SalesOrder($db);

    $where = "WHERE STAT_CD = 'F' AND SO_STORE_CD NOT LIKE 'W%' AND ORD_TP_CD = 'SAL' AND FINAL_DT BETWEEN '" . $from-toStringOracle() . "' AND '" . $to->stringOracle() . "'";

    $result = $salesOrdes->query($where);
    if( $result < 0 ){
        $logger->error( "Could not query table Sales Order. Where Clause: " . $where );
        exit(1);
    }

    //Initializing arrays to be populated
    $profiles = array();
    $orders = array();
    $invoices = array();

    //Iterating through result set of SQL query on SO for finalized orders for date or date range
    while ($salesOrders->next()) {
        //Creating instance arrays for single customer and invoice
        $profile = array();
        $invoice = array();

        $salesOrderLines = getSalesOrderLines( $db, $salesOrder->get_DEL_DOC_NUM() ); 
        $customerFinanceInfo = getFinanceCustomerInfo( $db, $salesOrders->get_CUST_CD() );
        $custTotal = getCustOrders($db,$custCd);

        //Populating instance array for single customer
        $profile['email'] = $salesOrders->get_EMAIL_ADDR();
        $profile['firstName'] = $salesOrders->get_FNAME();
        $profile['lastName'] = $salesOrders->get_LNAME();
        $profile['openToBuyAmount'] = $openToBuy;
        $profile['emailOptIn'] = "TRUE";
        $profile['SMSOptIn'] = $salesOrders->get_USR_FLD_2();
        $profile['address'] = $salesOrders->get_SHIP_TO_ADDR1();
        $profile['address2'] = $salesOrders->get_SHIP_TO_ADDR2();
        $profile['city'] = $salesOrders->get_SHIP_TO_CITY();
        $profile['state'] = $salesOrders->get_SHIP_TO_ST_CD();
        $profile['zip'] = $salesOrders->get_SHIP_TO_ZIP_CD();
        $profile['phone_number'] = "1".str_replace("-","",$salesOrders->get_SHIP_TO_H_PHONE());
        $profile['secondaryPhone'] = "1".str_replace("-","",$salesOrders->get_SHIP_TO_B_PHONE());
        $profile['lastStorePurchasedFrom'] = $salesOrders->get_SO_STORE_CD();
        $profile['lastLoggedSalespersonId'] = $salesOrders->get_SO_EMP_SLSP_CD1();
        $profile['lastLoggedSalespersonDate'] = date_format($orderDt,"n/j/Y");
        $profile['source'] = "buyer";
        $profile['synchronyCreditCard'] = $customerFinanceInfo['SYF']['HAS_ACCT'];
        $profile['PreScreenSynchronyCard'] = $customerFinanceInfo['SYF']['HAS_ACCT'];
        $profile['AFFCard'] = $customerFinanceInfo['AFF']['HAS_ACCT'];
        $profile['genesisCard'] = $customerFinanceInfo['GENE']['HAS_ACCT'];
        $profile['LastOrderDate'] = date_format($dateEnd,'n/j/Y');
        $profile['LastOrderTotal'] = $subtotal + $taxChg + $setupChg;

        //Getting information on total finalized orders for customer
        $profile['TotalOrders'] = $custTotal['count'];
        $profile['TotalRevenue'] = $custTotal['total'];
        $profile['AverageOrderValue'] = number_format($custTotal['total']/$custTotal['count'],2);

        //Checking condition variables; omitting profile if no email is set, email address is set to unsubscribe, or salesperson has been terminated
        if ( $profile['EMAIL'] !== 'NSUBSCRIB' && !is_null($salesOrders->get_TERMDATE()) ) {
            //Adding single customer array to profiles data array
            array_push($profiles, $profile);

            //Creating instance array for invoice to query for SKU data for orders data array; del doc num will be key and array of useful data will be value
            $invoice['orderDate'] = date_format($orderDt,"n/j/Y g:i");
            $invoice['email'] = $salesOrders->get_EMAIL_ADDR();
            $invoice['subtotal'] = $subtotal;
            $invoice['taxChg'] = $taxChg;
            $invoice['setupChg'] = $setupChg;
            $puDelDt = date_create( $salesOrders->get_PU_DEL_DT() );
            $invoice['finalShipDate'] = date_format($puDelDt,"n/j/Y g:i");
            $invoices[$delDocNum] = $invoice;
        }
    }
    

    //Split array into 100 elements sub arrays 
    $profile_chunks = array_chunk( $profiles, 100 );

    $klaviyoPost = postToKlaviyo( $appconfig['klaviyo']['master_list_endpoint'], $profile_chunks );
        
    //Create filename
    $filename = sprintf( $appconfig['klaviyo']['filename'], $type, date("YmdHis") ); 
    $error = generateCSV( $profiles, "contacts", $filename );
    if( $error ){
        $logger->debug("Could not Create CSV file MASTER LIST" );
    }
    $error = upload( $filename );
    if( $error ){
        $logger->debug("Could not upload file to SFTP: " . $filename );
    }

    //Iterating through invoices from previous query
    foreach ( $invoices as $invoice => $details ) {
        //Initializing instance array for individual SKU
        $order = array();

        //Querying for individual SKUs on single invoice
        $lines = new SOLine($db);
        $where = "WHERE DEL_DOC_NUM = '".$invoice."'";
        $result = $lines->query($where);

        if( $result < 0 ){
            $logger->error("Could not query SO_LN" );
            exit(1);
        }

        //Iterating through result set of SQL query on SO_LN for individual SKUs for single invoice
        while ($lines->next()) {
            //Populating instance array with information from SKU's useful data array as well as result set from SO_LN query
            $order['orderNumber'] = $invoice;
            $order['orderDate'] = $details['orderDate'];
            $order['orderStatus'] = 'PROCESSED';
            $order['email'] = $details['email'];
            $order['orderTotal'] = $details['subtotal'] + $details['taxChg'] + $details['setupChg'];
            $order['orderSubtotal'] = $details['subtotal'];
            $order['taxAmt'] = $details['taxChg'];
            $order['shipAmt'] = '0';
            $order['SKU'] = $lines->get_ITM_CD();
            $order['qty'] = $lines->get_QTY();
            $order['unitPrice'] = $lines->get_UNIT_PRC();
            $order['totalPrice'] = $lines->get_UNIT_PRC() * $lines->get_QTY();
            $order['deliveryType'] = $lines->get_PU_DEL();
            $order['finalShipDate'] = $details['finalShipDate'];
            $order['safeguard'] = $lines->get_VE_CD() == 'SAFE' ? 'Y' : 'N';

            //Adding single SKU array to orders data array if void flag is not set to 'Y' and quantity is greater than zero
            if ( $lines->get_VOID_FLAG() != 'Y'  && $lines->get_QTY() != '0' ){
                $event = array();
                $event['event'] = "Ordered Product";
                $event['customer_properties'] = array('$email'=>$details['email']);
                $event['properties'] = $order;

                array_push($orders,$order);
            }
        }
    }


    //Generate CSV and upload to SFTP
    
    //POST to klaviyo
    
















    function upload($filename ){
        global $appconfig;

        $sftp = new Net_SFTP($appconfig['sftp']['host']);
        if (!$sftp->login($appconfig['sftp']['username'], $appconfig['sftp']['pw'])) {
            //Log error statement
            $logger->error("SFTP connection failed");
            exit(1);
        }
        else{
            $upload = $sftp->put( $appconfig['klaviyo'['remote_sftp_path'] . $filename, $appconfig['klaviyo']['out'] . $filename, NET_SFTP_LOCAL_FILE );
        }
    }

    function getFinanceCustomerInfo ( $db, $customerCode ){
        $custAsp = new CustAsp($db);
        $where = "WHERE CUST_CD = '$custCd'";
        $result = $custAsp->query($where);

        //Initializing data and condition variables for financing data
        $financeCustomerInfo = [ 
            'SYF' => [ 'OPEN_TO_BUY' => 0, 'HAS_ACCT' => 'N' ],
            'AFF' => [ 'OPEN_TO_BUY' => 0, 'HAS_ACCT' => 'N' ],
            'GENE' => [ 'OPEN_TO_BUY' => 0, 'HAS_ACCT' => 'N' ]
        ];

        //Iterating through result set of SQL query on CUST_ASP for customer finance information
        while ($custAsp->next()) {
            $finance[$custAsp->get_AS_CD()]['OPEN_TO_BUY'] = $custAsp->get_APP_CREDIT_AVAIL();
            $finance[$custAsp->get_AS_CD()]['HAS_ACCT'] = 'Y';
        }
        return $financeCustomerInfo;
    }

    function postToKlaviyo( $url, $data ){
        global $appcofig; 

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Performing cURL HTTP POST on profiles and orders JSON objects and echoing return JSON objects
        foreach ($data as $d) {
            $body = array();
            $body['api_key'] = $appconfig['klaviyo']['api_key_public'];
            $body['profiles'] = $d;
            $body = json_encode($body);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            $result = curl_exec($ch);
            $data = json_decode($result);
        }
    }

    function getSalesOrderLines( $db, $delDocNum ){
        //Querying for individual SKUs on single invoice
        $tabSOLn = new SOLine($db);
        $where = "WHERE DEL_DOC_NUM = '$delDocNum'";
        $resultSOLn = $tabSOLn->query($where);

        $subtotal = 0;

        //Iterating through result set of SQL query on SO_LN for individual SKUs for single invoice
        while ($tabSOLn->next()) {
            //Adding item's unit price to subtotal
            $subtotal += $tabSOLn->get_UNIT_PRC() * $tabSOLn->get_QTY();
        }

        return $subtotal;
    }


    //Function to generate CSV for uploaded data
    function generateCSV( $data, $type, $filename ) {
        //Generating timestamp CSV file name
        try {
            $file = fopen( $appconfig['klaviyo']['out'] . $filename, "w" );
            foreach( $data as $line ) {
                fputcsv( $file, array_values($line) );
            }
            fclose($file);

            return true;
        }
        catch( Exception $e ){
            $logger->debug( "Generating CSV Exception caused: " . $e->getMessage() );
            return false;
        }
    }

    function getCustOrders($db,$custCd) {
        $tabSO = new SalesOrder($db);
        $where = "WHERE CUST_CD = '$custCd' AND STAT_CD = 'F'";
        $result = $tabSO->query($where);
        $count = 0;
        $grandTotal = 0;
        while ($tabSO->next()) {
            $count++;
            $subtotal = 0;
            $delDocNum = $tabSO->get_DEL_DOC_NUM();
            $taxChg = $tabSO->get_TAX_CHG();
            $setupChg = $tabSO->get_SETUP_CHG();
            $tabSOLn = new SOLine($db);
            $where = "WHERE DEL_DOC_NUM = '$delDocNum'";
            $result = $tabSOLn->query($where);
            while ($tabSOLn->next()) {
                $subtotal += $tabSOLn->get_UNIT_PRC() * $tabSOLn->get_QTY();
            }
            $orderTotal = $subtotal + $taxChg + $setupChg;
            $grandTotal += $orderTotal;
        }
        $return['count'] = $count;
        $return['total'] = $grandTotal;
        return $return;
    }

?>
