<?php


if( !class_exists('Naxum_Contact') ) {

    class Naxum_Contact {

        protected $_apiBaseUrl = 'https://office.naxum.com/memberapi.cgi';

        const NAXUM_PROCESSING_MODE_LIVE = 'live';
        const NAXUM_PROCESSING_MODE_SANDBOX = 'sandbox';

        protected $_apiCred_live_db = 'jam';
        protected $_apiCred_live_secret = 't2^03ctJ9720G6kjam8';
        protected $_apiCred_sandbox_db = 'jam';
        protected $_apiCred_sandbox_secret = 't2^03ctJ9720G6kjam8';

        protected $_apiProcessingModes = array(self::NAXUM_PROCESSING_MODE_LIVE, self::NAXUM_PROCESSING_MODE_SANDBOX);
        protected $_processingMode;

        protected $_logFileName = 'naxum_api.log';
        protected $_logFilePath;
        protected $_logFileTotalPath;


        public function __construct() {
            $this->setProcessingMode();
            $this->_logFilePath = dirname(__FILE__). "/";
            $this->_logFileTotalPath = $this->_logFilePath . $this->_logFileName;
        }

        public function _buildApiCredentials() {
            $apiCredentials = array(
                'db' => $this->getCredential('db'),
                'secret' => $this->getCredential('secret'),
            );

            if( (empty( $apiCredentials['db'] ) || empty( $apiCredentials['secret'] ) ) ) {
                throw new Exception('Required Credential values not found');
            }

            return $apiCredentials;
        }

        public function callApi($apiDetails) {
            if( !empty($apiDetails) ) {
                try {
                    $url = $this->_apiBaseUrl;

                    $data = http_build_query($apiDetails);

                    $context_options = array (
                        'http' => array (
                            'method' => 'POST',
                            'header'=> "Content-Length: " . strlen($data) . "\r\n",
                            'content' => $data
                        )
                    );

                    $context = stream_context_create($context_options);
                    $result = file_get_contents($url, false, $context);
                    $response = $result;


                    //echo '<pre>'; var_dump($response); echo '</pre>'; die();

                    //$this->log($response);

                    return $response;

                }
                catch(Exception $e) {

                }
            }

            return false;
        }

        public function createContact($contactDetails, $categoryId=14) {
            if( empty($contactDetails) ) {
                throw new Exception('Empty Contact Details');
            }

            $apiCredentials = $this->_buildApiCredentials();

            if( empty($categoryId) ) {
                throw new Exception('Naxum Category ID required');
            }

            $contact = $this->prepareContactDetails($contactDetails);

            $contact['action'] = 'create';

            if( $this->getProcessingMode() == self::NAXUM_PROCESSING_MODE_LIVE ) {
                $contact['catid'] = $categoryId;
            }

            if( !empty($contact) ) {

                $postDetails = array_merge($apiCredentials, $contact);

                $response = $this->callApi($postDetails);

                $this->log( array('nickname'=>$contact['sitename'], 'time'=>date("Y-m-d H:i:s T"), 'action'=>$contact['action'], 'response'=>$response) );

                if( !empty($response) ) {
                    foreach( explode('\n', $response) as $response_line ) {
                        list($returnType, $returnValue) = explode('=', $response_line);

                        if( ($returnType == 'success') && !empty($returnValue) && is_numeric($returnValue) ) {
                            return array('error' => 0, 'success' => 1, 'field'=>$returnType, 'naxum_id'=>$returnValue, 'nickname'=>$contact['sitename']);
                            //return $returnValue; // Naxum ID
                        }
                        if( $returnType == 'sitename-exists' ) {
                            $userId = $this->statusContactByNickname($contact['sitename']);

                            if( !empty($userId) ) {
                                return array('error' => 1, 'success' => 0, 'field'=>$returnType, 'naxum_id'=>$userId, 'nickname'=>$contact['sitename']);
                            }

                            return array('error' => 1, 'success' => 0, 'field'=>$returnType, 'field_value'=>$returnValue);
                        } else {
                            if( $returnType != 'success' ) {
                                return array('error' => 1, 'success' => 0, 'field'=>$returnType, 'field_value'=>$returnValue);
                            }
                        }
                    }
                }

            }

            return false;
        }

        public function updateContact($contactDetails, $categoryId=14) {
            if( empty($contactDetails) ) {
                throw new Exception('Empty Contact Details');
            }

            $apiCredentials = $this->_buildApiCredentials();

            if( empty($categoryId) ) {
                throw new Exception('Naxum Category ID required');
            }

            $action = 'modify';
            $contact = $this->prepareContactDetails($contactDetails, $action);

            $contact['action'] = $action;


            if( $this->getProcessingMode() == self::NAXUM_PROCESSING_MODE_LIVE ) {
                $contact['catid'] = $categoryId;
            }

            if( !empty($contact) ) {

                $postDetails = array_merge($apiCredentials, $contact);

                $response = $this->callApi($postDetails);

                $this->log( array('nickname'=>$contact['sitename'], 'time'=>date("Y-m-d H:i:s T"), 'action'=>$contact['action'], 'response'=>$response) );

                if( !empty($response) ) {
                    foreach( explode('\n', $response) as $response_line ) {
                        list($returnType, $returnValue) = explode('=', $response_line);

                        if( ($returnType == 'success') && !empty($returnValue) && is_numeric($returnValue) ) {
                            return array('error' => 0, 'success' => 1, 'field'=>$returnType, 'naxum_id'=>$returnValue, 'nickname'=>$contact['sitename']);
                            //return $returnValue; // Naxum ID
                        }
                        if( $returnType == 'sitename-exists' ) {
                            $userId = $this->statusContactByNickname($contact['sitename']);

                            if( !empty($userId) ) {
                                return array('error' => 1, 'success' => 0, 'field'=>$returnType, 'naxum_id'=>$userId, 'nickname'=>$contact['sitename']);
                            }

                            return array('error' => 1, 'success' => 0, 'field'=>$returnType, 'field_value'=>$returnValue);

                        } else {
                            if( $returnType != 'success' ) {
                                return array('error' => 1, 'success' => 0, 'field'=>$returnType, 'field_value'=>$returnValue);
                            }
                        }
                    }
                }

            }

            return false;
        }

        public function statusContactByNickname($nickname) {
            $apiCredentials = $this->_buildApiCredentials();

            $contact = array();

            if( !empty($nickname) ) {
                $contact['sitename'] = $nickname;
            } else {
                throw new Exception('Customer Nickname required');
            }

            $contact['action'] = 'status';

            if( !empty($contact) ) {

                $postDetails = array_merge($apiCredentials, $contact);

                $response = $this->callApi($postDetails);

                $this->log( array('nickname'=>$contact['sitename'], 'time'=>date("Y-m-d H:i:s T"), 'action'=>$contact['action'], 'response'=>$response) );

                if( !empty($response) ) {
                    foreach( explode('\n', $response) as $response_line ) {
                        parse_str($response_line, $arr);
                        //list($status, $userId) = $arr;
                        //list($returnType, $returnValue) = explode('=', $response_line);

                        //echo '<pre>'; var_dump( $response_line, $arr, $status, $userId, (!empty($userId) && is_numeric($userId)) ); echo '</pre>'; die();


                        if( !empty($arr['userid']) && is_numeric($arr['userid']) ) {
                            return $arr['userid'];
                        }
                    }
                }
            }

            return false;
        }

        public function prepareContactDetails($contactDetails, $action='create')
        {
            $contact = array();

            /*
            if( !empty($contactDetails['firstname']) ) {
                $contact['fname'] = $contactDetails['firstname'];
            } else {
                throw new Exception('First Name required');
            }

            if( !empty($contactDetails['lastname']) ) {
                $contact['lname'] = $contactDetails['lastname'];
            } else {
                throw new Exception('Last Name required');
            }

            if( !empty($contactDetails['password']) ) {
                $contact['password'] = $contactDetails['password'];
            } else {
                throw new Exception('Customer Password required');
            }
            */

            if( in_array($action, array('modify')) )
            {
                if( !empty($contactDetails['memberid']) ) {
                    $contact['memberid'] = trim($contactDetails['memberid']);
                } else {
                    throw new Exception('Customer Member ID required');
                }
            }

            if( !empty($contactDetails['email']) && filter_var($contactDetails['email'], FILTER_VALIDATE_EMAIL) ) {
                $contact['email'] = trim($contactDetails['email']);
            } else {
                throw new Exception('Valid Email Address required');
            }

            $contactDetails['nickname'] = trim($contactDetails['nickname']);
            if( !empty($contactDetails['nickname']) ) {
                $contact['sitename'] = $contactDetails['nickname'];
            } else {
                throw new Exception('Customer Nickname required');
            }

            if( !empty($contactDetails['referrer_amico']) ) {
                $contact['sponmemberid'] = trim($contactDetails['referrer_amico']);
            }
            elseif( !empty($contactDetails['sponmemberid']) ) {
                $contact['sponmemberid'] = trim($contactDetails['sponmemberid']);
            }
            if( !empty($contact['sponmemberid']) ) {
                $contact['sponsorid'] = $contact['sponmemberid'];
                //unset($contact['sponmemberid']);
                $contact['sponmemberid'] = 3; // We need to send the
            }

            if( !empty($contactDetails['phone']) ) {
                $contactDetails['phone'] = str_replace(array('.', ' ', '+', '-', '(', ')'), '', $contactDetails['phone']);
            }
            if( is_numeric($contactDetails['phone']) ) {
                $contactDetails['phone'] = (int) $contactDetails['phone'];
            } else {
                $contactDetails['phone'] = '';
            }
            if( $contactDetails['phone'] != 10 ) {
                $contactDetails['phone'] = '';
            }

            if( !empty($contactDetails['firstname']) ) { $contact['fname'] = trim($contactDetails['firstname']); }
            if( !empty($contactDetails['lastname']) ) { $contact['lname'] = trim($contactDetails['lastname']); }
            if( !empty($contactDetails['password']) ) { $contact['password'] = $contactDetails['password']; }
            if( !empty($contactDetails['memberid']) ) { $contact['memberid'] = trim($contactDetails['memberid']); }
            if( !empty($contactDetails['memberid2']) ) { $contact['memberid2'] = trim($contactDetails['memberid2']); }
            if( !empty($contactDetails['phone']) ) { $contact['cellphone'] = $contactDetails['phone']; }
            //if( !empty($contactDetails['fax']) ) { $contact['fax'] = $contactDetails['fax']; }
            if( !empty($contactDetails['address']) ) { $contact['address'] = $contactDetails['address']; }
            if( !empty($contactDetails['address2']) ) { $contact['address2'] = $contactDetails['address2']; }
            if( !empty($contactDetails['city']) ) { $contact['city'] = $contactDetails['city']; }
            if( !empty($contactDetails['state']) ) { $contact['state'] = $contactDetails['state']; }
            if( !empty($contactDetails['zip']) ) { $contact['zip'] = $contactDetails['zip']; }
            if( !empty($contactDetails['country']) ) { $contact['country'] = $contactDetails['country']; }
            //if( !empty($contactDetails['ip']) ) { $contact['ip'] = $contactDetails['ip']; }

            return $contact;
        }

        public function getCredential($key) {
            if( !empty($key) ) {
                $keyVar = "_apiCred_{$this->_processingMode}_{$key}";

                if( !empty($this->{$keyVar}) ) {
                    return $this->{$keyVar};
                }
            }

            return false;
        }

        public function setProcessingMode($mode='live') {
            if( in_array($mode, $this->_apiProcessingModes) ) {
                $this->_processingMode = $mode;
            } else {
                throw new Exception('Invalid Processing Mode');
            }
        }

        public function getProcessingMode() {
            return $this->_processingMode;
        }

        public function log($logContent) {
            if( !is_dir($this->_logFilePath) ) {
                mkdir( $this->_logFilePath );
            }

            //$logContent = !is_string($logContent) ? print_r($logContent, true) : $logContent;

            file_put_contents( $this->_logFileTotalPath, (!is_string($logContent) ? print_r($logContent, true) : $logContent), FILE_APPEND );
        }

    }
    
}