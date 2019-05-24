<?php
namespace Drupal\uc_clickandpledge\Form;
//namespace Drupal\commerce_cnp\Form;  
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  
use Symfony\Component\HttpFoundation\RedirectResponse;

class CnPUberAuth extends ConfigFormBase
{
    /**  
    * {@inheritdoc}  
    */ 
   protected function getEditableConfigNames() {
       return [  
          'cnpuber.settings' 
        ]; 
   }
   /*
    * {@inheritdoc}
    */

	
	
   public function getFormId() {
       return "cnpuber_settings_form";
   }
   public function buildForm(array $form, FormStateInterface $form_state) {
     
    $config=$this->config("cnpuber.settings");
    
    $form=$this->displayBasicForm($form, $config, $form_state);

    return parent::buildForm($form, $form_state);  
   }
  
    /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      /*if (strlen($form_state->getValue('candidate_number')) < 10) {
        $form_state->setErrorByName('candidate_number', $this->t('Mobile number is too short.'));
      }*/
       $config=$this->config("cnpuber.settings");
	   if (!$this->is_valid_email(trim($form_state->getValue('cnp_vemail')))) {
            $form_state->setErrorByName('cnp_vemail', $this->t('Please enter valid Email.'));
        }
	
	
	   if(trim($form_state->getValue('cnp_verify_code'))!="")
	   {
		   $pat='/^\d{5}$/';
		   if(!preg_match($pat,trim($form_state->getValue('cnp_verify_code'))))
		   {
			   $form_state->setErrorByName('cnp_verify_code', $this->t('Enter valid verification code'));
		   }
	   }
	   else
	   {
			if($config->get('cnpuber.cnp_vemail')!="")
			{
				$form_state->setErrorByName('cnp_verify_code', $this->t('Enter valid verification code'));
			}
	   }
	   
