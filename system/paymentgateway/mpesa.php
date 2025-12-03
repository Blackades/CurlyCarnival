<?php

/**
 * PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *
 * Payment Gateway M-Pesa STK Push (Lipa Na M-Pesa Online)
 * 
 * M-Pesa is a mobile money service widely used in Kenya and other African countries.
 * This integration uses Safaricom's Daraja API for STK Push functionality.
 *
 * @link https://developer.safaricom.co.ke/
 **/

/**
 * Validate M-Pesa configuration
 * 
 * Checks if all required M-Pesa credentials are configured before allowing transactions.
 * Sends Telegram notification and redirects with error if configuration is incomplete.
 * 
 * @return void
 */
function mpesa_validate_config()
{
    global $config;
    
    if (empty($config['mpesa_consumer_key']) || 
        empty($config['mpesa_consumer_secret']) || 
        empty($config['mpesa_shortcode']) || 
        empty($config['mpesa_passkey'])) {
        sendTelegram("M-Pesa payment gateway not configured");
        r2(U . 'order/package', 'w', "Admin has not yet setup M-Pesa payment gateway, please tell admin");
    }
}

/**
 * Display M-Pesa configuration form
 * 
 * Shows the admin interface for configuring M-Pesa credentials and settings.
 * Displays callback URL that must be registered with Safaricom.
 * 
 * @return void
 */
function mpesa_show_config()
{
    global $ui, $config;
    
    $ui->assign('_title', 'M-Pesa - Payment Gateway');
    $ui->assign('_c', $config);
    $ui->assign('callback_url', U . 'callback/mpesa');
    $ui->display('mpesa.tpl');
}

/**
 * Save M-Pesa configuration
 * 
 * Saves M-Pesa credentials and settings to the database.
 * Updates existing records or creates new ones as needed.
 * 
 * @return void
 */
function mpesa_save_config()
{
    global $admin;
    
    $mpesa_consumer_key = _post('mpesa_consumer_key');
    $mpesa_consumer_secret = _post('mpesa_consumer_secret');
    $mpesa_shortcode = _post('mpesa_shortcode');
    $mpesa_passkey = _post('mpesa_passkey');
    $mpesa_environment = _post('mpesa_environment');
    
    $settings = [
        'mpesa_consumer_key' => $mpesa_consumer_key,
        'mpesa_consumer_secret' => $mpesa_consumer_secret,
        'mpesa_shortcode' => $mpesa_shortcode,
        'mpesa_passkey' => $mpesa_passkey,
        'mpesa_environment' => $mpesa_environment
    ];
    
    foreach ($settings as $key => $value) {
        $d = ORM::for_table('tbl_appconfig')->where('setting', $key)->find_one();
        if ($d) {
            $d->value = $value;
            $d->save();
        } else {
            $d = ORM::for_table('tbl_appconfig')->create();
            $d->setting = $key;
            $d->value = $value;
            $d->save();
        }
    }
    
    _log('[' . $admin['username'] . ']: M-Pesa Settings Saved Successfully', 'Admin', $admin['id']);
    r2(U . 'paymentgateway/mpesa', 's', 'Settings Saved Successfully');
}

/**
 * Create M-Pesa transaction and initiate STK Push
 * 
 * Initiates an STK Push payment request to the customer's phone.
 * Validates phone number, obtains OAuth token, generates transaction password,
 * and sends payment prompt to customer's mobile device.
 * 
 * @param array $trx Transaction record from tbl_payment_gateway
 * @param array $user Customer user record
 * @return void
 */
