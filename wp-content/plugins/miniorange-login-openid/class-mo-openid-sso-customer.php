<?php
/** miniOrange enables user to log in through OpenID to apps such as Google, Salesforce etc.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange OAuth
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
This library is miniOrange Authentication Service.
Contains Request Calls to Customer service.

**/
class CustomerOpenID {

	public $email;
	public $phone;
   
	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
	
	function create_customer(){

		$url = get_option('mo_openid_host_name') . '/moas/rest/customer/add';
		$ch = curl_init( $url );
		global $current_user;
		$current_user = wp_get_current_user();
		$this->email 		= get_option('mo_openid_admin_email');
		$this->phone 		= get_option('mo_openid_admin_phone');
		$company = get_option('mo_openid_admin_company_name');
		$first_name = get_option('mo_openid_admin_first_name');
		$last_name = get_option('mo_openid_admin_last_name');
		$password = get_option('mo_openid_admin_password');

		$fields = array(
			'companyName' => $company,
			'areaOfInterest' => 'WP OpenID Connect Login Plugin',
			'firstname'	=> $first_name,
			'lastname'	=> $last_name,
			'email'		=> $this->email,
			'phone'		=> $this->phone,
			'password'	=> $password
		);
		$field_string = json_encode($fields);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string );
		$content = curl_exec( $ch );

		if( curl_errno( $ch ) ){
			echo 'Request Error:' . curl_error( $ch );
			exit();
		}

