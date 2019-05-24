<?php 
namespace Drupal\uc_clickandpledge\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \SoapClient;
use SimpleXMLElement;



class CnPUberController extends ControllerBase
{
  // $variable is the wildcard from the route
	public function ajaxCallback($variable)
	{
		$data=$this->getWCCnPactivePaymentList($variable);
		//$accData=$this->cnp_getCnPUserEmailAccountList($variable);
		//$response['data'] = 'Some test data to return '.$variable;
		//$response['data'] = $data;
		
		return new JsonResponse( $data );
	}
	public function getWCCnPactivePaymentList($cnpaccid)
	{
		$cmpacntacptdcards = "";
		$cnpacountid = $cnpaccid;
		$cnpaccountGUID = $this->getwcCnPAccountGUID($cnpacountid);
		$cnpUID = "14059359-D8E8-41C3-B628-E7E030537905";
		$cnpKey = "5DC1B75A-7EFA-4C01-BDCD-E02C536313A3";
		$connect1  = array('soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 0);
		$client1   = new SoapClient('https://resources.connect.clickandpledge.com/wordpress/Auth2.wsdl', $connect1);
		if( isset($cnpacountid) && $cnpacountid !="" && isset($cnpaccountGUID) &&  $cnpaccountGUID !="")
		{ 
			$xmlr1  = new SimpleXMLElement("<GetAccountDetail></GetAccountDetail>");
			$xmlr1->addChild('accountId',$cnpacountid);
			$xmlr1->addChild('accountGUID',$cnpaccountGUID);
			$xmlr1->addChild('username',$cnpUID);
			$xmlr1->addChild('password',$cnpKey);
			$response1                    =  $client1->GetAccountDetail($xmlr1);
			$responsearramex              =  $response1->GetAccountDetailResult->Amex;
			$responsearrJcb               =  $response1->GetAccountDetailResult->Jcb;
			$responsearrMaster            =  $response1->GetAccountDetailResult->Master;
			$responsearrVisa              =  $response1->GetAccountDetailResult->Visa;
			$responsearrDiscover          =  $response1->GetAccountDetailResult->Discover;
			$responsearrecheck            =  $response1->GetAccountDetailResult->Ach;
			$responsearrCustomPaymentType =  $response1->GetAccountDetailResult->CustomPaymentType;
			return $response1;
		} 
	}
    public function getwcCnPAccountGUID($accid)
    {
       
        $database = \Drupal::database();
        $prefix=$database->tablePrefix();
        $table_name = $prefix.'dp_cnp_uber_jbcnpaccountsinfo';
        $cnpAccountGUId ="";
        $sql = "SELECT * FROM " . $table_name." where cnpaccountsinfo_orgid ='".$accid."'";
        $query = $database->query($sql);
        $result = $query->fetchAssoc();
        //print_r($result);
        $count = sizeof($result);
        $cnpAccountGUId  = $result['cnpaccountsinfo_accountguid'];
        return $cnpAccountGUId;

    }
    public function cnp_getCnPUserEmailAccountList($cnpacid) {
        $cnpwcaccountid = $cnpacid;
        //$paymntgtcls = new WC_Gateway_ClickandPledge();
        $totRes=array();
        
       $cnprtrntxt = $this->getwcCnPConnectCampaigns($cnpwcaccountid);
	   $totRes[]=$cnprtrntxt;
       //return $cnprtrntxt;
        $Clist=array();
       $cnprtrnpaymentstxt = $this->getWCCnPactivePaymentList($cnpwcaccountid);
	  // print_r($cnprtrntxt);
	   foreach($cnprtrnpaymentstxt as $obj=>$cli)
	   {
		   foreach($cli as $key=>$value)
		   {
			   if($value==1)
			   {
				   $Clist[$key]=$key;
			   }
		   }
	   }
	  $totRes[]=$Clist;
	  return $totRes;
       //return $allResponse=$cnprtrntxt."||".$cnprtrnpaymentstxt;
       //echo $cnprtrntxt."||".$cnprtrnpaymentstxt;
     // die();
    }
    public function getwcCnPConnectCampaigns($cnpaccid)
    {
        
        $cnpacountid = $cnpaccid;
        $cnpaccountGUID = $this->getwcCnPAccountGUID($cnpacountid);
        //print_r($cnpaccountGUID);
        $cnpUID = "14059359-D8E8-41C3-B628-E7E030537905";
        $cnpKey = "5DC1B75A-7EFA-4C01-BDCD-E02C536313A3";
        $connect  = array('soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 0);
        $default = array( 
            // We shall only enable TRACING & EXCEPTION for dev 
            'trace' => 1, 
            'exceptions' => true, 
            'cache_wsdl' => WSDL_CACHE_NONE, 
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        );
        $client= new SoapClient("https://resources.connect.clickandpledge.com/wordpress/Auth2.wsdl",$connect);
        if( isset($cnpacountid) && $cnpacountid !="" && isset($cnpaccountGUID) &&  $cnpaccountGUID !="")
        { 
            $xmlr  = new SimpleXMLElement("<GetActiveCampaignList2></GetActiveCampaignList2>");
			
            $cnpsel ="";
            $xmlr->addChild('accountId', $cnpacountid);
            $xmlr->addChild('AccountGUID', $cnpaccountGUID);
            $xmlr->addChild('username', $cnpUID);
            $xmlr->addChild('password', $cnpKey);
			
            $response = $client->GetActiveCampaignList2($xmlr); 
            $responsearr =  $response->GetActiveCampaignList2Result->connectCampaign;
			
        }
		else
		{
			$responsearr=(object)array(""=>"No Campaigns Found");
		}
       return $responsearr;
            
    }
	public function getRefreshAccounts($variable)
	{
		
		$data=array("Welcome"=>$variable);
		$rtnrefreshtokencnpdata = $this->getRefreshToken();
		//echo $rtnrefreshtokencnpdata;
		$cnpwcaccountid = $variable;
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => "https://aaas.cloud.clickandpledge.com/IdServer/connect/token",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $rtnrefreshtokencnpdata,
		CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/x-www-form-urlencoded"

		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
			$cnptokendata = json_decode($response);
			
			$cnptoken = $cnptokendata->access_token;
			$cnprtokentyp = $cnptokendata->token_type;
			if($cnptoken != "")
			{
				$curl = curl_init();

			  curl_setopt_array($curl, array(
  			  CURLOPT_URL => "https://api.cloud.clickandpledge.com/users/accountlist",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: ".$cnprtokentyp." ".$cnptoken,
				"content-type: application/json"),
			  	));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				  
					$cnpAccountsdata = json_decode($response);
					
					$camrtrnval = "";
					$rtncnpdata = $this->delete_cnpaccountslist();
					
					
					  $confaccno 	 =  $variable;	
					$totalAccounts=array();
					foreach($cnpAccountsdata as $cnpkey =>$cnpvalue)
					{
						$acc=array();
						$selectacnt ="";
						 //data come form service-insert data into accounts list table
						$cnporgid = $cnpvalue->OrganizationId;
						$cnporgname = addslashes($cnpvalue->OrganizationName);
						$cnpaccountid = $cnpvalue->AccountGUID;
						$cnpufname = addslashes($cnpvalue->UserFirstName);
						$cnplname = addslashes($cnpvalue->UserLastName);
						$cnpuid = $cnpvalue->UserId;
						$rtncnpdata = $this->insert_cnpwcaccountslist($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid);
						 /*if($confaccno == $cnporgid){$selectacnt ="selected='selected'";}
							 $camrtrnval .= "<option value='".$cnporgid."' ".$selectacnt.">".$cnporgid." [".$cnpvalue->OrganizationName."]</option>";*/
						$acc["orgid"]=$cnporgid;
						$acc["orgname"]=$cnporgname;
						$totalAccounts[]=$acc;
					}
					
					
					
					}
					//print_r($cnpAccountsdata);
				   
				}
			}
		