function mpesa_create_transaction($trx, $user)
{
    global $config;
    
    try {
        // Step 1: Validate and format customer phone number
        $phone = $user['phonenumber'];
        
        if (empty($phone)) {
            r2(U . 'order/package', 'e', 'Phone number is required for M-Pesa payments. Please update your profile with a valid phone number.');
            return;
        }
        
        try {
            $formatted_phone = mpesa_format_phone_number($phone);
        } catch (Exception $e) {
            _log('M-Pesa Phone Format Error for user ' . $user['username'] . ': ' . $e->getMessage(), 'M-Pesa');
            r2(U . 'order/package', 'e', $e->getMessage());
            return;
        }
        
        // Step 2: Get OAuth access token
        try {
            $access_token = mpesa_get_access_token();
        } catch (Exception $e) {
            _log('M-Pesa OAuth Error for transaction ' . $trx['id'] . ': ' . $e->getMessage(), 'M-Pesa');
            sendTelegram("M-Pesa OAuth failed for transaction {$trx['id']}: " . $e->getMessage());
            r2(U . 'order/package', 'e', 'Payment service temporarily unavailable. Please try again later or contact support.');
            return;
        }
        
        // Step 3: Generate current timestamp in YYYYMMDDHHmmss format
        $timestamp = date('YmdHis');
        
        // Step 4: Generate transaction password
        $shortcode = $config['mpesa_shortcode'];
        $passkey = $config['mpesa_passkey'];
        $password = mpesa_generate_password($shortcode, $passkey, $timestamp);
        
        // Step 5: Prepare STK Push request JSON payload
        $callback_url = U . 'callback/mpesa';
        $amount = (int)$trx['price']; // M-Pesa expects integer amount without decimals
        
        $request_data = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $formatted_phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $formatted_phone,
            'CallBackURL' => $callback_url,
            'AccountReference' => $user['username'],
            'TransactionDesc' => $trx['plan_name']
        ];
        
        // Step 6: Send POST request to STK Push endpoint
        $base_url = mpesa_get_base_url();
        $stk_push_url = $base_url . '/mpesa/stkpush/v1/processrequest';
        
        try {
            $response = Http::postJsonData($stk_push_url, $request_data, [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            ]);
            
            // Parse response
            $result = json_decode($response, true);
            
            // Log the request and response for debugging
            _log('M-Pesa STK Push Request for transaction ' . $trx['id'] . ': ' . json_encode($request_data), 'M-Pesa');
            _log('M-Pesa STK Push Response for transaction ' . $trx['id'] . ': ' . $response, 'M-Pesa');
            
            // Step 7: Check if STK Push was successful
            if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                // Extract CheckoutRequestID and MerchantRequestID
                $checkout_request_id = $result['CheckoutRequestID'];
                $merchant_request_id = isset($result['MerchantRequestID']) ? $result['MerchantRequestID'] : '';
                
                // Step 8: Update transaction record
                $d = ORM::for_table('tbl_payment_gateway')->find_one($trx['id']);
                if ($d) {
                    $d->gateway_trx_id = $checkout_request_id;
                    $d->pg_request = json_encode($request_data);
                    $d->expired_date = date('Y-m-d H:i:s', strtotime('+2 minutes')); // STK Push expires in 2 minutes
                    $d->save();
                }
                
                // Log success
                _log('M-Pesa STK Push initiated successfully for transaction ' . $trx['id'] . ' - CheckoutRequestID: ' . $checkout_request_id, 'M-Pesa');
                
                // Step 9: Redirect to transaction view page
                r2(U . 'order/view/' . $trx['id'], 's', 'Payment request sent to your phone. Please enter your M-Pesa PIN to complete the payment.');
                
            } else {
                // STK Push failed
                $error_code = isset($result['errorCode']) ? $result['errorCode'] : 'Unknown';
                $error_message = isset($result['errorMessage']) ? $result['errorMessage'] : 'Unknown error';
                
                _log('M-Pesa STK Push Failed for transaction ' . $trx['id'] . ' - Code: ' . $error_code . ', Message: ' . $error_message, 'M-Pesa');
                sendTelegram("M-Pesa STK Push failed for transaction {$trx['id']}: {$error_code} - {$error_message}");
                
                // Provide user-friendly error messages
                if (strpos(strtolower($error_message), 'invalid') !== false && strpos(strtolower($error_message), 'phone') !== false) {
                    r2(U . 'order/package', 'e', 'Invalid phone number for M-Pesa. Please update your profile with a valid Kenyan phone number.');
                } else {
                    r2(U . 'order/package', 'e', 'Payment request failed: ' . $error_message . '. Please try again or contact support.');
                }
            }
            
        } catch (Exception $e) {
            // Handle network errors and timeouts
            _log('M-Pesa STK Push Exception for transaction ' . $trx['id'] . ': ' . $e->getMessage(), 'M-Pesa');
            sendTelegram("M-Pesa STK Push exception for transaction {$trx['id']}: " . $e->getMessage());
            
            // Check if it's a timeout error
            if (strpos(strtolower($e->getMessage()), 'timeout') !== false) {
                r2(U . 'order/package', 'e', 'Request timeout. Please check your internet connection and try again.');
            } else {
                r2(U . 'order/package', 'e', 'Payment service error. Please try again later or contact support.');
            }
        }
        
    } catch (Exception $e) {
        // Catch any unexpected errors
        _log('M-Pesa Unexpected Error for transaction ' . $trx['id'] . ': ' . $e->getMessage(), 'M-Pesa');
        sendTelegram("M-Pesa unexpected error for transaction {$trx['id']}: " . $e->getMessage());
        r2(U . 'order/package', 'e', 'An unexpected error occurred. Please try again or contact support.');
    }
}