       if(trim($form_state->getValue('cnp_verify_code'))!="" && trim($form_state->getValue('cnp_vemail'))!="")
       {
           $data=$this->getCnPTransactions(trim($form_state->getValue('cnp_vemail')),trim($form_state->getValue('cnp_verify_code')));
			if($data=="error")
			{
				$form_state->setErrorByName('cnp_verify_code', $this->t('Please enter verification code.'));
				
			}
       }
      
        
        
    }
	public function is_valid_email($email) 
	{ 
		return preg_match('/^(([^<>()[\]\\.,;:\s@"\']+(\.[^<>()[\]\\.,;:\s@"\']+)*)|("[^"\']+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $email); 
	}
   
   public function submitForm(array &$form, FormStateInterface $form_state) {
        parent::submitForm($form, $form_state);
        $cnp_verify_code=($form_state->getValue('cnp_verify_code'))?$form_state->getValue('cnp_verify_code'):"";
        $cnp_vemail=($form_state->getValue('cnp_vemail'))?$form_state->getValue('cnp_vemail'):"";
        
		$cnp_verify_code=trim($cnp_verify_code);
		$cnp_vemail=trim($cnp_vemail);
		
        if($form_state->getValues('cnp_vemail')!="")
        {
            if(!$cnp_verify_code)
            {
            $vemail=$form_state->getValues()['cnp_vemail'];
            $curl = curl_init();
            $cnpemailaddress = $vemail;
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.cloud.clickandpledge.com/users/requestcode",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "email: ".$cnpemailaddress
                ),
            ));

            $response = curl_exec($curl);
            $this->config('cnpuber.settings')  
            ->set('cnpuber.cnp_vemail', trim($form_state->getValue('cnp_vemail')))
            ->set('cnpuber.cnp_verify_code', trim($cnp_verify_code))
            ->save(); 
            $err = curl_error($curl);
            //drupal_set_message($err);
            drupal_set_message(t($err), 'status',false);
            curl_close($curl);
            }
        }
        if($cnp_verify_code!="" && $cnp_vemail!="")
        {
            $this->config('cnpuber.settings')  
            ->set('cnpuber.cnp_vemail', trim($form_state->getValue('cnp_vemail')))
            ->set('cnpuber.cnp_verify_code', trim($cnp_verify_code))
            ->save(); 
            $this->my_goto("cnpuber_settings");
        }
       
       /* foreach ($form_state->getValues() as $key => $value) {
           drupal_set_message($key . ': ' . $value); 
        }*/
       //drupal_set_message("Verification code sent to Email. please check");
       
   }
   public function displayBasicForm($form,$config, $form_state)
   {
	    //$form['#attached']['library'][] = 'uc_clickandpledge/cnp_uc_credit.styles';
		$connection= \Drupal::database();
		$prefix=$connection->tablePrefix();
        $table_name = $prefix.'dp_cnp_uber_jbcnpaccountsinfo';
		$sql = "SELECT * FROM " .$table_name;
        $query = $connection->query($sql);
		$query->allowRowCount = TRUE;
		
		
		//logo display
		$cnpalogo="<img src='".base_path().drupal_get_path('module', 'uc_clickandpledge')."/images/cnp_logo.png'>";
		$form['cnp_alogo'] = array(
			'#prefix' => '<div class="cnp_dlogo"> '.$cnpalogo,
			'#suffix' => '</div>',
		);
		
	   $form['heading_text_start'] = array(
            '#markup' => '<div>
			<p>Click & Pledge works by adding credit card fields on the checkout and then sending the details to Click & Pledge for verification.</p>
			<ol>
				<li>Enter the email address associated with your Click & Pledge account, and click on Get the Code</li>
				<li>Please check your email inbox for the Login Verification Code email</li>
				<li>Enter the provided code and click Login</li>
			</ol>
			</div>'
			
        );
		
		
		if ($query->rowCount()!=0) {
			$form['heading_text'] = array(
				'#markup' => '<div><img src="" height="" width=""/>
				<a class="button" href="cnpuber_settings">Go to settings</a></div><br><hr/>'
			);
		}
		
		
	   
	   
       $form['cnp_vemail'] = [  
        '#type' => 'textfield',  
        '#title' => $this->t(''),  
        '#description' => $this->t('Enter Connect User Name'),  
        '#default_value' => $config->get('cnpuber.cnp_vemail'),  
        ]; 
		
        if($config->get('cnpuber.cnp_vemail')!="")
		{
			$form['cnp_verify_code'] = [  
				'#type' => 'textfield', 
				'#attributes'=>array("id"=>"verifycode"),
				'#title' => $this->t(''),  
				'#description' => $this->t('Please enter the code sent to your email'),  
				'#default_value' => $config->get('cnpuber.cnp_verify_code'),  
			]; 
			
			$form['swdu_text'] = array(
				'#markup' => '<div class="signin_with_diff_user">
				<a class="" href="different_user_signin">Sign in with a different account</a></div>'
			);
			
		}
		
        return $form;
   }
   
   
    public function my_goto($path) { 
     $response = new RedirectResponse($path, 302);
     $response->send();
     return;
    }
    public function getCnPTransactions($cnpemailid,$cnpcode)
    {   
          $curl = curl_init();
          curl_setopt_array($curl, array(
          CURLOPT_URL => "https://aaas.cloud.clickandpledge.com/idserver/connect/token",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $this->get_cnpwctransactions($cnpemailid,$cnpcode),
          CURLOPT_HTTPHEADER => array(
              "cache-control: no-cache",
              "content-type: application/x-www-form-urlencoded"

            ),
          ));

          $response = curl_exec($curl);
          $err = curl_error($curl);
		  //return $response;
          curl_close($curl);
          if ($err) {
            echo "cURL Error #:" . $err;
          } else {
          $cnptokendata = json_decode($response);
			
           if(!isset($cnptokendata->error)){
			 
              $cnptoken = $cnptokendata->access_token;
              $cnprtoken = $cnptokendata->refresh_token;
              $cnptransactios = $this->delete_cnpwctransactions();
              $rtncnpdata =  $this->insrt_cnpwctokeninfo($cnpemailid,$cnpcode,$cnptoken,$cnprtoken);    

              if($rtncnpdata != "")
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
                  "authorization: Bearer ".$cnptoken,
                  "content-type: application/json"),
                    ));

                  $response = curl_exec($curl);
                  $err = curl_error($curl);
                  curl_close($curl);

                  if ($err) {
                    echo "cURL Error #:" . $err;
                  } else {

                      $cnpAccountsdata = json_decode($response);

                      $cnptransactios = $this->delete_wccnpaccountslist();

                      foreach($cnpAccountsdata as $cnpkey =>$cnpvalue)
                      {
                       $cnporgid = $cnpvalue->OrganizationId;
                       $cnporgname = addslashes($cnpvalue->OrganizationName);
                       $cnpaccountid = $cnpvalue->AccountGUID;
                       $cnpufname = addslashes($cnpvalue->UserFirstName);
                       $cnplname = addslashes($cnpvalue->UserLastName);
                       $cnpuid = $cnpvalue->UserId;
                      $cnptransactios = $this->insert_cnpwcaccountsinfo($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid);    

                      }
                      //print_r($cnpAccountsdata);
                     return "success";
                  }
              }
              }else{
                  return "error";
              }

          }
    }
    public function get_cnpwctransactions($cnpemailid,$cnpcode)
    {
        $database = \Drupal::database();
        $prefix=$database->tablePrefix();
        $table_name = $prefix.'dp_cnp_uber_jbcnpsettingsinfo';
        $sql = "SELECT * FROM ". $table_name;
        $query = $database->query($sql);
        $results = $query->fetchAssoc();

        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
             $password="password";
             $cnpsecret = openssl_decrypt($results['cnpsettingsinfo_clentsecret'],"AES-128-ECB",$password);
             $rtncnpdata = "client_id=".$results['cnpsettingsinfo_clientid']."&client_secret=". $cnpsecret."&grant_type=".$results['cnpsettingsinfo_granttype']."&scope=".$results['cnpsettingsinfo_scope']."&username=".$cnpemailid."&password=".$cnpcode;
        }

        return $rtncnpdata;
    }
    public function delete_cnpwctransactions(){
        $database = \Drupal::database();
        $prefix=$database->tablePrefix();
        $table_name = 'dp_cnp_uber_jbcnptokeninfo';
        $database->delete($table_name)->execute();
    }
    public function insrt_cnpwctokeninfo($cnpemailid,$cnpcode,$cnptoken,$cnprtoken)
    {
        $database = \Drupal::database();
        $prefix=$database->tablePrefix();
        $table_name = "dp_cnp_uber_jbcnptokeninfo";
		$fields = array(
			'cnptokeninfo_username' => $cnpemailid, 
			'cnptokeninfo_code' => $cnpcode, 
			'cnptokeninfo_accesstoken' => $cnptoken,
			'cnptokeninfo_refreshtoken' => $cnprtoken,
		);
		
        $result =  $database->insert($table_name)->fields($fields)->execute();
		
        //$id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
        $res=$database->query("select max(cnptokeninfo_id) from ".$prefix.$table_name);
        $id=$res->fetchCol();	
        return $id[0];
    }
    public function delete_wccnpaccountslist()
    {
        $database = \Drupal::database();
        $prefix=$database->tablePrefix();
        $table_name = 'dp_cnp_uber_jbcnpaccountsinfo';
        $database->delete($table_name)->execute();
    }
    public function insert_cnpwcaccountsinfo($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid)
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
}