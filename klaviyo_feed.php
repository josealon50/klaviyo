<?php
    date_default_timezone_set('America/Los_Angeles');

    set_include_path('./libs/phpseclib');

    include_once('config.php');
    include_once('autoload.php');
    include_once('Net/SFTP.php');

    define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);

    global $appconfig, $logger;

    $logger = new ILog($appconfig['logger']['username'], "klaviyo_feed" . date('ymdhms') . ".log", $appconfig['logger']['log_folder'], $appconfig['logger']['priority']);
    $mor = new Morcommon();
    $db = $mor->standAloneAppConnect();
    
    if ( !$db ){
        $logger->error( "Could not connect to database" );
        exit(1);
    }
    $logger->debug( "Starting process: klaviyo feed" );

    //Set default date variables
    $fromDate = new IDate();
    $fromDate = $fromDate->setDate( date('m/d/Y',strtotime("-1 days")), IDate::ORACLE_FORMAT );
    $toDate = new IDate();
    $toDate = $toDate->toStringOracle();

    if( $argc > 1 ){
        $fromDate = $from->setDate( $argv[1] );
        $fromDate = $fromDate->toStringOracle();
        $toDate = $toDate->setDate( $argv[2] );
        $toDate = $toDate->toStringOracle();
    }

    $logger->debug( "Processing finalized orders" );
    $finalizedSales = processFinalizedOrders( $db, $fromDate, $toDate );
    $profile_chunks = array_chunk( $finalizedSales['profiles'], 100 );
    $klaviyoPost = postToKlaviyo( $appconfig['klaviyo']['master_list_endpoint'], $profile_chunks );
    $logger->debug( "Finished processing finalized orders" );
    //Create filename
    $filename = sprintf( $appconfig['klaviyo']['filename'], 'finalized_orders', date("YmdHis") ); 
    $error = generateCSV( $finalizedSales['profiles'], $filename );
    if( $error ){
        $logger->debug("Could not Create CSV file profiles" );
    }
    $error = upload( $filename );
    if( $error ){
        $logger->debug("Could not upload file to SFTP: " . $filename );
    }

    $finalizedOrders = processInvoices( $db, $finalizedSales['invoices'] );
    $klaviyoPost = postToKlaviyo( $appconfig['klaviyo']['track_endpoint'], $finalizedOrders['events'] );
    //Generate CSV and upload to SFTP
    $filename = sprintf( $appconfig['klaviyo']['filename'], 'finalized_track_orders', date("YmdHis") ); 
    $error = generateCSV($finalizedOrders['orders'], $filename );
    if( $error ){
        $logger->debug("Could not Create CSV file profiles" );
    }

    $error = upload( $filename );
    if( $error ){
        $logger->debug("Could not upload file to SFTP: " . $filename );
    }
    $logger->debug( "Processing open orders" );
    $openOrders = processOpenOrders( $db, $fromDate, $toDate );
    $profile_chunks = array_chunk( $openOrders['profiles'], 100 );
    $logger->debug( "Posting to klaviyo open orders" );
    $klaviyoPost = postToKlaviyo( $appconfig['klaviyo']['master_list_endpoint'], $profile_chunks );
    $logger->debug( "Processing inovices from open orders" );
    $openOrdersEvents = processInvoices( $db, $openOrders['invoices'] );
    $klaviyoPost = postToKlaviyo( $appconfig['klaviyo']['track_endpoint'], $openOrdersEvents['events'] );

    //Generate CSV and upload to SFTP
    $logger->debug( "Generating CSV for open orders" );
    $filename = sprintf( $appconfig['klaviyo']['filename'], 'open_orders', date("YmdHis") ); 
    $error = generateCSV($openOrdersEvents['orders'], $filename);
    if( $error ){
        $logger->debug("Could not Create CSV file profiles" );
    }
    $logger->debug( "Uploading CSV for open orders to SFTP" );

    $error = upload( $filename );
    if( $error ){
        $logger->debug("Could not upload file to SFTP: " . $filename );
    }

    $filename = sprintf( $appconfig['klaviyo']['filename'], 'open_track_orders', date("YmdHis") ); 
    $error = generateCSV( $openOrders['profiles'], $filename );
    if( $error ){
        $logger->debug("Could not Create CSV file profiles" );
    }
    $error = upload( $filename );
    if( $error ){
        $logger->debug("Could not upload file to SFTP: " . $filename );
    }


    $logger->debug( "Querying customer prospects" );
    $prospects = processCustomerProspects( $db, $fromDate, $toDate );
    $klaviyoPost = postToKlaviyo( $appconfig['klaviyo']['track_prospects'], $prospects );

    $filename = sprintf( $appconfig['klaviyo']['filename'], 'customer_prospects', date("YmdHis") ); 
    $error = generateCSV( $prospects, $filename );
    if( $error ){
        $logger->debug("Could not Create CSV file for customer prospects" );
    }
    $error = upload( $filename );
    if( $error ){
        $logger->debug("Could not upload file to SFTP: " . $filename );
    }


    $logger->debug( "Finishing process: klaviyo feed" );

    function upload($filename ){
        global $appconfig, $logger;

        $sftp = new Net_SFTP($appconfig['sftp']['host']);
        if (!$sftp->login($appconfig['sftp']['username'], $appconfig['sftp']['pw'])) {
            //Log error statement
            $logger->error("SFTP connection failed could not upload filename: " . $filename);
            //exit(1);
        }
        else{
            $upload = $sftp->put( $appconfig['klaviyo']['remote_sftp_path'] . $filename, $appconfig['klaviyo']['out'] . $filename, NET_SFTP_LOCAL_FILE );
        }
    }

    function getFinanceCustomerInfo ( $db, $customerCode ){
        $custAsp = new CustAsp($db);
        $where = "WHERE CUST_CD = '" . $customerCode . "'";
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
        global $appconfig, $logger; 

        $result = '';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Performing cURL HTTP POST on profiles and orders JSON objects and echoing return JSON objects
        foreach ($data as $d) {
            $body = array();
            $body['api_key'] = $appconfig['klaviyo']['api_key'];
            $body['profiles'] = $d;
            $body = json_encode($body);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            $result = curl_exec($ch);
            $data = json_decode($result);
            $logger->debug("POST klaviyo " . print_r($result, 3) );
        }
        return $data;

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
    function generateCSV( $data, $filename ) {
        global $appconfig, $logger;
        //Generating timestamp CSV file name
        try {
            $file = fopen( $appconfig['klaviyo']['out'] . $filename, "w" );
            foreach( $data as $line ) {
                fputcsv( $file, array_values($line) );
            }
            fclose($file);

            return false;
        }
        catch( Exception $e ){
            $logger->debug( "Generating CSV Exception caused: " . $e->getMessage() );
            return true;
        }
    }

    function getCustOrders($db,$custCd) {
        $tabSO = new SalesOrder($db);
        $where = "WHERE SO.CUST_CD = '$custCd' AND STAT_CD = 'F'";
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

    function processInvoices( $db, $invoices ){
        $orders = array();
        $events = array();

        //Iterating through invoices from previous query
        foreach ( $invoices as $invoice => $details ) {
            //Initializing instance array for individual SKU
            $order = array();

            //Querying for individual SKUs on single invoice
            $lines = new SOLine($db);
            $where = "WHERE DEL_DOC_NUM = '" . $invoice . "'";
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

                    array_push( $orders, $order );
                    array_push( $events, $event );
                }
            }
        }

        return array( "orders" => $orders, "events" => $events );
    }

    function processOpenOrders( $db, $fromDate, $toDate ){
        global $appconfig, $logger;

        //Initializing arrays to be populated
        $salesOrders = new SalesOrder($db);

        //SQL WHERE clause is built based on what dates are set; default is to query for yesterday's date, but will query for date range if beginning date is provided
        $where = "WHERE STAT_CD = 'O' AND SO_STORE_CD NOT LIKE 'W%' AND ORD_TP_CD = 'SAL' AND SO_WR_DT BETWEEN '" . $fromDate . "' AND '" . $toDate . "'";
        $result = $salesOrders->query($where);
        if( $result < 0 ){
            $logger->error( "Could not query table Sales Order. Where Clause: " . $where );
            exit(1);
        }

        return processSales( $db, $salesOrders );


    }

    function processFinalizedOrders( $db, $fromDate, $toDate ){
        global $appconfig, $logger;

        //Initializing arrays to be populated
        $profiles = array();
        $invoices = array();
        $salesOrders = new SalesOrder($db);

        //SQL WHERE clause is built based on what dates are set; default is to query for yesterday's date, but will query for date range if beginning date is provided
        $where = "WHERE STAT_CD = 'F' AND SO_STORE_CD NOT LIKE 'W%' AND ORD_TP_CD = 'SAL' AND FINAL_DT BETWEEN '" . $fromDate . "' AND '" . $toDate . "'";
        $result = $salesOrders->query($where);
        if( $result < 0 ){
            $logger->error( "Could not query table Sales Order. Where Clause: " . $where );
            exit(1);
        }

        return processSales( $db, $salesOrders );

    }

    function processSales( $db, $data ){
        $profiles = array();
        $invoices = array();

        //Iterating through result set of SQL query on SO for finalized orders for date or date range
        while ($data->next()) {
            //Creating instance arrays for single customer and invoice
            $profile = array();
            $invoice = array();

            $salesOrderLinesTotal = getSalesOrderLines( $db, $data->get_DEL_DOC_NUM() ); 
            $customerFinanceInfo = getFinanceCustomerInfo( $db, $data->get_CUST_CD() );
            $custTotal = getCustOrders( $db, $data->get_CUST_CD() );

            //Populating instance array for single customer
            $profile['email'] = $data->get_EMAIL_ADDR();
            $profile['firstName'] = $data->get_FNAME();
            $profile['lastName'] = $data->get_LNAME();
            $profile['openToBuyAmount'] = $customerFinanceInfo['SYF']['OPEN_TO_BUY'];
            $profile['emailOptIn'] = "TRUE";
            $profile['SMSOptIn'] = $data->get_USR_FLD_2();
            $profile['address'] = $data->get_SHIP_TO_ADDR1();
            $profile['address2'] = $data->get_SHIP_TO_ADDR2();
            $profile['city'] = $data->get_SHIP_TO_CITY();
            $profile['state'] = $data->get_SHIP_TO_ST_CD();
            $profile['zip'] = $data->get_SHIP_TO_ZIP_CD();
            $profile['phone_number'] = "1".str_replace("-","",$data->get_SHIP_TO_H_PHONE());
            $profile['secondaryPhone'] = "1".str_replace("-","",$data->get_SHIP_TO_B_PHONE());
            $profile['lastStorePurchasedFrom'] = $data->get_SO_STORE_CD();
            $profile['lastLoggedSalespersonId'] = $data->get_SO_EMP_SLSP_CD1();
            $profile['lastLoggedSalespersonDate'] = date_format(new DateTime($data->get_SO_WR_DT()), "n/j/Y");
            $profile['source'] = "buyer";
            $profile['synchronyCreditCard'] = $customerFinanceInfo['SYF']['HAS_ACCT'];
            $profile['PreScreenSynchronyCard'] = $customerFinanceInfo['SYF']['HAS_ACCT'];
            $profile['AFFCard'] = $customerFinanceInfo['AFF']['HAS_ACCT'];
            $profile['genesisCard'] = $customerFinanceInfo['GENE']['HAS_ACCT'];
            $profile['LastOrderDate'] = date_format(new DateTime('yesterday'),'n/j/Y');
            $profile['LastOrderTotal'] = '$' . number_format($salesOrderLinesTotal + $data->get_TAX_CHG() + $data->get_SETUP_CHG(), 2);

            //Getting information on total finalized orders for customer
            $profile['TotalOrders'] = $custTotal['count'];
            $profile['TotalRevenue'] = $custTotal['total'];
            $profile['AverageOrderValue'] = $custTotal['total'] != 0 ? number_format($custTotal['total'] / $custTotal['count'], 2) : $custTotal['total'];

            //Checking condition variables; omitting profile if no email is set, email address is set to unsubscribe, or salesperson has been terminated
            if ( $profile['email'] !== 'NSUBSCRIB' && is_null($data->get_TERMDATE()) ) {
                //Adding single customer array to profiles data array
                array_push($profiles, $profile);

                //Creating instance array for invoice to query for SKU data for orders data array; del doc num will be key and array of useful data will be value
                $invoice['orderDate'] = date_format(new DateTime($data->get_SO_WR_DT()), "n/j/Y g:i");
                $invoice['orderType'] = $data->get_STAT_CD() == 'O' ? 'OPEN' : 'FINALIZED';
                $invoice['email'] = $data->get_EMAIL_ADDR();
                $invoice['subtotal'] = '$' . number_format($salesOrderLinesTotal, 2);
                $invoice['taxChg'] = $data->get_TAX_CHG();
                $invoice['setupChg'] = $data->get_SETUP_CHG();

                $puDelDt = date_create( $data->get_PU_DEL_DT() );
                $invoice['finalShipDate'] = date_format($puDelDt,"n/j/Y g:i");
                $invoices[$data->get_DEL_DOC_NUM()] = $invoice;
            }
        }

        return array( 'profiles' => $profiles, 'invoices' => $invoices );

    }

    function processCustomerProspects( $db, $fromDate, $toDate ){
        global $appconfig, $logger;
        $prospects = new CustomerProspects($db);
        $where = "WHERE CREATED_AT > '" . $fromDate . "' AND CREATED_AT <= '" . $toDate . "' ";

        $result = $prospects->query($where);
        if ( $result < 0 ){
           $logger->debug( "ERROR querying table cust_prospect" ); 
           exit(1);
        }
        $customers = array();
        while( $prospects->next() ){
            $tmp = [
                'firstName' => $prospects->get_FNAME(),
                'lastName' => $prospects->get_LNAME(),
                'email' => $prospects->get_EMAIL(),
                'phone_number' => $prospects->get_PHONE(),
                'lastLoggedSalesPersonId' => $prospects->get_EMP_CD(),
            ];
            
            array_push( $customers, $tmp );
        }

        return $customers;


    }
    


?>