/**
 * Handle M-Pesa payment notification callback
 * 
 * Processes payment status notifications from M-Pesa after customer completes or cancels payment.
 * Validates callback payload, updates transaction status, and activates customer package on success.
 * 
 * @return void
 */
function mpesa_payment_notification()
{
    // Step 1: Validate request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die('Method Not Allowed');
    }
    
    // Step 2: Read raw POST data (php://input)
    $raw_data = file_get_contents('php://input');
    
    // Step 3: Log callback data to pages/mpesa-webhook.html with timestamp
    $log_file = 'pages/mpesa-webhook.html';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "\n\n=== M-Pesa Callback - {$timestamp} ===\n{$raw_data}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    // Step 4: Parse JSON payload and extract stkCallback data
    $callback_data = json_decode($raw_data, true);
    
    if (!$callback_data || !isset($callback_data['Body']['stkCallback'])) {
        _log('M-Pesa Callback: Invalid payload structure', 'M-Pesa');
        http_response_code(400);
        die('Invalid payload');
    }
    
    $stk_callback = $callback_data['Body']['stkCallback'];
    
    // Step 5: Extract ResultCode, ResultDesc, CheckoutRequestID, MerchantRequestID
    $result_code = isset($stk_callback['ResultCode']) ? $stk_callback['ResultCode'] : null;
    $result_desc = isset($stk_callback['ResultDesc']) ? $stk_callback['ResultDesc'] : '';
    $checkout_request_id = isset($stk_callback['CheckoutRequestID']) ? $stk_callback['CheckoutRequestID'] : '';
    $merchant_request_id = isset($stk_callback['MerchantRequestID']) ? $stk_callback['MerchantRequestID'] : '';
    
    // Step 6: Find transaction by CheckoutRequestID in tbl_payment_gateway
    $trx = ORM::for_table('tbl_payment_gateway')
        ->where('gateway_trx_id', $checkout_request_id)
        ->find_one();
    
    // Step 7: Check if transaction exists, if not log and exit with 404
    if (!$trx) {
        _log('M-Pesa Callback: Transaction not found for CheckoutRequestID: ' . $checkout_request_id, 'M-Pesa');
        http_response_code(404);
        die('Transaction not found');
    }
    
    // Step 8: Check if transaction already processed (status 2, 3, or 4), if yes exit
    if (in_array($trx['status'], [2, 3, 4])) {
        _log('M-Pesa Callback: Transaction ' . $trx['id'] . ' already processed with status ' . $trx['status'], 'M-Pesa');
        http_response_code(200);
        die('Already processed');
    }
    
    // Process based on ResultCode
    if ($result_code == 0) {
        // SUCCESS: Process successful payment (subtask 6.2)
        mpesa_process_successful_payment($trx, $stk_callback);
    } else {
        // FAILURE/CANCELLED: Process failed payment (subtask 6.3)
        mpesa_process_failed_payment($trx, $stk_callback, $result_code, $result_desc);
    }
    
    // Return HTTP 200 OK to M-Pesa
    http_response_code(200);
    die('OK');
}

/**
 * Process successful M-Pesa payment
 * 
 * Handles successful payment callback by extracting payment details,
 * activating customer package, and updating transaction status.
 * 
 * @param object $trx Transaction record from database
 * @param array $stk_callback STK callback data from M-Pesa
 * @return void
 */
