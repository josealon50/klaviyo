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
    $profilesTotal = array();
    $orders = array();
    $ordersTotal = array();
    $invoices = array();

    function upload( $remotepath, $localpath, $filename ){
        $sftp = new Net_SFTP($appconfig['sftp']['host']);
        if (!$sftp->login($appconfig['sftp']['username'], $appconfig['sftp']['pw'])) {
            //Log error statement
            $logger->error("SFTP connection failed");
            exit(1);
        }
        else{
            $upload = $sftp->put($remotePath . $filename, $localpath . $filename, NET_SFTP_LOCAL_FILE);
        }
    }

    //Function to generate CSV for uploaded data
    function generateCSV( $data, $type, $outputPath ) {
        //Generating timestamp CSV file name
        $name = sprintf( $appconfig['klaviyo']['filename'], $type, date("YmdHis") ); 
        $file = fopen( $name, "w" );
        foreach( $data as $line ) {
            fputcsv( $file, array_values($line) );
        }
        fclose($file);
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
    
    $result = $tabSO->query($where);

    //Initializing arrays to be populated
    $profiles = array();
    $profilesTotal = array();
    $orders = array();
    $ordersTotal = array();
    $invoices = array();

    //Iterating through result set of SQL query on SO for finalized orders for date or date range
    while ($tabSO->next()) {
        //Creating instance arrays for single customer and invoice
        $profile = array();
        $invoice = array();

        //Populating local variables with SO data for later reference
        $delDocNum = $tabSO->get_DEL_DOC_NUM();
        echo "Found invoice $delDocNum\n";
        $custCd = $tabSO->get_CUST_CD();
        $taxChg = $tabSO->get_TAX_CHG();
        $setupChg = $tabSO->get_SETUP_CHG();
        $orderDt = date_create($tabSO->get_SO_WR_DT());

        //Querying for customer information (email and name)
        $tabCust = new Cust($db);
        $where = "WHERE CUST_CD = '$custCd'";
        $resultCust = $tabCust->query($where);
        $email = "";
        $fname = "";
        $lname = "";
        if ($tabCust->next()) {
            $email = $tabCust->get_EMAIL_ADDR();
            $fname = $tabCust->get_FNAME();
            $lname = $tabCust->get_LNAME();
        }

        //Querying for individual SKUs on single invoice
        $tabSOLn = new SOLine($db);
        $where = "WHERE DEL_DOC_NUM = '$delDocNum'";
        $resultSOLn = $tabSOLn->query($where);

        //Initializing local variables for SO_LN data for later reference
        $subtotal = 0;
        $safeguard = "N";

        //Iterating through result set of SQL query on SO_LN for individual SKUs for single invoice
        while ($tabSOLn->next()) {
            //Adding item's unit price to subtotal
            $subtotal += $tabSOLn->get_UNIT_PRC() * $tabSOLn->get_QTY();
        }

        //Querying for financing information for customer (open to buy amount and LOC with certain finance companies)
        $tabAsp = new CustAsp($db);
        $where = "WHERE CUST_CD = '$custCd'";
        $result = $tabAsp->query($where);

        //Initializing data and condition variables for financing data
        $openToBuy = 0;
        $hasSyf = "N";
        $hasAFF = "N";
        $hasGen = "N";

        //Iterating through result set of SQL query on CUST_ASP for customer finance information
        while ($tabAsp->next()) {
            //Setting open to buy amt to nonzero value on available app credit
            if ($tabAsp->get_APP_CREDIT_AVAIL() > 0) {
                $openToBuy = $tabAsp->get_APP_CREDIT_AVAIL();
            }

            //Setting condition variables for having LOC with certain finance companies
            if ($tabAsp->get_AS_CD() == "SYF") {
                $hasSyf = "Y";
            }
            if ($tabAsp->get_AS_CD() == "AFF") {
                $hasAFF = "Y";
            }
            if ($tabAsp->get_AS_CD() == "GENE") {
                $hasGen = "Y";
            }
        }
        
        //Populating instance array for single customer
        $profile['email'] = $email;
        $profile['firstName'] = $fname;
        $profile['lastName'] = $lname;
        $profile['openToBuyAmount'] = $openToBuy;
        $profile['emailOptIn'] = "TRUE";
        $profile['SMSOptIn'] = $tabSO->get_USR_FLD_2();
        $profile['address'] = $tabSO->get_SHIP_TO_ADDR1();
        $profile['address2'] = $tabSO->get_SHIP_TO_ADDR2();
        $profile['city'] = $tabSO->get_SHIP_TO_CITY();
        $profile['state'] = $tabSO->get_SHIP_TO_ST_CD();
        $profile['zip'] = $tabSO->get_SHIP_TO_ZIP_CD();
        $profile['phone_number'] = "1".str_replace("-","",$tabSO->get_SHIP_TO_H_PHONE());
        $profile['secondaryPhone'] = "1".str_replace("-","",$tabSO->get_SHIP_TO_B_PHONE());
        $profile['lastStorePurchasedFrom'] = $tabSO->get_SO_STORE_CD();
        $profile['lastLoggedSalespersonId'] = $tabSO->get_SO_EMP_SLSP_CD1();
        $profile['lastLoggedSalespersonDate'] = date_format($orderDt,"n/j/Y");
        $profile['source'] = "buyer";
        $profile['synchronyCreditCard'] = $hasSyf;
        $profile['PreScreenSynchronyCard'] = $hasSyf;
        $profile['AFFCard'] = $hasAFF;
        $profile['genesisCard'] = $hasGen;
        $profile['LastOrderDate'] = date_format($dateEnd,'n/j/Y');
        $profile['LastOrderTotal'] = $subtotal + $taxChg + $setupChg;

        //Getting information on total finalized orders for customer
        $custTotal = getCustOrders($db,$custCd);
        $profile['TotalOrders'] = $custTotal['count'];
        $profile['TotalRevenue'] = $custTotal['total'];
        $profile['AverageOrderValue'] = number_format($custTotal['total']/$custTotal['count'],2);

        //Setting condition variables for set email, subscription status, and employed salesperson
        $emailSet = isset($profile['email']);
        $subscribed = true;
        if (strpos(strtoupper($email), "NSUBSCRIB") !== false) {
            $subscribed = false;
        }
        $slspHired = true;
        $tabEmp = new Emp($db);
        $where = "WHERE EMP_CD = '".$tabSO->get_SO_EMP_SLSP_CD1()."' AND TERMDATE IS NULL";
        $result = $tabEmp->query($where);
        if (!$tabEmp->next()) {
            $slspHired = false;
        }

        //Checking condition variables; omitting profile if no email is set, email address is set to unsubscribe, or salesperson has been terminated
        if ($emailSet && $subscribed && $slspHired) {

            //Adding single customer array to profiles data array
            echo "Added $delDocNum to contacts list\n";
            array_push($profiles, $profile);
            if (sizeof($profiles) == 100) {
                array_push($profilesTotal, $profiles);
                $profiles = array();
            }

            //Creating instance array for invoice to query for SKU data for orders data array; del doc num will be key and array of useful data will be value
            $invoice['orderDate'] = date_format($orderDt,"n/j/Y g:i");
            $invoice['email'] = $email;
            $invoice['subtotal'] = $subtotal;
            $invoice['taxChg'] = $taxChg;
            $invoice['setupChg'] = $setupChg;
            $puDelDt = date_create($tabSO->get_PU_DEL_DT());
            $invoice['finalShipDate'] = date_format($puDelDt,"n/j/Y g:i");
            $invoices[$delDocNum] = $invoice;
        }
    }
    array_push($profilesTotal,$profiles);

    $url = "https://a.klaviyo.com/api/v2/list/".$klaviyo['master_list']."/members";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Performing cURL HTTP POST on profiles and orders JSON objects and echoing return JSON objects
    $profilesOutput = array();
    foreach ($profilesTotal as $profiles) {
        $contacts = array();
        $contacts['api_key'] = $apiKey;
        $contacts['profiles'] = $profiles;
        foreach ($profiles as $profile) {
            array_push($profilesOutput, $profile);
        }
        $contactsData = json_encode($contacts);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $contactsData);
        $result = curl_exec($ch);
        $data = json_decode($result);
        echo var_dump($data);
    }
    generateCSV($profilesOutput,"contacts",$outputPath,$sftp);

    //Iterating through invoices from previous query
    foreach ($invoices as $invoice=>$details) {

        //Initializing instance array for individual SKU
        $order = array();

        //Querying for individual SKUs on single invoice
        $tabSOLn = new SOLine($db);
        $where = "WHERE DEL_DOC_NUM = '".$invoice."'";
        $result = $tabSOLn->query($where);

        //Iterating through result set of SQL query on SO_LN for individual SKUs for single invoice
        while ($tabSOLn->next()) {

            //Populating instance array with information from SKU's useful data array as well as result set from SO_LN query
            $order['orderNumber'] = $invoice;
            $order['orderDate'] = $details['orderDate'];
            $order['orderStatus'] = 'PROCESSED';
            $order['email'] = $details['email'];
            $order['orderTotal'] = $details['subtotal'] + $details['taxChg'] + $details['setupChg'];
            $order['orderSubtotal'] = $details['subtotal'];
            $order['taxAmt'] = $details['taxChg'];
            $order['shipAmt'] = '0';
            $order['SKU'] = $tabSOLn->get_ITM_CD();
            $order['qty'] = $tabSOLn->get_QTY();
            $order['unitPrice'] = $tabSOLn->get_UNIT_PRC();
            $order['totalPrice'] = $tabSOLn->get_UNIT_PRC() * $tabSOLn->get_QTY();
            $order['deliveryType'] = $tabSO->get_PU_DEL();
            $order['finalShipDate'] = $details['finalShipDate'];

            //Querying for whether an individual SKU is a safeguard item and setting safeguard to Y if so
            $itmCd = $tabSOLn->get_ITM_CD();
            $safeguard = 'N';
            $tabItm = new Item($db);
            $where = "WHERE ITM_CD = '$itmCd'";
            $resultItm = $tabItm->query($where);
            if ($tabItm->next()) {
                if ($tabItm->get_VE_CD() == "SAFE") {
                    $safeguard = 'Y';
                }
            }
            $order['safeguard'] = $safeguard;

            //Initializing and evaluating condition variables for void flag and quantity
            $notVoid = true;
            if ($tabSOLn->get_VOID_FLAG() == "Y") {
                $notVoid = false;
            }
            $nonzeroQty = true;
            if ($tabSOLn->get_QTY() == "0") {
                $nonzeroQty = false;
            }

            //Adding single SKU array to orders data array if void flag is not set to 'Y' and quantity is greater than zero
            if ($notVoid && $nonzeroQty){
                $event = array();
                $event['token'] = $apiPublic;
                $event['event'] = "Ordered Product";
                $event['customer_properties'] = array('$email'=>$details['email']);
                $event['properties'] = $order;
                $eventData = json_encode($event, JSON_UNESCAPED_SLASHES);
                $eventData = base64_encode($eventData);
                $url = "https://a.klaviyo.com/api/track?data=".$eventData;
                $result = file_get_contents($url);

                array_push($orders,$order);
                if (sizeof($orders) == 100) {
                    array_push($ordersTotal,$orders);
                    $orders = array();
                }
            }
        }
    }
    array_push($ordersTotal,$orders);

    $ordersOutput = array();
    foreach ($ordersTotal as $orders) {
        $products = array();
        $products['api_key'] = $apiKey;
        $products['profiles'] = $orders;
        foreach ($orders as $order) {
            array_push($ordersOutput,$order);
        }
    }
    generateCSV($ordersOutput,"orders",$outputPath,$sftp);

    //Exiting program
    curl_close($ch);
    return;


?>
