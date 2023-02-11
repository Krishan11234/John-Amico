<?php


if( !class_exists('Appnotch_Tenant') ) {

    class Appnotch_User {

        private $_dbName;
        private $_dbConnection;
        private $_apiAuthCode;

        private $_appnotchDbReady = false;

        protected $_apiBaseUrl = 'https://api.appnotch.com/v2/';
        protected $_apiCred_APPID = 44745;
        protected $_apiCred_SUBJECT = 'MyJA-44745';
        protected $_apiCred_APIKEY = 'jE+qWLNSlAv8mVg11gO9AGYtNPeAwYagws5bxMHoS5o=';

        protected $_apiCred_IDENTITY = 23;

        protected $_logFileName = 'appnotch_api.log';
        protected $_logFilePath;
        protected $_logFileTotalPath;

        CONST APPNOTCH_TABLE_NAME = 'appnotch_users';


        CONST APPNOTCH_API_ERROR__MEMBER_EXIST_MESSAGE = "member with the given email address already exists";
        CONST APPNOTCH_API_ERROR__TENANT_EXIST_MESSAGE = "another tenant exists with";
        CONST APPNOTCH_API_ERROR__TENANT_AND_MEMBER_BOUND_MESSAGE = "member has already access to this tenant";




        public function __construct($dbName, $dbConn)
        {
            if(!empty($dbConn)) {
                $this->_dbName = $dbName;
            }
            if(!empty($dbConn)) {
                $this->_dbConnection = $dbConn;
            }

            $this->_logFilePath = dirname(__FILE__). "/";
            $this->_logFileTotalPath = $this->_logFilePath . $this->_logFileName;

            $this->_prepareAppnotchTable();
        }

        private function _prepareAppnotchTable()
        {
            try {
                $tableCheckSql = "SELECT TABLE_CATALOG FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$this->_dbName}' AND TABLE_NAME = '{$this->getTableName()}'; ";
                $query = mysqli_query($this->getDBConnection(), $tableCheckSql);

                if( mysqli_num_rows($query) < 1 ) {
                    $createTableSql = "
                        CREATE TABLE IF NOT EXISTS `{$this->getTableName()}` (
                            `auid` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `ref_user_type` varchar(30) NOT NULL,
                            `ref_user_id` int NOT NULL,
                            `tenant_id` int NOT NULL,
                            `tenant_branch_url` varchar(200) NOT NULL,
                            `tenantmember_id` int NOT NULL,
                            `tenantmember_email` varchar(200) NOT NULL,
                            `tenantmember_password` varchar(64) NOT NULL,
                            -- `tenantmember_firstname` varchar(50) NOT NULL,
                            -- `tenantmember_lastname` varchar(200) NOT NULL,
                            `tenantmember_name` varchar(200) NOT NULL,
                            `created_at` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
                            `updated_at` TIMESTAMP DEFAULT NOW() ON UPDATE NOW()
                        ) COLLATE 'utf8_unicode_ci';
                    ";
                    $createTableQuery = mysqli_query($this->getDBConnection(), $createTableSql) or die(mysqli_error($this->getDBConnection()));
                } else {
                    $createTableQuery = 1;
                }

                if($createTableQuery) {
                    $columnCheckSql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$this->_dbName}' AND TABLE_NAME = 'tbl_member' AND COLUMN_NAME = 'ja_mobileapp_user_id'";
                    $query = mysqli_query($this->getDBConnection(), $columnCheckSql);

                    if( mysqli_num_rows($query) < 1 ) {
                        $alterSql = " ALTER TABLE `tbl_member` ADD `ja_mobileapp_user_id` int(11) NOT NULL DEFAULT '0' AFTER `bit_ja_mobileapp_active`; ";
                        $query = mysqli_query($this->getDBConnection(), $alterSql) or die(mysqli_error($this->getDBConnection()));

                        $alterSql = " ALTER TABLE `tbl_member` ADD FOREIGN KEY (`ja_mobileapp_user_id`) REFERENCES `{$this->getTableName()}` (`auid`) ON DELETE SET NULL; ";
                        $query = mysqli_query($this->getDBConnection(), $alterSql) or die(mysqli_error($this->getDBConnection()));
                    }

                    $indexCheckSql = "SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = '{$this->_dbName}' AND TABLE_NAME = '{$this->getTableName()}' AND CONSTRAINT_NAME = 'tenantmember_id_tenantmember_email'";
                    $query = mysqli_query($this->getDBConnection(), $indexCheckSql);

                    if( mysqli_num_rows($query) < 1 ) {
                        $alterSql = " ALTER TABLE `{$this->getTableName()}` ADD UNIQUE `tenantmember_id_tenantmember_email` (`tenantmember_id`, `tenantmember_email`); ";
                        $query = mysqli_query($this->getDBConnection(), $alterSql) or die(mysqli_error($this->getDBConnection()));
                    }

                    if($query) {
                        $this->_appnotchDbReady = true;
                        return true;
                    }
                }
                return false;
            }
            catch (Exception $e) {
                $this->log( " APPNOTCH_DB_READY_ERROR  : {$e->getMessage()}", __LINE__ );
            }
        }

        protected function _buildApiCredentials()
        {
            $apiCredentials = array(
                'Subject' => $this->getCredential('SUBJECT'),
                'Key' => $this->getCredential('APIKEY'),
            );

            if( (empty( $apiCredentials['Subject'] ) || empty( $apiCredentials['Key'] ) ) ) {
                $this->log( " BUILD_CRED_ERROR  : Required Credential values not found ", __LINE__ );
                return false;
            }

            return $apiCredentials;
        }

        private function _getAuthorization()
        {
            try {

                if(!empty($this->_apiAuthCode)) {
                    return $this->_apiAuthCode;
                }

                $params = $this->_buildApiCredentials();
                $response = $this->callApi($params, 'auth');

                //echo '<pre>'; var_dump($response); die();

                if(!empty($response)) {
                    $this->_apiAuthCode = $response;
                    return $response;
                }
            }
            catch (Exception $e) {
                $this->log( "getAuthorization_ERROR: " . $e->getMessage(), __LINE__ );
            }
            return false;
        }

        public function createTenantMember($contactDetails)
        {
            if( empty($contactDetails) ) {
                $this->log( "ERROR: Empty Contact Details", __LINE__ );
                return false;
            }

            $contact = $this->prepareContactDetails($contactDetails);
            $existingContact = $this->checkAppnotchUserExistsInDb($contact['email'], 'tenantmember_email', true);

            //echo '<pre>'; var_dump($existingContact); die();
            if( !empty($existingContact['tenantmember_id']) ) {
                return $existingContact;
            }

            $authToken = $this->_getAuthorization();
            if(empty($authToken)) {
                $this->log( "Empty Authorization Code", __LINE__ );
                return false;
            }

            $contact["createdBy"] = $this->getCredential('IDENTITY');
            $contact["authToken"] = $authToken;

            //echo '<pre>'; var_dump($contact); die();

            if( !empty($contact) ) {
                $response = $this->callApi($contact, 'tenantmember');

                //$this->log( array('nickname'=>$contact['sitename'], 'time'=>date("Y-m-d H:i:s T"), 'action'=>$contact['action'], 'response'=>$response) );

                //echo '<pre>'; var_dump($response); die();
                if(is_string($response)) {
                    if( strtolower($response) == self::APPNOTCH_API_ERROR__MEMBER_EXIST_MESSAGE) {
                        $response = $this->readTenantMember($contact['email']);
                    }
                }

                if( !empty($response['id']) ) {
                    $insertableArray = array(
                        'ref_user_type' => $contactDetails['ref_user_type'],
                        'ref_user_id' => $contactDetails['ref_user_id'],
                        'tenant_id' => 0,
                        'tenant_branch_url' => '',
                        'tenantmember_id' => $response['id'],
                        'tenantmember_email' => $response['email'],
                        'tenantmember_password' => $contact['password'],
                        'tenantmember_name' => $contact['name'],
                        'created_at' => 'NOW()',
                    );

                    $sql = " INSERT INTO {$this->getTableName()} {$this->_insertableQueryMaker($insertableArray)} ; ";
                    $query = mysqli_query($this->getDBConnection(), $sql) or die(mysqli_error($this->getDBConnection()));

                    if($query) {
                        return $insertableArray;
                    }
                } else {
                    $this->log( "TENANT_MEMBER_CREATE_ERROR: {$response}", __LINE__ );

                    return false;
                }

            }

            return false;
        }

        public function readTenantMember($memberEmail)
        {
            if( empty($memberEmail) ) {
                $this->log( "ERROR: GetTeanatMemberDetails:  Email not found", __LINE__ );
                return false;
            }

            $authToken = $this->_getAuthorization();
            if(empty($authToken)) {
                $this->log( "Empty Authorization Code", __LINE__ );
                return false;
            }
            $contact["authToken"] = $authToken;

            $response = $this->callApi($contact, 'tenantmember?email=' . urlencode($memberEmail), 'GET' );

            if( !empty($response['id']) ) {
                return $response;
            } else {
                $this->log( "TENANT_MEMBER_READ_ERROR: {$response}", __LINE__ );
                return false;
            }
        }

        public function createTenant($contactDetails, $memberDetails=array())
        {
            if( empty($contactDetails) ) {
                $this->log( "ERROR: Empty Contact Details", __LINE__ );
                return false;
            }

            $contact = $this->prepareContactDetails($contactDetails);
            //echo '<pre>'; var_dump($contact); die();
            $existingContact = $this->checkAppnotchUserExistsInDb($contactDetails['tenantmember_id'], 'tenantmember_id', true);

            if( !empty($existingContact['tenant_id']) ) {
                return $existingContact;
            }

            $authToken = $this->_getAuthorization();
            if(empty($authToken)) {
                $this->log( "Empty Authorization Code", __LINE__ );
                return false;
            }

            $contact["appId"] = $this->getCredential('APPID');
            $contact["authToken"] = $authToken;

            // App Footer Request
            $contact["footer"]['footerType'] = 1;
            $contact["footer"]['footerData'] = array(
                array(
                    "Type" => "n/a,n/a,n/a",
                    "Title" => "Home",
                    "URL" => "native:home",
                    "Image" => "fa fa-home",
                    "ImageCode" => null,
                    "FColor" => "FFFFFF",
                    "BColor" => "1D0430",
                    "STabColor" => "590C94",
                    "IsBurger" => false,
                ),
                array(
                    "Type" => "phone",
                    "Title" => "Phone",
                    "URL" => "tel:{$contact['phone']}",
                    "Image" => "fa fa-phone",
                    "ImageCode" => null,
                    "FColor" => "FFFFFF",
                    "BColor" => "1D0430",
                    "STabColor" => "590C94",
                    "IsBurger" => false,
                ),
                array(
                    "Type" => "n/a,,n/a",
                    "Title" => "Share",
                    "URL" => "native:share",
                    "Image" => "fa fa-share-alt",
                    "ImageCode" => null,
                    "FColor" => "FFFFFF",
                    "BColor" => "1D0430",
                    "STabColor" => "590C94",
                    "IsBurger" => false,
                ),
                array(
                    "Type" => "pushlog",
                    "Title" => "Messages",
                    "URL" => "native:pushlog",
                    "Image" => "fa fa-comments-o",
                    "ImageCode" => null,
                    "FColor" => "FFFFFF",
                    "BColor" => "1D0430",
                    "STabColor" => "590C94",
                    "IsBurger" => false,
                ),
                array(
                    "Type" => "email",
                    "Title" => "Email",
                    "URL" => "mailto:{$memberDetails['email']}",
                    "Image" => "fa fa-envelope",
                    "ImageCode" => null,
                    "FColor" => "FFFFFF",
                    "BColor" => "1D0430",
                    "STabColor" => "590C94",
                    "IsBurger" => false,
                )
            );

            $contact["LandingPage"]["HomeData"] = array(
                array(
                    "Type" => "n/a,n/a,n/a",
                    "Title" => "Shop",
                    "Url" => "native:shop:Shop/{$contact['url']}",
                    "Image" => "fa fa-shopping-cart",
                    "ImageCode" => null
                ),
                array(
                    "Type" => "phone",
                    "Title" => "Phone",
                    "Url" => "tel:{$contact['phone']}",
                    "Image" => "fa fa-phone",
                    "ImageCode" => null
                ),
                array(
                    "Type" => "pushlog",
                    "Title" => "Messages",
                    "Url" => "native:pushlog",
                    "Image" => "fa fa-comments-o",
                    "ImageCode" => null
                ),
                array(
                    "Type" => "n/a,,n/a",
                    "Title" => "Share",
                    "Url" => "native:share",
                    "Image" => "fa fa-share-alt",
                    "ImageCode" => null
                )
            );

            //echo '<pre>'; var_dump($contactDetails, $contact, json_encode($contact)); die();
            //echo '<pre>'; print_r(json_encode($contact)); die();

            if( !empty($contact) ) {
                $response = $this->callApi($contact, "apps/{$this->getCredential('APPID')}/tenants");

                //echo '<pre>'; var_dump($response); die();
                if( !empty($response['id']) ) {
                    $insertableArray = array(
                        'tenant_id' => $response['id'],
                        'tenant_branch_url' => $response['branchUrl'],
                    );

                    $sql = " UPDATE {$this->getTableName()} SET {$this->_updateableQueryMaker($insertableArray)} WHERE tenantmember_id='{$contactDetails['tenantmember_id']}'; ";
                    $query = mysqli_query($this->getDBConnection(), $sql) or die(mysqli_error($this->getDBConnection()));

                    if($query) {
                        return $response;
                    }
                } else {
                    $this->log( "TENANT_CREATE_ERROR: {$response}", __LINE__ );
                    return false;
                }
            }

            return false;
        }

        public function enableTenant($tenantId)
        {
            if(empty($tenantId)) {
                $this->log( "DISABLE_TENANT_ERROR: No Tenant ID Found", __LINE__ );
                return false;
            }

            $existingContact = $this->checkAppnotchUserExistsInDb($tenantId, 'tenant_id');

            if( empty($existingContact) ) {
                $this->log( "DISABLE_TENANT_ERROR: Tenant ID Invalid", __LINE__ );
                return false;
            }

            $authToken = $this->_getAuthorization();
            if(empty($authToken)) {
                $this->log( "Empty Authorization Code", __LINE__ );
                return false;
            }

            $contact["authToken"] = $authToken;
            $contact["disabled"] = false;

            if( !empty($contact) ) {
                $response = $this->callApi($contact, "apps/{$this->getCredential('APPID')}/tenants/{$tenantId}", 'PUT');
                if( !empty($response['id']) ) {
                    return true;
                }
            }

            return false;
        }

        public function disableTenant($tenantId)
        {
            if(empty($tenantId)) {
                $this->log( "DISABLE_TENANT_ERROR: No Tenant ID Found", __LINE__ );
                return false;
            }

            $existingContact = $this->checkAppnotchUserExistsInDb($tenantId, 'tenant_id');
            if( empty($existingContact) ) {
                $this->log( "DISABLE_TENANT_ERROR: Tenant ID Invalid", __LINE__ );
                return false;
            }

            $authToken = $this->_getAuthorization();
            if(empty($authToken)) {
                $this->log( "Empty Authorization Code", __LINE__ );
                return false;
            }

            $contact["authToken"] = $authToken;
            $contact["disabled"] = 1;

            if( !empty($contact) ) {
                $response = $this->callApi($contact, "apps/{$this->getCredential('APPID')}/tenants/{$tenantId}", 'PUT');
                //echo '<pre>'; var_dump($response); die();
                if( !empty($response['id']) ) {
                    return true;
                } else {
                    $this->log( "TENANT_MEMBER_DISABLE_ERROR: {$response}", __LINE__ );
                }
            }

            return false;
        }

        public function deleteTenantMember($tenantMemberId)
        {
            if(empty($tenantMemberId)) {
                $this->log( "DISABLE_TENANT_ERROR: No Tenant ID Found", __LINE__ );
                return false;
            }

            $existingContact = $this->checkAppnotchUserExistsInDb($tenantId, 'tenant_id');
            if( empty($existingContact) ) {
                $this->log( "DISABLE_TENANT_ERROR: Tenant ID Invalid", __LINE__ );
                return false;
            }

            $authToken = $this->_getAuthorization();
            if(empty($authToken)) {
                $this->log( "Empty Authorization Code", __LINE__ );
                return false;
            }

            $contact["authToken"] = $authToken;
            $contact["disabled"] = 1;

            if( !empty($contact) ) {
                $response = $this->callApi($contact, "apps/{$this->getCredential('APPID')}/tenants/{$tenantId}", 'PUT');
                //echo '<pre>'; var_dump($response); die();
                if( !empty($response['id']) ) {
                    return true;
                } else {
                    $this->log( "TENANT_MEMBER_DISABLE_ERROR: {$response}", __LINE__ );
                }
            }

            return false;
        }

        public function deleteTenant($tenantId)
        {
            if(empty($tenantId)) {
                $this->log( "DISABLE_TENANT_ERROR: No Tenant ID Found", __LINE__ );
                return false;
            }

            $existingContact = $this->checkAppnotchUserExistsInDb($tenantId, 'tenant_id');
            if( empty($existingContact) ) {
                $this->log( "DISABLE_TENANT_ERROR: Tenant ID Invalid", __LINE__ );
                return false;
            }

            $authToken = $this->_getAuthorization();
            if(empty($authToken)) {
                $this->log( "Empty Authorization Code", __LINE__ );
                return false;
            }

            $contact["authToken"] = $authToken;
            $contact["disabled"] = 1;

            if( !empty($contact) ) {
                $response = $this->callApi($contact, "apps/{$this->getCredential('APPID')}/tenants/{$tenantId}", 'PUT');
                //echo '<pre>'; var_dump($response); die();
                if( !empty($response['id']) ) {
                    return true;
                } else {
                    $this->log( "TENANT_MEMBER_DISABLE_ERROR: {$response}", __LINE__ );
                }
            }

            return false;
        }


        public function bindTenantWithTenantMember($tenantMember, $tenant)
        {
            if(empty($tenantMember)) {
                $this->log( "Tenant Member ID not Passed", __LINE__ );
                return false;
            }
            if(empty($tenant)) {
                $this->log( "Tenant ID not Passed", __LINE__ );
                return false;
            }

            $authToken = $this->_getAuthorization();
            if(empty($authToken)) {
                $this->log( "Empty Authorization Code", __LINE__ );
                return false;
            }

            $contact["isOwner"] = true;
            $contact["authToken"] = $authToken;

            $response = $this->callApi($contact, "tenantmember/{$tenantMember}/associate/{$tenant}");

            if(
                !empty($response['id']) ||
                ( is_string($response) && (strpos( strtolower($response), self::APPNOTCH_API_ERROR__TENANT_AND_MEMBER_BOUND_MESSAGE) !== false) )
            ) {
                $contactId = $this->checkAppnotchUserExistsInDb($tenantMember, 'tenantmember_id', false, true);
                //echo '<pre>'; var_dump($contactId); die();

                return $contactId;
            } else {
                $this->log( "TENANT_MEMBER_ASSIGNING_ERROR: {$response}", __LINE__ );
            }

            return false;
        }



        public function createTenantMemberAndCreateTenantAndAssign($contactDetails)
        {
            $tenantMember = $this->createTenantMember($contactDetails);

            $tenantMemberId = !empty($tenantMember['id']) ? $tenantMember['id'] : ( !empty($tenantMember['tenantmember_id']) ? $tenantMember['tenantmember_id'] : 0 );

            if(!empty($tenantMemberId)) {
                $tenantContactDetails = array(
                    "displayName" => $contactDetails['name'],
                    "url" => $contactDetails['url'],
                    "disabled" => false,
                    "hidden" => false,
                    "firstName" => $contactDetails['firstName'],
                    "lastName" => $contactDetails['lastName'],
                    "phone" => $contactDetails['phoneNumber'],
                    'tenantmember_id' => $tenantMemberId,
                    'tag' => (!empty($contactDetails['tag']) ? $contactDetails['tag'] : ''),
                );

                $tenant = $this->createTenant($tenantContactDetails, $contactDetails);

                if( !empty($tenant['id']) || !empty($tenant['tenant_id']) ) {
                    $tenantId = !empty($tenant['id']) ? $tenant['id'] : ( !empty($tenant['tenant_id']) ? $tenant['tenant_id'] : 0 );

                    //echo '<pre>'; var_dump($tenantMemberId, $tenant, $tenantId); die();

                    if(!empty($tenantId)) {
                        $associated = $this->bindTenantWithTenantMember($tenantMemberId, $tenantId, false, true);
                        if($associated) {
                            return $associated;
                        }
                    }
                }
            }
            return false;
        }

        public function deleteTenantMemberAndDeleteTenant($contactDetails)
        {
            if(!empty($tenantMember['id']) || !empty($tenantMember['tenantmember_id'])) {

            }
            return false;
        }


        public function callApi($apiDetails, $path='', $method='POST')
        {
            try {

               if(!empty($method)) {
                    if( !in_array( strtoupper($method), array('POST', 'GET', 'READ', 'DELETE', 'PUT', 'UPDATE')) ) {
                        $this->log('CURL_ERROR: Invalid Method: ' . $method, __LINE__);
                        return false;
                    }
                }

                $authToken = !empty($apiDetails['authToken']) ? $apiDetails['authToken'] : '';
                $url = $this->_apiBaseUrl . "$path";
                //if(!empty( $authToken)) { echo '<pre>'; var_dump($url); die(); }

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_CUSTOMREQUEST => $method,
                    CURLOPT_POSTFIELDS => !empty($apiDetails) ? json_encode($apiDetails) : '',
                    CURLOPT_HTTPHEADER => array(
                        "Cache-Control: no-cache",
                        "Content-Type: application/json",
                        (!empty($authToken) ? "Authorization: Bearer {$authToken}" : ''),
                    ),
                ));

                $response = curl_exec($curl);
                $error = curl_error($curl);

                $info = curl_getinfo($curl);

                //if(!empty( $authToken)) { echo '<pre>'; var_dump($response, $error, $info); die(); }

                curl_close($curl);

                if (!empty($error)) {
                    $this->log('CURL_ERROR: ' . print_r($error, true), __LINE__);
                }


                //echo '<pre>'; var_dump($response); echo '</pre>'; die();

                //$this->log($response);

                $response = trim($response, '"');
                $response = $this->isJson($response) ? (array) json_decode($response) : $response;

                return $response;

            } catch (Exception $e) {
                $this->log("CURL_ERROR: " . $e->getMessage(), __LINE__);
            }

            return false;
        }

        public function prepareContactDetails($contactDetails)
        {
            $contact = array();

            if( !empty($contactDetails['email']) ) {
                if( filter_var($contactDetails['email'], FILTER_VALIDATE_EMAIL)  ) {
                    $contact['email'] = trim($contactDetails['email']);
                }
                else {
                    return false;
                }
            }

            if( !empty($contactDetails['phoneNumber']) ) {
                $contactDetails['phoneNumber'] = str_replace(array('.', ' ', '+', '-', '(', ')'), '', $contactDetails['phoneNumber']);
            }
            if( !empty($contactDetails['phone']) ) {
                $contactDetails['phone'] = str_replace(array('.', ' ', '+', '-', '(', ')'), '', $contactDetails['phone']);
            }
            if( !empty($contactDetails['url']) ) {
                $contactDetails['url'] = (strpos($contactDetails['url'], 'http') !== false) ? $contactDetails['url'] : 'http:' . $contactDetails['url'];
            }

            if( !empty($contactDetails['ref_user_type']) ) { $contact['ref_user_type'] = trim($contactDetails['ref_user_type']); }
            if( !empty($contactDetails['ref_user_id']) ) { $contact['ref_user_id'] = trim($contactDetails['ref_user_id']); }
            if( !empty($contactDetails['displayName']) ) { $contact['displayName'] = trim($contactDetails['displayName']); }
            if( !empty($contactDetails['iconUrl']) ) { $contact['iconUrl'] = trim($contactDetails['iconUrl']); }
            if( !empty($contactDetails['splashUrl']) ) { $contact['splashUrl'] = trim($contactDetails['splashUrl']); }
            if( !empty($contactDetails['url']) ) { $contact['url'] = trim($contactDetails['url']); }
            if( isset($contactDetails['disabled']) ) { $contact['disabled'] = (boolean) $contactDetails['disabled']; }
            if( isset($contactDetails['hidden']) ) { $contact['hidden'] = (boolean) $contactDetails['hidden']; }
            if( !empty($contactDetails['password']) ) { $contact['password'] = $contactDetails['password']; }
            if( !empty($contactDetails['firstName']) ) { $contact['firstName'] = trim($contactDetails['firstName']); }
            if( !empty($contactDetails['lastName']) ) { $contact['lastName'] = trim($contactDetails['lastName']); }
            if( !empty($contactDetails['phone']) ) { $contact['phone'] = $contactDetails['phone']; }
            if( !empty($contactDetails['phoneNumber']) ) { $contact['phoneNumber'] = $contactDetails['phoneNumber']; }
            if( !empty($contactDetails['tag']) ) { $contact['tag'] = $contactDetails['tag']; }

            return $contact;
        }

        public function getTableName()
        {
            return self::APPNOTCH_TABLE_NAME;
        }

        public function appnotchDBReady()
        {
            return $this->_appnotchDbReady;
        }

        public function getDBConnection()
        {
            return $this->_dbConnection;
        }

        public function getCredential($key) {
            if( !empty($key) ) {
                $keyVar = "_apiCred_{$key}";

                if( !empty($this->{$keyVar}) ) {
                    return $this->{$keyVar};
                }
            }

            return false;
        }

        public function checkAppnotchUserExistsInDb($identifier, $by='email', $returnRow=false, $returnCol=false)
        {
            if(!empty($identifier)) {
                $by = (!empty($by) ? $by : 'tenantmember_email');
                if($by == 'email') { $by = 'tenantmember_email'; }

                $select = $returnRow ? "*" : "auid";

                $sql = "SELECT {$select} FROM {$this->getTableName()} WHERE `{$by}`='{$identifier}' ";
                $query = mysqli_query($this->getDBConnection(), $sql);

                if( mysqli_num_rows($query) > 0 ) {
                    if($returnRow) {
                        $result = mysqli_fetch_assoc($query);

                        return $result;
                    }
                    if($returnCol) {
                        $result = mysqli_fetch_assoc($query);
                        return $result[$select];
                    }
                    return true;
                }
            }
            return false;
        }

        private function _insertableQueryMaker($data)
        {
            $queryString = '';

            if(!empty($data) && is_array($data)) {
                $keys = "(`".implode('`, `', array_keys($data))."`)";
                $values = "VALUES ('".implode("', '", array_values($data))."')";

                $queryString = " {$keys}  {$values} ";
            }

            return $queryString;
        }

        private function _updateableQueryMaker($data)
        {
            $queryString = '';
            $queryStrings = [];

            if(!empty($data) && is_array($data)) {
                foreach($data as $key => $value) {
                    $queryStrings[] = " `{$key}`='{$value}'";
                }
                $queryString = implode(', ', $queryStrings);
            }

            return $queryString;
        }

        public function log($logContent, $lineNumber='')
        {
            if( !is_dir($this->_logFilePath) ) {
                mkdir( $this->_logFilePath );
            }

            $content = (!is_string($logContent) ? print_r($logContent, true) : $logContent) . PHP_EOL . PHP_EOL;
            file_put_contents( $this->_logFileTotalPath, date('Y-m-d H:i:s') . ":  --- LINE : {$lineNumber} ---  :: " . $content, FILE_APPEND );
        }

        public function isJson($string) {
            if (is_numeric($string) || empty($string) ) return false;
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }


        public function urlify ($text, $delimiter='-') {

            $replace = [
                '&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
                '&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä'=> 'Ae',
                '&Auml;' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae',
                'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D',
                'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E',
                'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G',
                'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I',
                'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I',
                'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'K', 'Ľ' => 'K',
                'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N',
                'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
                'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O',
                'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S',
                'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
                'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U',
                '&Uuml;' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U',
                'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z',
                'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
                'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
                'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
                'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
                'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e',
                'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h',
                'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
                'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j',
                'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l',
                'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n',
                'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
                '&ouml;' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe',
                'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
                'û' => 'u', 'ü' => 'ue', 'ū' => 'u', '&uuml;' => 'ue', 'ů' => 'u', 'ű' => 'u',
                'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y',
                'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'ß' => 'ss',
                'ſ' => 'ss', 'ый' => 'iy', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
                'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
                'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
                'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
                'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '',
                'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a',
                'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
                'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l',
                'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
                'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
                'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e',
                'ю' => 'yu', 'я' => 'ya'
            ];

            // make a human readable string
            $text = strtr($text, $replace);

            // replace non letter or digits by -
            $text = preg_replace('~[^\\pL\d.]+~u', $delimiter, $text);

            // trim
            $text = trim($text, $delimiter);

            // remove unwanted characters
            $text = preg_replace('~[^'.$delimiter.'\w.]+~', '', $text);

            $text = strtolower($text);

            return $text;
        }

    }
    
}