function mpesa_process_successful_payment($trx, $stk_callback)
{
    // Step 1: Parse CallbackMetadata using mpesa_parse_callback_metadata()
    $callback_metadata = isset($stk_callback['CallbackMetadata']['Item']) ? $stk_callback['CallbackMetadata']['Item'] : [];
    $payment_details = mpesa_parse_callback_metadata($callback_metadata);
    
    // Step 2: Extract Amount, MpesaReceiptNumber, TransactionDate, PhoneNumber
    $amount = $payment_details['Amount'];
    $mpesa_receipt = $payment_details['MpesaReceiptNumber'];
    $transaction_date = $payment_details['TransactionDate'];
    $phone_number = $payment_details['PhoneNumber'];
    
    // Convert M-Pesa date format (YYYYMMDDHHmmss) to MySQL datetime
    if ($transaction_date) {
        $paid_date = date('Y-m-d H:i:s', strtotime($transaction_date));
    } else {
        $paid_date = date('Y-m-d H:i:s');
    }
    
    // Step 3: Call Package::rechargeUser() to activate customer package
    try {
        Package::rechargeUser(
            $trx['user_id'],
            $trx['routers'],
            $trx['plan_id'],
            'M-Pesa',
            $mpesa_receipt
        );
        
        // Step 4: Update transaction: status=2, pg_paid_response, payment_method='M-PESA', payment_channel='mpesa_stk', paid_date
        $trx->pg_paid_response = json_encode($stk_callback);
        $trx->payment_method = 'M-PESA';
        $trx->payment_channel = 'mpesa_stk';
        $trx->paid_date = $paid_date;
        $trx->status = 2; // Paid
        $trx->save();
        
        // Step 5: Send success Telegram notification with payment details
        $message = "✅ M-Pesa Payment Successful\n\n";
        $message .= "Transaction ID: {$trx['id']}\n";
        $message .= "Customer: {$trx['username']}\n";
        $message .= "Plan: {$trx['plan_name']}\n";
        $message .= "Amount: KES {$amount}\n";
        $message .= "M-Pesa Receipt: {$mpesa_receipt}\n";
        $message .= "Phone: {$phone_number}\n";
        $message .= "Date: {$paid_date}";
        
        sendTelegram($message);
        
        // Step 6: Log successful payment
        _log('M-Pesa Payment Success - Transaction: ' . $trx['id'] . ', Receipt: ' . $mpesa_receipt . ', Amount: ' . $amount, 'M-Pesa');
        
    } catch (Exception $e) {
        // Handle package activation failure
        _log('M-Pesa Package Activation Failed for transaction ' . $trx['id'] . ': ' . $e->getMessage(), 'M-Pesa');
        
        // Still update transaction as paid for manual review
        $trx->pg_paid_response = json_encode($stk_callback);
        $trx->payment_method = 'M-PESA';
        $trx->payment_channel = 'mpesa_stk';
        $trx->paid_date = $paid_date;
        $trx->status = 2; // Paid
        $trx->save();
        
        // Send Telegram notification about activation failure
        $message = "⚠️ M-Pesa Payment Received but Package Activation Failed\n\n";
        $message .= "Transaction ID: {$trx['id']}\n";
        $message .= "Customer: {$trx['username']}\n";
        $message .= "Amount: KES {$amount}\n";
        $message .= "M-Pesa Receipt: {$mpesa_receipt}\n";
        $message .= "Error: {$e->getMessage()}\n\n";
        $message .= "Please activate package manually.";
        
        sendTelegram($message);
    }
}

/**
 * Process failed or cancelled M-Pesa payment
 * 
 * Handles failed or cancelled payment callback by updating transaction status
 * and sending notification to admin.
 * 
 * @param object $trx Transaction record from database
 * @param array $stk_callback STK callback data from M-Pesa
 * @param int $result_code M-Pesa result code
 * @param string $result_desc M-Pesa result description
 * @return void
 */
function mpesa_process_failed_payment($trx, $stk_callback, $result_code, $result_desc)
{
    // Step 1: Update transaction: status=4, pg_paid_response with full callback data
    $trx->pg_paid_response = json_encode($stk_callback);
    $trx->status = 4; // Failed
    $trx->save();
    
    // Step 2: Send failure Telegram notification with error details
    $message = "❌ M-Pesa Payment Failed\n\n";
    $message .= "Transaction ID: {$trx['id']}\n";
    $message .= "Customer: {$trx['username']}\n";
    $message .= "Plan: {$trx['plan_name']}\n";
    $message .= "Amount: KES {$trx['price']}\n";
    $message .= "Result Code: {$result_code}\n";
    $message .= "Reason: {$result_desc}";
    
    sendTelegram($message);
    
    // Step 3: Log failure with ResultDesc
    _log('M-Pesa Payment Failed - Transaction: ' . $trx['id'] . ', Code: ' . $result_code . ', Reason: ' . $result_desc, 'M-Pesa');
}