		return new JsonResponse( $totalAccounts );
	}
	public function getRefreshToken()
	{
		$database = \Drupal::database();
        $prefix=$database->tablePrefix();
		//refresh tokeinfo
        $table_name = $prefix.'dp_cnp_uber_jbcnptokeninfo';
        $cnpAccountGUId ="";
        $sql = "SELECT * FROM " . $table_name;
        $query = $database->query($sql);
        $result = $query->fetchAssoc();
		$refreshtoken=$result['cnptokeninfo_refreshtoken'];
		//settings table data
		$table_name1 = $prefix.'dp_cnp_uber_jbcnpsettingsinfo';
		$sql1 = "SELECT * FROM " . $table_name1;
		$query1 = $database->query($sql1);
		$result1 = $query1->fetchAssoc();
		$count = sizeof($result1);
		$password="password";
		$cnpsecret = openssl_decrypt($result1['cnpsettingsinfo_clentsecret'],"AES-128-ECB",$password);
		$rtncnpdata = "client_id=".$result1['cnpsettingsinfo_clientid']."&client_secret=". $cnpsecret."&grant_type=refresh_token&scope=".$result1['cnpsettingsinfo_scope']."&refresh_token=".$refreshtoken;
		return $rtncnpdata;
	}
	public function delete_cnpaccountslist()
    {
        $database = \Drupal::database();
        $prefix=$database->tablePrefix();
        $table_name = 'dp_cnp_uber_jbcnpaccountsinfo';
        $database->delete($table_name)->execute();
    }
	public function insert_cnpwcaccountslist($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid)
    {
        $database = \Drupal::database();
        $prefix=$database->tablePrefix();
        $table_name = 'dp_cnp_uber_jbcnpaccountsinfo';
        $result = $database->insert($table_name)
        ->fields([
            'cnpaccountsinfo_orgid' => $cnporgid, 
            'cnpaccountsinfo_orgname' => $cnporgname, 
            'cnpaccountsinfo_accountguid' => $cnpaccountid,
            'cnpaccountsinfo_userfirstname' => $cnpufname,
            'cnpaccountsinfo_userlastname'=> $cnplname,
            'cnpaccountsinfo_userid'=> $cnpuid
        ])
        ->execute();
        //$id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
        $res=$database->query("select max(cnpaccountsinfo_id) from ".$prefix.$table_name);
        $id=$res->fetchCol();	
        return $id[0];
    }
	public function signinDifferentUser()
	{
		\Drupal::configFactory()->getEditable('cnpuber.settings')->delete();
		$this->my_goto("cnpauth");
	}
	 public function my_goto($path) { 
     $response = new RedirectResponse($path, 302);
     $response->send();
     return;
    }
}

?>