		curl_close( $ch );
		return $content;
	}

	function get_customer_key() {
		$url 	= get_option('mo_openid_host_name') . "/moas/rest/customer/key";
		$ch 	= curl_init( $url );
		$email 	= get_option("mo_openid_admin_email");

		$password = get_option("mo_openid_admin_password");

		$fields = array(
			'email' 	=> $email,
			'password' 	=> $password
		);
		$field_string = json_encode( $fields );

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec( $ch );
		if( curl_errno( $ch ) ){
			echo 'Request Error:' . curl_error( $ch );
			exit();
		}
		curl_close( $ch );

		return $content;
	}

	function check_customer() {
		$url 	= get_option('mo_openid_host_name') . "/moas/rest/customer/check-if-exists";
		$ch 	= curl_init( $url );
		$email 	= get_option("mo_openid_admin_email");

		$fields = array(
			'email' 	=> $email,
		);
		$field_string = json_encode( $fields );

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec( $ch );
		if( curl_errno( $ch ) ){
			echo 'Request Error:' . curl_error( $ch );
			exit();
		}
		curl_close( $ch );

		return $content;
	}

	function send_otp_token($authType){
		$url = get_option('mo_openid_host_name') . '/moas/api/auth/challenge';
		$ch = curl_init($url);
		$customerKey =  $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$username = get_option('mo_openid_admin_email');
		$phone = get_option('mo_openid_admin_phone');
        $currentTimeInMillis = self::get_timestamp();

		/* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;

		if($authType == 'EMAIL') {
			$fields = array(
			'customerKey' => $customerKey,
			'email' => $username,
			'authType' => 'EMAIL',
			'transactionName' => 'WordPress miniOrange Social Login, Social Sharing'
			);
		}else if($authType == 'SMS'){
			$fields = array(
			'customerKey' => $customerKey,
			'phone' => $phone,
			'authType' => 'SMS',
			'transactionName' => 'WordPress miniOrange Social Login, Social Sharing'
		    );
		}
		$field_string = json_encode($fields);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader, $timestampHeader, $authorizationHeader));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
        $proxy_host = get_option("mo_proxy_host");
        if(!empty($proxy_host)){
            curl_setopt($ch, CURLOPT_PROXY, get_option("mo_proxy_host"));
            curl_setopt($ch, CURLOPT_PROXYPORT, get_option("mo_proxy_port"));
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, get_option("mo_proxy_username").':'.get_option("mo_proxy_password"));
        }
		$content = curl_exec($ch);

		if(curl_errno($ch)){
			echo 'Request Error:' . curl_error($ch);
			exit();
		}
		curl_close($ch);
		return $content;
	}

    function get_timestamp() {
        $url = get_option ( 'mo_openid_host_name' ) . '/moas/rest/mobile/get-timestamp';
        $ch = curl_init ( $url );

        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt ( $ch, CURLOPT_ENCODING, "" );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 2 ); // required for https urls

        curl_setopt ( $ch, CURLOPT_MAXREDIRS, 10 );

        curl_setopt ( $ch, CURLOPT_POST, true );

        $proxy_host = get_option("mo_proxy_host");
        if(!empty($proxy_host)){
            curl_setopt($ch, CURLOPT_PROXY, get_option("mo_proxy_host"));
            curl_setopt($ch, CURLOPT_PROXYPORT, get_option("mo_proxy_port"));
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, get_option("mo_proxy_username").':'.get_option("mo_proxy_password"));
        }
        $content = curl_exec ( $ch );

        if (curl_errno ( $ch )) {
            echo 'Error in sending curl Request';
            exit ();
        }
        curl_close ( $ch );
        $currentTimeInMillis = round( microtime( true ) * 1000 );
        return empty( $content ) ? number_format($currentTimeInMillis, 0, '', ''): $content;
    }

	function check_customer_valid(){
		$url = get_option('mo_openid_host_name') . '/moas/api/customer/license';
		$ch = curl_init($url);
		$customerKey = get_option('mo_openid_admin_customer_key');
		$apiKey =  get_option('mo_openid_admin_api_key');

		$username = get_option('mo_openid_admin_email');
		$phone = get_option('mo_openid_admin_phone');
		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = self::get_timestamp();
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		/* Creating the Hash using SHA-512 algorithm */
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;
		$fields = array(
					   'customerId' => $customerKey,
					   'applicationName' => 'wp_social_login'
				);
		$field_string = json_encode($fields);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
											$timestampHeader, $authorizationHeader));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec($ch);

		if(curl_errno($ch)){
			echo 'Request Error:' . curl_error($ch);
		   exit();
		}
		curl_close($ch);
		return $content;
	}

	function validate_otp_token($transactionId,$otpToken){
		$url = get_option('mo_openid_host_name') . '/moas/api/auth/validate';
		$ch = curl_init($url);

		$customerKey =  $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$username = get_option('mo_openid_admin_email');

		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = self::get_timestamp();
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;

		$fields = '';

			//*check for otp over sms/email
			$fields = array(
				'txId' => $transactionId,
				'token' => $otpToken,
			);

		$field_string = json_encode($fields);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
											$timestampHeader, $authorizationHeader));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec($ch);

		if(curl_errno($ch)){
			echo 'Request Error:' . curl_error($ch);
		   exit();
		}
		curl_close($ch);
		return $content;
	}

    function mo_openid_send_email_alert($email,$phone,$message){

        // $hostname = Utilities::getHostname();
        $hostname 	= get_site_option('mo_openid_host_name') ;
        $url =  $hostname.'/moas/api/notify/send';
        $ch = curl_init($url);

        // $customer_details = Utilities::getCustomerDetails();
        $customerKey = $this->defaultCustomerKey;
        $apiKey = $this->defaultApiKey;

        $currentTimeInMillis= round(microtime(true) * 1000);
        $stringToHash 		= $customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue 			= hash("sha512", $stringToHash);
        $customerKeyHeader 	= "Customer-Key: " . $customerKey;
        $timestampHeader 	= "Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader= "Authorization: " . $hashValue;
        $fromEmail 			= $email;
        $subject            = "MiniOrange Social Login Plugin Feedback: ".$email;
        $site_url=site_url();

        $user= get_userdata(get_current_user_id());


        $query =" MiniOrange Social Login [Free] ";
        $content='<div >Hello, <br><br>First Name :'.$user->user_firstname.'<br><br>Last  Name :'.$user->user_lastname.'   <br><br>Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>Phone Number :'.$phone.'<br><br><b>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a></b><br><br><b>Plugin Deactivated: '.$query.'</b><br><br><b>Reason: '.$message.'</b></div>';


        $fields = array(
            'customerKey'	=> $customerKey,
            'sendEmail' 	=> true,
            'email' 		=> array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $fromEmail,
                'bccEmail' 		=> 'socialloginsupport@miniorange.com',
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> 'socialloginsupport@miniorange.com',
                'toName' 		=> 'socialloginsupport@miniorange.com',
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);


        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);

        if(curl_errno($ch)){
            return json_encode(array("status"=>'ERROR','statusMessage'=>curl_error($ch)));
        }
        curl_close($ch);

        return ($content);
    }

	function submit_contact_us( $email, $phone, $query ) {
		global $current_user;
		$current_user = wp_get_current_user();
		$company = get_option('mo_openid_admin_company_name') ? get_option('mo_openid_admin_company_name') : '';
		$first_name = get_option('mo_openid_admin_first_name') ? get_option('mo_openid_admin_first_name') : '';
		$last_name = get_option('mo_openid_admin_last_name') ? get_option('mo_openid_admin_last_name') : '';
		$query = '[WP OpenID Connect Login Free Plugin] ' . $query;
		$fields = array(
			'firstName'			=> $first_name,
			'lastName'	 		=> $last_name,
			'company' 			=> $company,
			'email' 			=> $email,
			'phone'				=> $phone,
			'query'				=> $query
		);
		$field_string = json_encode( $fields );

		$url = get_option('mo_openid_host_name') . '/moas/rest/customer/contact-us';

		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF-8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec( $ch );

		if( curl_errno( $ch ) ){
			echo 'Request Error:' . curl_error( $ch );
			return false;
		}
		//echo " Content: " . $content;

		curl_close( $ch );

		return true;
	}
	
	function forgot_password($email){
		
		$url = get_option('mo_openid_host_name') . '/moas/rest/customer/password-reset';
		$ch = curl_init($url);
		
		/* The customer Key provided to you */
		$customerKey = get_option('mo_openid_admin_customer_key');
	
		/* The customer API Key provided to you */
		$apiKey = get_option('mo_openid_admin_api_key');
	
		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = self::get_timestamp();
        $stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash("sha512", $stringToHash);
	
		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;
		
		$fields = '';
	
		//*check for otp over sms/email
		$fields = array(
			'email' => $email
		);
		
		$field_string = json_encode($fields);
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader, 
											$timestampHeader, $authorizationHeader));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt( $ch, CURLOPT_TIMEOUT, 20);
		$content = curl_exec($ch);
		
		if(curl_errno($ch)){
			return null;
		}
		curl_close($ch);
		return $content;
	}
}?>