/**
 * Query M-Pesa transaction status
 * 
 * Manually checks the status of a pending transaction with M-Pesa.
 * Used when customer wants to verify payment status or when callback is delayed.
 * 
 * @param array $trx Transaction record from tbl_payment_gateway
 * @param array $user Customer user record
 * @return void
 */
function mpesa_get_status($trx, $user)
{
    global $config;
    
    try {
        // Step 1: Get OAuth access token
        try {
            $access_token = mpesa_get_access_token();
        } catch (Exception $e) {
            _log('M-Pesa Status Query OAuth Error for transaction ' . $trx['id'] . ': ' . $e->getMessage(), 'M-Pesa');
            sendTelegram("M-Pesa status query OAuth failed for transaction {$trx['id']}: " . $e->getMessage());
            r2(U . 'order/view/' . $trx['id'], 'e', 'Payment service temporarily unavailable. Please try again later.');
            return;
        }
        
        // Step 2: Generate current timestamp in YYYYMMDDHHmmss format
        $timestamp = date('YmdHis');
        
        // Step 3: Generate transaction password
        $shortcode = $config['mpesa_shortcode'];
        $passkey = $config['mpesa_passkey'];
        $password = mpesa_generate_password($shortcode, $passkey, $timestamp);
        
        // Step 4: Prepare status query JSON payload with CheckoutRequestID
        $request_data = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $trx['gateway_trx_id']
        ];
        
        // Step 5: Send POST request to status query endpoint
        $base_url = mpesa_get_base_url();
        $query_url = $base_url . '/mpesa/stkpushquery/v1/query';
        
        try {
            $response = Http::postJsonData($query_url, $request_data, [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            ]);
            
            // Step 6: Parse response and extract ResultCode, ResultDesc, ResponseCode
            $result = json_decode($response, true);
            
            // Log the request and response for debugging
            _log('M-Pesa Status Query Request for transaction ' . $trx['id'] . ': ' . json_encode($request_data), 'M-Pesa');
            _log('M-Pesa Status Query Response for transaction ' . $trx['id'] . ': ' . $response, 'M-Pesa');
            
            // Extract result details
            $result_code = isset($result['ResultCode']) ? $result['ResultCode'] : null;
            $result_desc = isset($result['ResultDesc']) ? $result['ResultDesc'] : '';
            $response_code = isset($result['ResponseCode']) ? $result['ResponseCode'] : null;
            
            // Step 7: Process based on ResultCode
            if ($result_code === null) {
                // Invalid response format
                _log('M-Pesa Status Query: Invalid response format for transaction ' . $trx['id'], 'M-Pesa');
                r2(U . 'order/view/' . $trx['id'], 'e', 'Unable to check payment status. Please try again later.');
                return;
            }
            
            // Convert result code to string for comparison
            $result_code_str = (string)$result_code;
            
            if ($result_code_str == '0') {
                // SUCCESS: Payment was successful
                mpesa_process_status_success($trx, $user, $result);
                
            } elseif ($result_code_str == '1032') {
                // CANCELLED: User cancelled the payment
                mpesa_process_status_cancelled($trx);
                
            } elseif ($result_code_str == '1037') {
                // TIMEOUT: STK Push request timed out
                mpesa_process_status_timeout($trx);
                
            } elseif ($result_code_str == '1') {
                // INSUFFICIENT BALANCE: User has insufficient funds
                mpesa_process_status_insufficient_balance($trx);
                
            } else {
                // UNKNOWN STATUS: Log and notify admin
                mpesa_process_status_unknown($trx, $result_code, $result_desc, $result);
            }
            
        } catch (Exception $e) {
            // Handle network errors and timeouts
            _log('M-Pesa Status Query Exception for transaction ' . $trx['id'] . ': ' . $e->getMessage(), 'M-Pesa');
            
            if (strpos(strtolower($e->getMessage()), 'timeout') !== false) {
                r2(U . 'order/view/' . $trx['id'], 'e', 'Request timeout. Please check your internet connection and try again.');
            } else {
                r2(U . 'order/view/' . $trx['id'], 'e', 'Unable to check payment status. Please try again later.');
            }
        }
        
    } catch (Exception $e) {
        // Catch any unexpected errors
        _log('M-Pesa Status Query Unexpected Error for transaction ' . $trx['id'] . ': ' . $e->getMessage(), 'M-Pesa');
        r2(U . 'order/view/' . $trx['id'], 'e', 'An unexpected error occurred. Please try again or contact support.');
    }
}

/**
 * Process successful payment status from status query
 * 
 * Handles successful payment confirmation from status query.
 * Activates package if not already activated and updates transaction status.
 * 
 * @param object $trx Transaction record from database
 * @param array $user Customer user record
 * @param array $result Status query response from M-Pesa
 * @return void
 */
function mpesa_process_status_success($trx, $user, $result)
{
    // Check if transaction is already paid
    if ($trx['status'] == 2) {
        _log('M-Pesa Status Query: Transaction ' . $trx['id'] . ' already marked as paid', 'M-Pesa');
        r2(U . 'order/view/' . $trx['id'], 's', 'Payment confirmed! Your package is already active.');
        return;
    }
    
    // Extract payment receipt if available
    $mpesa_receipt = isset($result['MpesaReceiptNumber']) ? $result['MpesaReceiptNumber'] : 'N/A';
    
    try {
        // Activate package if not already activated
        Package::rechargeUser(
            $trx['user_id'],
            $trx['routers'],
            $trx['plan_id'],
            'M-PESA',
            $mpesa_receipt
        );
        
        // Update transaction status to paid
        $d = ORM::for_table('tbl_payment_gateway')->find_one($trx['id']);
        if ($d) {
            $d->pg_paid_response = json_encode($result);
            $d->payment_method = 'M-PESA';
            $d->payment_channel = 'mpesa_stk';
            $d->paid_date = date('Y-m-d H:i:s');
            $d->status = 2; // Paid
            $d->save();
        }
        
        // Send success notification
        $message = "✅ M-Pesa Payment Confirmed (Status Query)\n\n";
        $message .= "Transaction ID: {$trx['id']}\n";
        $message .= "Customer: {$trx['username']}\n";
        $message .= "Plan: {$trx['plan_name']}\n";
        $message .= "Amount: KES {$trx['price']}\n";
        $message .= "M-Pesa Receipt: {$mpesa_receipt}";
        
        sendTelegram($message);
        
        // Log success
        _log('M-Pesa Status Query Success - Transaction: ' . $trx['id'] . ' confirmed and activated', 'M-Pesa');
        
        // Redirect with success message
        r2(U . 'order/view/' . $trx['id'], 's', 'Payment confirmed successfully! Your package has been activated.');
        
    } catch (Exception $e) {
        // Handle package activation failure
        _log('M-Pesa Status Query: Package activation failed for transaction ' . $trx['id'] . ': ' . $e->getMessage(), 'M-Pesa');
        
        // Still update transaction as paid for manual review
        $d = ORM::for_table('tbl_payment_gateway')->find_one($trx['id']);
        if ($d) {
            $d->pg_paid_response = json_encode($result);
            $d->payment_method = 'M-PESA';
            $d->payment_channel = 'mpesa_stk';
            $d->paid_date = date('Y-m-d H:i:s');
            $d->status = 2; // Paid
            $d->save();
        }
        
        // Send notification about activation failure
        $message = "⚠️ M-Pesa Payment Confirmed but Package Activation Failed (Status Query)\n\n";
        $message .= "Transaction ID: {$trx['id']}\n";
        $message .= "Customer: {$trx['username']}\n";
        $message .= "Amount: KES {$trx['price']}\n";
        $message .= "M-Pesa Receipt: {$mpesa_receipt}\n";
        $message .= "Error: {$e->getMessage()}\n\n";
        $message .= "Please activate package manually.";
        
        sendTelegram($message);
        
        r2(U . 'order/view/' . $trx['id'], 'w', 'Payment confirmed but package activation failed. Please contact support.');
    }
}

/**
 * Process cancelled payment status from status query
 * 
 * Handles cancelled payment confirmation from status query.
 * Updates transaction status to failed.
 * 
 * @param object $trx Transaction record from database
 * @return void
 */
function mpesa_process_status_cancelled($trx)
{
    // Update transaction status to failed
    $d = ORM::for_table('tbl_payment_gateway')->find_one($trx['id']);
    if ($d && $d['status'] != 2) { // Don't update if already paid
        $d->status = 4; // Failed
        $d->save();
    }
    
    // Log cancellation
    _log('M-Pesa Status Query: Transaction ' . $trx['id'] . ' was cancelled by user', 'M-Pesa');
    
    // Redirect with cancellation message
    r2(U . 'order/view/' . $trx['id'], 'w', 'Payment was cancelled. You can try again by creating a new order.');
}

/**
 * Process timeout payment status from status query
 * 
 * Handles timeout payment confirmation from status query.
 * Updates transaction status to failed.
 * 
 * @param object $trx Transaction record from database
 * @return void
 */
function mpesa_process_status_timeout($trx)
{
    // Update transaction status to failed
    $d = ORM::for_table('tbl_payment_gateway')->find_one($trx['id']);
    if ($d && $d['status'] != 2) { // Don't update if already paid
        $d->status = 4; // Failed
        $d->save();
    }
    
    // Log timeout
    _log('M-Pesa Status Query: Transaction ' . $trx['id'] . ' timed out', 'M-Pesa');
    
    // Redirect with timeout message
    r2(U . 'order/view/' . $trx['id'], 'w', 'Payment request timed out. Please try again with a new order.');
}

/**
 * Process insufficient balance status from status query
 * 
 * Handles insufficient balance confirmation from status query.
 * Keeps transaction as pending to allow retry.
 * 
 * @param object $trx Transaction record from database
 * @return void
 */
function mpesa_process_status_insufficient_balance($trx)
{
    // Keep status as pending (don't update)
    _log('M-Pesa Status Query: Transaction ' . $trx['id'] . ' has insufficient balance', 'M-Pesa');
    
    // Redirect with insufficient funds message
    r2(U . 'order/view/' . $trx['id'], 'w', 'Insufficient M-Pesa balance. Please top up your account and try again.');
}

/**
 * Process unknown payment status from status query
 * 
 * Handles unknown payment status from status query.
 * Logs result and sends notification to admin.
 * 
 * @param object $trx Transaction record from database
 * @param string $result_code M-Pesa result code
 * @param string $result_desc M-Pesa result description
 * @param array $result Full status query response
 * @return void
 */
function mpesa_process_status_unknown($trx, $result_code, $result_desc, $result)
{
    // Log unknown status
    _log('M-Pesa Status Query: Unknown status for transaction ' . $trx['id'] . ' - Code: ' . $result_code . ', Desc: ' . $result_desc, 'M-Pesa');
    
    // Send Telegram notification
    $message = "⚠️ M-Pesa Unknown Payment Status\n\n";
    $message .= "Transaction ID: {$trx['id']}\n";
    $message .= "Customer: {$trx['username']}\n";
    $message .= "Result Code: {$result_code}\n";
    $message .= "Result Description: {$result_desc}\n\n";
    $message .= "Please check transaction manually.";
    
    sendTelegram($message);
    
    // Redirect with generic message
    r2(U . 'order/view/' . $trx['id'], 'w', 'Payment status: ' . $result_desc . '. Please contact support if you have completed the payment.');
}

/**
 * Get M-Pesa API base URL based on environment
 * 
 * Returns the appropriate Daraja API base URL for sandbox or production.
 * 
 * @return string Base URL for Daraja API
 */
function mpesa_get_base_url()
{
    global $config;
    
    if (isset($config['mpesa_environment']) && $config['mpesa_environment'] == 'production') {
        return 'https://api.safaricom.co.ke';
    } else {
        return 'https://sandbox.safaricom.co.ke';
    }
}

/**
 * Get OAuth access token from Daraja API
 * 
 * Obtains an OAuth 2.0 access token using consumer key and secret.
 * Token is valid for 1 hour and should be cached.
 * 
 * @return string Access token
 * @throws Exception If authentication fails
 */
function mpesa_get_access_token()
{
    global $config;
    
    $consumer_key = $config['mpesa_consumer_key'];
    $consumer_secret = $config['mpesa_consumer_secret'];
    
    // Create Basic Auth header
    $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
    
    // Get base URL and construct OAuth endpoint
    $base_url = mpesa_get_base_url();
    $url = $base_url . '/oauth/v1/generate?grant_type=client_credentials';
    
    try {
        // Send GET request with Basic Auth
        $response = Http::getData($url, [
            'Authorization: Basic ' . $credentials
        ]);
        
        // Parse JSON response
        $result = json_decode($response, true);
        
        if (isset($result['access_token'])) {
            return $result['access_token'];
        } else {
            // Log error and throw exception
            _log('M-Pesa OAuth Error: ' . json_encode($result), 'M-Pesa');
            sendTelegram("M-Pesa OAuth authentication failed: " . json_encode($result));
            throw new Exception('Failed to obtain M-Pesa access token');
        }
    } catch (Exception $e) {
        _log('M-Pesa OAuth Exception: ' . $e->getMessage(), 'M-Pesa');
        sendTelegram("M-Pesa OAuth exception: " . $e->getMessage());
        throw new Exception('M-Pesa authentication error: ' . $e->getMessage());
    }
}

/**
 * Format phone number to E.164 format
 * 
 * Converts various phone number formats to E.164 format (254XXXXXXXXX) required by M-Pesa.
 * Accepts formats: 0712345678, 712345678, 254712345678, +254712345678
 * 
 * @param string $phone Phone number in any supported format
 * @return string Formatted phone number in E.164 format
 * @throws Exception If phone number is invalid
 */
function mpesa_format_phone_number($phone)
{
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Handle different formats
    if (strlen($phone) == 12 && substr($phone, 0, 3) == '254') {
        // Already in correct format: 254XXXXXXXXX
        $formatted = $phone;
    } elseif (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
        // Format: 0712345678 -> 254712345678
        $formatted = '254' . substr($phone, 1);
    } elseif (strlen($phone) == 9 && (substr($phone, 0, 1) == '7' || substr($phone, 0, 1) == '1')) {
        // Format: 712345678 -> 254712345678
        $formatted = '254' . $phone;
    } else {
        // Invalid format
        throw new Exception('Invalid phone number format. Please use: 0712345678, 712345678, 254712345678, or +254712345678');
    }
    
    // Validate final format
    if (strlen($formatted) != 12) {
        throw new Exception('Invalid phone number length. Expected 12 digits in format 254XXXXXXXXX');
    }
    
    if (substr($formatted, 0, 3) != '254') {
        throw new Exception('Phone number must be a Kenyan number starting with 254');
    }
    
    return $formatted;
}

/**
 * Generate transaction password for M-Pesa API
 * 
 * Creates a Base64 encoded password from shortcode, passkey, and timestamp.
 * Required for STK Push and status query requests.
 * 
 * @param string $shortcode Business shortcode
 * @param string $passkey Lipa Na M-Pesa passkey
 * @param string $timestamp Timestamp in YYYYMMDDHHmmss format
 * @return string Base64 encoded password
 */
function mpesa_generate_password($shortcode, $passkey, $timestamp)
{
    // Concatenate shortcode, passkey, and timestamp
    $concatenated = $shortcode . $passkey . $timestamp;
    
    // Base64 encode the concatenated string
    return base64_encode($concatenated);
}

/**
 * Parse M-Pesa callback metadata
 * 
 * Extracts payment details from callback metadata array.
 * Returns associative array with Amount, MpesaReceiptNumber, TransactionDate, PhoneNumber.
 * 
 * @param array $items Callback metadata items array
 * @return array Associative array with extracted payment details
 */
function mpesa_parse_callback_metadata($items)
{
    // Initialize result array
    $result = [
        'Amount' => null,
        'MpesaReceiptNumber' => null,
        'TransactionDate' => null,
        'PhoneNumber' => null
    ];
    
    // Loop through callback metadata items
    if (is_array($items)) {
        foreach ($items as $item) {
            if (isset($item['Name']) && isset($item['Value'])) {
                $name = $item['Name'];
                $value = $item['Value'];
                
                // Extract specific fields
                switch ($name) {
                    case 'Amount':
                        $result['Amount'] = $value;
                        break;
                    case 'MpesaReceiptNumber':
                        $result['MpesaReceiptNumber'] = $value;
                        break;
                    case 'TransactionDate':
                        $result['TransactionDate'] = $value;
                        break;
                    case 'PhoneNumber':
                        $result['PhoneNumber'] = $value;
                        break;
                }
            }
        }
    }
    
    return $result;
}
