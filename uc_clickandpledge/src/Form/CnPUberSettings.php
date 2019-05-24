<?php
namespace Drupal\uc_clickandpledge\Form;
use \SoapClient;
use SimpleXMLElement;
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\HttpFoundation\RedirectResponse;



class CnPUberSettings extends ConfigFormBase
{
    /**  
    * {@inheritdoc}  
    */ 
   public $connection;
    public function __construct(\Drupal\Core\Config\ConfigFactoryInterface $config_factory) {
        parent::__construct($config_factory);
        $connection= \Drupal::database();
		$prefix=$connection->tablePrefix();
        $table_name = $prefix.'dp_cnp_uber_jbcnpaccountsinfo';
		$sql = "SELECT * FROM " .$table_name;
        $query = $connection->query($sql);
		$query->allowRowCount = TRUE;
		if ($query->rowCount()==0) {
			 $this->my_goto("cnpauth");
		}
		
		/*$query = \Drupal::database()->delete('key_value')
        ->condition('collection', 'system.schema')
        ->condition('name', 'welcome_module')
        ->execute();
		var_dump($query);*/
    }
   protected function getEditableConfigNames() {
       return [  
          'cnpuber.mainsettings' 
        ]; 
   }
   
   /**
   * {@inheritdoc}
   */
  /*public function defaultConfiguration() {
    return [
      'cnp_status_enable' => '',
      'cnp_title' => '',
      'cnp_desc' => '',
      'cnp_accid' => '',
      'cnp_camp_urls' => '',
      'cnp_mode' => '',
      'cnp_payment_credit_cards' => '',
      'cnp_payment_credit_card_options_hidden' => '',
      'cnp_pre_auth' => '',
      'cnp_receipt_header' => '',
      'cnp_terms_con' => '',
      'cnp_recurr_label' => '',
      'cnp_recurr_settings' => '',
      'cnp_recurr_oto' => '',
      'cnp_recurr_recur' => '',
      'cnp_default_payment_options' => '',
      'cnp_recurring_types' => '',
      'cnp_recurr_type_option' => '',
      'cnp_default_recurring_type' => '',
      'cnp_recurring_periodicity' => '',
      'cnp_recurring_periodicity_options' => '',
      'cnp_recurring_no_of_payments' => '',
      'cnp_recurring_no_of_payments_options' => '',
      'cnp_recurring_default_no_payment_lbl' => '',
      'cnp_recurring_default_no_payments' => '',
      'cnp_recurring_max_no_payment_lbl' => '',
      'cnp_recurring_max_no_payment' => '',
      'cnp_recurring_default_no_payment_open_field_lbl' => '',
      'cnp_recurring_default_no_payments_open_filed' => '',
      'cnp_recurring_max_no_payment_open_filed_lbl' => '',
      'cnp_recurring_max_no_payment_open_filed' => '',
      'cnp_recurring_default_no_payment_fncc_lbl' => '',
      'cnp_recurring_default_no_payments_fnnc' => '',
    ] + parent::defaultConfiguration();
  }
  */
   /*
    * {@inheritdoc}
    */
   public function getFormId() {
       return "cnp_main_settings";
   }
   public function buildForm(array $form, FormStateInterface $form_state) {
    \Drupal::configFactory()->getEditable('cnpuber.settings')->delete();
    $config=$this->config("cnpuber.mainsettings");
   
  
    $form=$this->displaySettingsForm($form, $config,$form_state);
    return parent::buildForm($form, $form_state);  
   }
  
    /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {
    
    }
   
    public function my_goto($path) { 
     $response = new RedirectResponse($path, 302);
     $response->send();
     return;
    }
   
   public function submitForm(array &$form, FormStateInterface $form_state) {
        parent::submitForm($form, $form_state);
		 $this->config('cnpuber.mainsettings')  
          ->set('cnpuber.cnp_status_enable', $form_state->getValue('cnp_status_enable'))
           //->set('cnpuber.cnp_title', trim($form_state->getValue('cnp_title')))
           //->set('cnpuber.cnp_desc', trim($form_state->getValue('cnp_desc')))
           ->set('cnpuber.cnp_accid', $form_state->getValue('cnp_accid'))
           ->set('cnpuber.cnp_camp_urls', $form_state->getValue('cnp_camp_urls'))
           ->set('cnpuber.cnp_mode', $form_state->getValue('cnp_mode'))
           ->set('cnpuber.cnp_payment_credit_cards', $form_state->getValue('cnp_payment_credit_cards'))
           //->set('cnpuber.cnp_payment_credit_card_options', $form_state->getValue('cnp_payment_credit_card_options'))
           ->set('cnpuber.cnp_payment_credit_card_options_hidden', $form_state->getValue('cnp_payment_credit_card_options_hidden'))
           //->set('cnpuber.cnp_payment_echeck', $form_state->getValue('cnp_payment_echeck'))
           //->set('cnpuber.cnp_payment_echeck_hidden', $form_state->getValue('cnp_payment_echeck_hidden'))
           //->set('cnpuber.cnp_payment_methdos1', $form_state->getValue('cnp_payment_methdos1'))
           //->set('cnpuber.cnp_customPayment_titles', $form_state->getValue('cnp_customPayment_titles'))
           //->set('cnpuber.cnp_referenceNumber_label', $form_state->getValue('cnp_referenceNumber_label'))
           //->set('cnpuber.cnp_default_payment', $form_state->getValue('cnp_default_payment'))
           ->set('cnpuber.cnp_pre_auth', $form_state->getValue('cnp_pre_auth'))
           ->set('cnpuber.cnp_receipt_patron', $form_state->getValue('cnp_receipt_patron'))
           ->set('cnpuber.cnp_receipt_header', trim($form_state->getValue('cnp_receipt_header')))
           ->set('cnpuber.cnp_terms_con', trim($form_state->getValue('cnp_terms_con')))
           ->set('cnpuber.cnp_recurr_label', trim($form_state->getValue('cnp_recurr_label')))
           ->set('cnpuber.cnp_recurr_settings', trim($form_state->getValue('cnp_recurr_settings')))
           ->set('cnpuber.cnp_recurr_oto', $form_state->getValue('cnp_recurr_oto'))
           ->set('cnpuber.cnp_recurr_recur', $form_state->getValue('cnp_recurr_recur'))
           ->set('cnpuber.cnp_default_payment_options', $form_state->getValue('cnp_default_payment_options'))
           ->set('cnpuber.cnp_recurring_types', trim($form_state->getValue('cnp_recurring_types')))
           ->set('cnpuber.cnp_recurr_type_option', $form_state->getValue('cnp_recurr_type_option'))
           ->set('cnpuber.cnp_default_recurring_type', $form_state->getValue('cnp_default_recurring_type'))
           ->set('cnpuber.cnp_recurring_periodicity', trim($form_state->getValue('cnp_recurring_periodicity')))
           ->set('cnpuber.cnp_recurring_periodicity_options', $form_state->getValue('cnp_recurring_periodicity_options'))
           ->set('cnpuber.cnp_recurring_no_of_payments', trim($form_state->getValue('cnp_recurring_no_of_payments')))
           ->set('cnpuber.cnp_recurring_no_of_payments_options', $form_state->getValue('cnp_recurring_no_of_payments_options'))
           ->set('cnpuber.cnp_recurring_default_no_payment_lbl', trim($form_state->getValue('cnp_recurring_default_no_payment_lbl')))
           ->set('cnpuber.cnp_recurring_default_no_payments', trim($form_state->getValue('cnp_recurring_default_no_payments')))
           ->set('cnpuber.cnp_recurring_max_no_payment_lbl', trim($form_state->getValue('cnp_recurring_max_no_payment_lbl')))
           ->set('cnpuber.cnp_recurring_max_no_payment', trim($form_state->getValue('cnp_recurring_max_no_payment')))
           ->set('cnpuber.cnp_recurring_default_no_payment_open_field_lbl', trim($form_state->getValue('cnp_recurring_default_no_payment_open_field_lbl')))
           ->set('cnpuber.cnp_recurring_default_no_payments_open_filed', trim($form_state->getValue('cnp_recurring_default_no_payments_open_filed')))
           ->set('cnpuber.cnp_recurring_max_no_payment_open_filed_lbl', trim($form_state->getValue('cnp_recurring_max_no_payment_open_filed_lbl')))
           ->set('cnpuber.cnp_recurring_max_no_payment_open_filed', trim($form_state->getValue('cnp_recurring_max_no_payment_open_filed')))
           ->set('cnpuber.cnp_recurring_default_no_payment_fncc_lbl', trim($form_state->getValue('cnp_recurring_default_no_payment_fncc_lbl')))
           ->set('cnpuber.cnp_recurring_default_no_payments_fnnc', trim($form_state->getValue('cnp_recurring_default_no_payments_fnnc')))
          ->save();
		/*foreach ($form_state->getValues() as $key => $value) {
		  drupal_set_message($key . ': ' . $value);
		};*/
    }
   //create cnp payment setting form
   public function displaySettingsForm($form,$config,$form_state)
   {
	   //$form['navlinks'] = array('#type' => 'link', '#title' => t('CnP Auth'), '#href' => 'cnpauth'); 
	   //print_r($config->get('cnpuber.cnp_camp_urls'));
	   
		//query to get loggedin user data
		$connection= \Drupal::database();
		$prefix=$connection->tablePrefix();
        $table_name = $prefix.'dp_cnp_uber_jbcnptokeninfo';
		$sql = "SELECT * FROM " .$table_name;
        $query = $connection->query($sql);
		$result = $query->fetchAll();
		$loggedinAs=$result[0]->cnptokeninfo_username;
		
		$form['cnp_bform_main_div_start']=[
			'#prefix' => '<div class="cnp_bform_main_div">',
        ];
		
		//logo display
		$cnpdlogo="<img src='".base_path().drupal_get_path('module', 'uc_clickandpledge')."/images/cnp_logo.png'>";
		$form['cnp_dlogo'] = array(
			'#prefix' => '<div class="cnp_dlogo"> '.$cnpdlogo,
			'#suffix' => '</div>',
		);
		
		
	   $form['heading_text'] = array(
            '#markup' => '<div>
			<p>Click & Pledge works by adding credit card fields on the checkout and then sending the details to Click & Pledge for verification.</p>
			<p>You are logged in as <b>['.$loggedinAs.']</b></p>
			<a class="button" href="cnpauth">Change User</a></div><br><hr/>'
        );
		$form['base_url_cnp'] = [
			'#type' => 'hidden',
			'#default_value' => base_path(),
			'#attributes' => array("id"=>"base_url_cnp"),
		];
	


	   $form['cnp_status_enable']=[
            "#type"=> "checkboxes",
			"#title"=>"Status",
			'#attributes' => array('id' => 'enable-disable-cnp-payment-gateway'),
            "#options"=> array("yes"=>"Enable Click & Pledge"),
            '#default_value' => ($config->get('cnpuber.cnp_status_enable')) ? $config->get('cnpuber.cnp_status_enable') : [],
			'#prefix' => '<div class="container-inline cnp_bform_status_check">',
			'#suffix' => '</div>',
        ];
		
		/*$form['cnp_title'] = [  
            '#type' => 'textfield',  
			"#attributes"=>array(""=>"myclass"),
            '#title' => $this->t('Title'), 
            '#default_value' => $config->get('cnpuber.cnp_title'),
            "#required"=> TRUE,
			'#prefix' => '<div class="cnp_bform_title">',
			'#suffix' => '</div>',
        ];
        $form['cnp_desc'] = [  
            '#type' => 'textfield',  
            '#title' => $this->t('Description'), 
            '#default_value' => $config->get('cnpuber.cnp_desc'),
			'#prefix' => '<div class="cnp_bform_desc">',
			'#suffix' => '</div>',
        ];*/
		$form["hs_div_start"]=[
			'#prefix' => '<div id="cnp_hs_div">',
		];
		
		$form['cnp_accid'] = [
        '#type' => 'select',
		"#required"=> TRUE,
        '#title' => t('C&P Account ID'),
		'#default_value' => $config->get('cnpuber.cnp_accid'),
		'#prefix' => '<div id="cnp_accid_wrapper" class="cnp_bform_accid">',
        '#suffix' => '</div>',
        '#options' => $this->getAccountIDs(),
		'#ajax' => [
				  'callback' => [$this, 'changeOptionsAjax'],
				  'wrapper' => 'cnp_camp_urls_wrapper',
			],
		
		];
		$form['cnp_accid_hidden'] = [
			"#type"=>"hidden",
			"#attributes"=>array("id"=>"cnp_accid_hidden"),
			'#default_value' => $config->get('cnpuber.cnp_accid'),
		];
		/*$form['signup_connect_with_youtube_submit'] = array(
			'#type' => 'button',
			'#value' => 'Refresh Accounts',
			'#ajax' => [
				  'callback' => [$this, 'RefreshAccounts()'],
				  'wrapper' => 'cnp_accid_wrapper',
			],
		);*/
		$form['refresh_accounts'] = array(
            '#markup' => '<a href="#" id="rfrshtokens">Refresh Accounts</a>',
        );
		//onload display campaign url of the selected account ID
		
		$form['cnp_camp_urls'] = [
        '#type' => 'select',
        '#title' => t('Connect Campaign URL Alias<div class="cnp_demo">
  <span data-tooltip="Transaction will post to this connect campaign.Receipts,stats are sent and updated based on the set campaign" class="cnp_tooltip">?</span></div>'),
		'#default_value' => $config->get('cnpuber.cnp_camp_urls'),
        '#options' => $this->getOptions($form_state,$config),
        '#prefix' => '<div id="cnp_camp_urls_wrapper" class="cnp_bform_camp_urls">',
        '#suffix' => '</div>',
		
		];
		$form['cnp_camp_urls_tooltip'] = [
			'#markup' => '',
		];
		$form['cnp_mode']=[
            "#type"=> "radios",
            "#title"=> $this->t("Transaction Mode"),
            "#options"=> array(
                    "Yes" => $this->t('Test'),
                    "No" => $this->t('Production'),
                ),
            "#default_value" => ($config->get('cnpuber.cnp_mode')!==null) ? $config->get('cnpuber.cnp_mode'):"No" ,
            '#prefix' => '<div class="container-inline cnp_bform_mode">',
            '#suffix' => '</div>',
        ];
		 //PAYMENT METHODS
		
		
		 
		 
		$form['cnp_payment_credit_cards']=[
            "#type"=> "checkboxes",
            "#title"=> $this->t("Payment Methods"),
			'#attributes' => array('checked' => 'checked',"disabled"=>"disabled"),
            "#options"=> array("Credit Cards"=>"Credit Cards"),
            '#default_value' => ($config->get('cnpuber.cnp_payment_credit_cards')) ? $config->get('cnpuber.cnp_payment_credit_cards') : [],
			'#prefix' => '<div class="container-inline cnp_bform_payment_cards">',
			'#suffix' => '</div>',
        ];
		
		// print_r($config->get('cnpuber.cnp_payment_credit_card_options'));
		//get the payment options once the page has loaded
		if($config->get('cnpuber.cnp_accid'))
		{
			$serviceOPtions=$this->getWCCnPactivePaymentList($config->get('cnpuber.cnp_accid'));
			$sOpt=array("Amex","Discover","Jcb","Master","Visa");
			if(!empty($serviceOPtions))
			{
			$OriginalOptions=array();
			$payOptions=$serviceOPtions->GetAccountDetailResult;
				foreach($payOptions as $k=>$v)
				{
					if(in_array($k,$sOpt))
					{
						if($v==1)
						{
							$OriginalOptions[$k]=$k;
						}
					}
				}
			}
			else
			{
				$OriginalOptions=array("Amex","Discover","Jcb","Master","Visa");
			}
			
		}
		else
		{
			$OriginalOptions=array("Amex","Discover","Jcb","Master","Visa");
		}
		//print_r($config->get('cnpuber.cnp_payment_credit_card_options'));
		$form['cnp_payment_credit_card_options']=[
            /*"#type"=> "checkboxes",
			
			'#attributes' => array('checked' => 'checked',"disabled"=>"disabled"),
            "#options"=> $OriginalOptions,
            '#default_value' => ($config->get('cnpuber.cnp_payment_credit_card_options')) ? $config->get('cnpuber.cnp_payment_credit_card_options') : [],*/
			//"#title"=>"Accepted Credit Cards",
			'#prefix' => '<div class="container-inline cnp_payment_options_wrapper" id="payment_options_wrapper">',
			'#suffix' => '</div>',
        ];
		
		$cards_hidden="";
		foreach($OriginalOptions as $op)
		{
			$cards_hidden.=$op."#";
		}
		$form['cnp_payment_credit_card_options_hidden']=[
			"#type"=> "hidden",
			"#attributes"=> array("id"=>"card_options_hidden"),
			'#default_value'=>$cards_hidden,
			'#prefix' => '<div class="" id="credit_card_options_hidden">',
			'#suffix' => '</div>',
		];
		
		$form['note_text'] = array(
            '#markup' => '<div>
			<p><b>Note:</b> Due to limitations with the Drupal Ubercart payment API, only Credit Card payment is currently supported.</p></div>'
        );
		
		/*$form['cnp_payment_echeck']=[
            "#type"=> "checkboxes",
			'#attributes' => array('checked' => 'checked',"disabled"=>"disabled"),
            "#options"=> array("eCheck"=>"eCheck"),
            '#default_value' => ($config->get('cnpuber.cnp_payment_echeck')) ? $config->get('cnpuber.cnp_payment_echeck') : [],
			'#prefix' => '<div class="container-inline" id="eCheck-wrapper">',
			'#suffix' => '</div>',
        ];*/
		/*if($config->get('cnpuber.cnp_payment_echeck'))
		{
			$opt=$config->get('cnpuber.cnp_payment_echeck');
			$opt=$opt['eCheck'];
		}
		else
		{
			$opt=array("eCheck"=>"eCheck");
		}*/
		//print_r($opt);
		/*$form['cnp_payment_echeck_hidden']=[
			"#type"=> "hidden",
			"#attributes"=> array("id"=>"echeck_option_hidden"),
			'#default_value'=>$opt,
			'#prefix' => '<div class="" id="cnp_payment_echeck_hidden">',
			'#suffix' => '</div>',
		];
		if($config->get('cnpuber.cnp_accid'))
		{
			$serviceOPtions1=$this->getWCCnPactivePaymentList($config->get('cnpuber.cnp_accid'));
			$payOptions1=$serviceOPtions1->GetAccountDetailResult;
			if($payOptions1->CustomPaymentType=="")
			{
				$css_class='none';
			}
		}*/
			/*$form['some_text_start'] = array(
            '#prefix' => '<div class="custom_payment_wrapper">'
			);*/
			
			/*$form['custom_payment_option_hidden']=[
			"#type"=> "hidden",
			"#attributes"=> array("id"=>"custom_payment_option_hidden"),
			'#default_value'=>$config->get('cnpuber.cnp_payment_methdos1')['CustomPayment'],
			];*/
				//print_r();
				 /*$form['cnp_payment_methdos1'] = array(
				  '#type' => 'checkboxes',
				  '#attributes' => array('checked' => 'checked'),
				  '#default_value' => ($config->get('cnpuber.cnp_payment_methdos1')) ? $config->get('cnpuber.cnp_payment_methdos1') : [],
					"#options"=> array("CustomPayment"=>"Custom Payment"),
					'#ajax' => array(
					'callback' => '::updateDefaultPayments',
					'effect' => 'fade',
					"wrapper"=>"cnp_def_pay",
					'event' => 'change',
					'progress' => array(
					  'type' => 'throbber',
					  'message' => NULL,
					),
				  ),
				);*/
				//echo $config->get('cnpuber.cnp_payment_methdos1')['CustomPayment'];
				
				
				 /*$form['cnp_customPayment_titles'] = array(
				  '#type' => 'textarea',
				  '#title' => 'Custom Title',
				  "#prefix"=>"<div class='payment-titles-area'>",
				  "#suffix"=>"</div>",
				  '#description' =>  htmlspecialchars("Separate with semicolon (;)"),
				  '#default_value' =>($config->get('cnpuber.cnp_customPayment_titles')) ? $config->get('cnpuber.cnp_customPayment_titles') :"COD;",
				  '#ajax' => array(
					'callback' => '::checkDefaultPayments',
					'effect' => 'fade',
					"wrapper"=>"cnp_def_pay",
					'event' => 'change',
					'progress' => array(
					  'type' => 'throbber',
					  'message' => NULL,
					),
				  ),
				);*/
				
				/*$form['cnp_referenceNumber_label'] = [  
					'#type' => 'textfield',  
					'#title' => $this->t('Reference Number Label'), 
					'#default_value' => ($config->get('cnpuber.cnp_referenceNumber_label')) ? $config->get('cnpuber.cnp_referenceNumber_label'):"REF",
					'#prefix' => '<div class="ref-number">',
					'#suffix' => '</div>',
				];*/
			
			/*$form['some_text_end'] = array(
			'#suffix' => '</div>'
			);	*/
		
		
		/*$dp=array(
			"Credit Card"=>"Credit Card",
			"eCheck"=>"eCheck",
		);
       if($config->get('cnpuber.cnp_customPayment_titles'))
	   {
		   $pay_methods=$config->get('cnpuber.cnp_customPayment_titles');
		   $arr=explode(";",$pay_methods,-1);
		  
		   foreach($arr as $a)
		   {
			   $dp[$a]=$a;
		   }
	   }
	   else
	   {
		    $dp=array(
			"Credit Card"=>"Credit Card",
			"eCheck"=>"eCheck",
			);
	   }*/
	   //print_r($dp);
	   //$form['#attributes'] = array('OnSubmit' => 'return validateCnPSettings()');
		/*$form['cnp_default_payment']=[
            "#type"=> "select",
            "#title"=> $this->t("Default Payment Method"),
            "#options"=>$this->getPayOptions($form_state,$config),
			"#prefix"=>"<div id='cnp_def_pay'>",
			"#suffix"=>"</div>",
            "#default_value" => ($config->get('cnpuber.cnp_default_payment')!==null) ? $config->get('cnpuber.cnp_default_payment'):"" ,
        ];*/
		/*$form['cnp_pre_auth']=[
            "#type"=> "checkboxes",
            "#title"=> $this->t("Allow Pre-Authorization for 0 (Zero) balance"),
            "#options"=> array("1"=>"Allow Pre-Authorization for 0 (Zero) balance"),
            "#default_value" => $config->get('cnpuber.cnp_pre_auth'),
            '#prefix' => '<div class="container-inline cnp_bform_pre_auth">',
            '#suffix' => '</div>',
        ];*/
		
		$form['cnp_pre_auth']=[
			"#type"=> "hidden",
			'#default_value'=>1,
		];
		
		
		$form['receipt_settings_start'] = array(
            '#prefix' => '<div class="cnp_bform_receipt_settings">',
        );
		
		$form['some_text'] = array(
            '#markup' => '<H2>Receipt Settings</H2>'
        );
		$form['cnp_receipt_patron']=[
            "#type"=> "checkboxes",
            "#title"=> $this->t("Send Receipt to Patron"),
            "#options"=> array("1"=>"Send Receipt to Patron"),
            "#default_value" => ($config->get('cnpuber.cnp_receipt_patron'))?$config->get('cnpuber.cnp_receipt_patron'):[],
			'#prefix' => '<div class="cnp_bform_receipt_patron">',
            '#suffix' => '</div>',
        ];
		$form['cnp_receipt_header']=[
            "#type"=> "textarea",
            "#title"=> $this->t("Receipt Header"),
			"#attributes"=>array("maxlength"=>"1500","id"=>"cnp_receipt_head_msg"),
            "#default_value" => $config->get('cnpuber.cnp_receipt_header'),
            '#prefix' => '<div class="cnp_bform_receipt_header">',
            '#suffix' => '</div>',
            "#description"=> "Maximum: 1500 characters, the following HTML tags are allowed: ".htmlspecialchars("<P></P><BR /><OL></OL><LI></LI><UL></UL>").". you have <span id='cnpheadcount'>1500</span> characters left.",
        ];
         $form['cnp_terms_con']=[
            "#type"=> "textarea",
            "#title"=> $this->t("Terms & Conditions"),
			"#attributes"=>array("maxlength"=>"1500","id"=>"cnp_terms_con_msg"),
            "#default_value" => $config->get('cnpuber.cnp_terms_con'),
            '#prefix' => '<div class="cnp_bform_terms_con">',
            '#suffix' => '</div>',
            "#description"=> "The following HTML tags are allowed: ".htmlspecialchars("<P></P><BR /><OL></OL><LI></LI><UL></UL>").". 
Maximum: 1500 characters, you have <span id='cnptnccount'>1500</span> characters left.",
        ];
		$form['receipt_settings_end'] = array(
            '#suffix' => '</div>',
        );
		
		$form['some_text1'] = array(
            '#markup' => '<H2 class="recurring_set">Recurring Settings</H2>'
        );
		$form['cnp_recurr_label'] = [  
            '#type' => 'textfield',  
            '#title' => $this->t('Label'), 
            '#default_value' => ($config->get('cnpuber.cnp_recurr_label'))? $config->get('cnpuber.cnp_recurr_label') : "Set this as a recurring payment",
            '#prefix' => '<div class="cnp_bform_recurr_label">',
            '#suffix' => '</div>',
        ];
		$form['cnp_recurr_settings'] = [  
            '#type' => 'textfield',  
            '#title' => $this->t('Settings'), 
            '#default_value' => ($config->get('cnpuber.cnp_recurr_settings'))? $config->get('cnpuber.cnp_recurr_settings') : "Payment options",
			'#prefix' => '<div class="cnp_bform_recurr_settings">',
            '#suffix' => '</div>',
        ];
		$form['cnp_recurr_oto']=[
            "#type"=> "checkboxes",
			//"#attributes"=>array("checked"=>"checked"),
            "#title"=> $this->t(""),
            "#options"=> array("oto"=>"One Time Only"),
            "#default_value" => ($config->get('cnpuber.cnp_recurr_oto'))?$config->get('cnpuber.cnp_recurr_oto'):[],
			//'#prefix' => '<div class="cnp_bform_recurr_oto">',
			'#prefix' => '<div class="cnp_recurr_oto">',
            '#suffix' => '</div>',
        ];
		$form['cnp_recurr_recur']=[
            "#type"=> "checkboxes",
			//"#attributes"=>array("id"=>"cnp_recurr_recur"),
            "#title"=> $this->t(""),
            "#options"=> array("1"=>" Recurring"),
            "#default_value" => ($config->get('cnpuber.cnp_recurr_recur'))?$config->get('cnpuber.cnp_recurr_recur'):[],
			//'#prefix' => '<div class="cnp_bform_recurr_recur">',
			'#prefix' => '<div class="cnp_recurr_recur">',
            '#suffix' => '</div>',
        ];
		
		$form['recurr_option_div_start'] = array(
            '#markup' => '<div class="recurr_option">',
        );
		
		$form['cnp_default_payment_options']=[
            "#type"=> "select",
            "#title"=> $this->t("Default Payment Options"),
            "#options"=> array(
                "Recurring"=>$this->t("Recurring"),
                "One Time Only"=>$this->t("One Time Only"),
                ),
			'#prefix' => '<div class="container-inline cnp_bform_default_payment_options" id="default_payment_options_wrapper">',
			'#suffix' => '</div>',
            "#default_value" => $config->get('cnpuber.cnp_default_payment_options'),
        ];
		$form['cnp_recurring_types']=[
            "#type"=> "textfield",
            "#title"=> $this->t(""),
			'#prefix' => '<div class="cnp_bform_recurring_types">',
            '#suffix' => '</div>',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_types'))?$config->get('cnpuber.cnp_recurring_types'):"Recurring types",
        ];
		$form['cnp_recurr_type_option']=[
            "#type"=> "checkboxes",
            "#title"=> $this->t(""),
			//"#required"=> TRUE,
            "#options"=> array(
				"Installment"=>"Installment (e.g. pay $1000 in 10 installments of $100 each)",
				"Subscription"=>"Subscription (e.g. pay $100 every month for 12 months)"
				),
            "#default_value" => ($config->get('cnpuber.cnp_recurr_type_option'))?$config->get('cnpuber.cnp_recurr_type_option'):["Installment"=>"Installment (e.g. pay $1000 in 10 installments of $100 each)"],
			'#prefix' => '<div class="cnp_bform_recurr_type_option">',
            '#suffix' => '</div>',
        ];
		$form['cnp_default_recurring_type']=[
            "#type"=> "select",
            "#title"=> $this->t("Default Recurring type"),
			"#options"=> array(
                "Subscription"=>$this->t("Subscription"),
                "Installment"=>$this->t("Installment"),
                ),
			'#prefix' => '<div class="container-inline cnp_bform_default_recurring_type" id="default_recurring_type_wrapper">',
			'#suffix' => '</div>',
            "#default_value" => $config->get('cnpuber.cnp_default_recurring_type'),
        ];
		$form['cnp_recurring_periodicity']=[
            "#type"=> "textfield",
            "#title"=> $this->t(""),
			'#prefix' => '<div class="cnp_bform_recurring_periodicity">',
            '#suffix' => '</div>',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_periodicity'))?$config->get('cnpuber.cnp_recurring_periodicity'):"Periodicity",
        ];
		$form['cnp_recurring_periodicity_options']=[
            "#type"=> "checkboxes",
            "#title"=> $this->t(""),
            "#options"=> array(
				"Week"=>"Week",
				"2 Weeks"=>"2 Weeks",
				"Month"=>"Month",
				"2 Months"=>"2 Months",
				"Quarter"=>"Quarter",
				"6 Months"=>"6 Months",
				"Year"=>"Year",
				),
            "#default_value" => ($config->get('cnpuber.cnp_recurring_periodicity_options'))?$config->get('cnpuber.cnp_recurring_periodicity_options'):[],
			'#prefix' => '<div class="cnp_bform_recurring_periodicity_options">',
            '#suffix' => '</div>',
        ];
		$form['cnp_recurring_no_of_payments']=[
            "#type"=> "textfield",
            
			'#prefix' => '<div class="cnp_bform_recurring_no_of_payments">',
            
            "#default_value" => ($config->get('cnpuber.cnp_recurring_no_of_payments'))?$config->get('cnpuber.cnp_recurring_no_of_payments'):"Number of payments",
        ];
		$form['cnp_recurring_no_of_payments_options']=[
            "#type"=> "radios",
            "#title"=> $this->t(""),
            "#options"=> array(
				"indefinite_openfield"=>"Indefinite + Open Field Option",
				"1"=>"Indefinite Only",
				"openfield"=>"Open Field Only",
				"fixednumber"=>" Fixed Number - No Change Allowed",
				),
			'#suffix' => '</div>',
            "#default_value" => $config->get('cnpuber.cnp_recurring_no_of_payments_options'),
        ];
		$form['cnp_recurring_default_no_payment_lbl']=[
            "#type"=> "textfield",
			'#prefix' => '<div class="default_no_of_payments_wrapper_start"><div class="container-inline cnp_bform_no_payment_lbl">',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_default_no_payment_lbl'))?$config->get('cnpuber.cnp_recurring_default_no_payment_lbl'):"Default number of payments",
        ];
		$form['cnp_recurring_default_no_payments']=[
            "#type"=> "textfield",
            "#title"=> $this->t(""),
			"#attributes"=>array("maxlength"=>3),
            '#suffix' => '</div>',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_default_no_payments'))?$config->get('cnpuber.cnp_recurring_default_no_payments'):"",
        ];
		$form['cnp_recurring_max_no_payment_open_filed_lbl']=[
            "#type"=> "textfield",
           
			'#prefix' => '<div class="container-inline cnp_bform_fix_number_no_change" id="fix-number-no-change">',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_max_no_payment_open_filed_lbl'))?$config->get('cnpuber.cnp_recurring_max_no_payment_open_filed_lbl'):"Maximum number of installments allowed",
        ];
		$form['cnp_recurring_max_no_payment']=[
            "#type"=> "textfield",
            "#title"=> $this->t(""),
			"#attributes"=>array("maxlength"=>3),
            '#suffix' => '</div></div>',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_max_no_payment'))?$config->get('cnpuber.cnp_recurring_max_no_payment'):"",
        ];
		//open filed form
		$form['cnp_recurring_default_no_payment_open_field_lbl']=[
            "#type"=> "textfield",
			'#prefix' => '<div class="open_filed_wrapper_start"><div class="container-inline cnp_bform_open_field_lbl">',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_default_no_payment_open_field_lbl'))?$config->get('cnpuber.cnp_recurring_default_no_payment_open_field_lbl'):"Default number of payments",
        ];
		
		$form['cnp_recurring_default_no_payments_open_filed']=[
            "#type"=> "textfield",
            "#title"=> $this->t(""),
			"#attributes"=>array("maxlength"=>3),
            "#default_value" => ($config->get('cnpuber.cnp_recurring_default_no_payments_open_filed'))?$config->get('cnpuber.cnp_recurring_default_no_payments_open_filed'):"",
			'#suffix' => '</div>',
        ];
		$form['cnp_recurring_max_no_payment_lbl']=[
            "#type"=> "textfield",
			'#prefix' => '<div class="container-inline cnp_bform_max_no_payment" id="">',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_max_no_payment_lbl'))?$config->get('cnpuber.cnp_recurring_max_no_payment_lbl'):"Maximum number of installments allowed",
        ];
		
		$form['cnp_recurring_max_no_payment_open_filed']=[
            "#type"=> "textfield",
            "#title"=> $this->t(""),
			"#attributes"=>array("disabled"=>"disabled","maxlength"=>3),
            '#suffix' => '</div></div>',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_max_no_payment_open_filed'))?$config->get('cnpuber.cnp_recurring_max_no_payment_open_filed'):"999",
        ];
		//cnp_recurring_default_no_payments_open_filed
		//fixed Number nochange allowed filed
		$form['cnp_recurring_default_no_payment_fncc_lbl']=[
            "#type"=> "textfield",
			'#prefix' => '<div class="fixed_number_no_chnage_wrapper_start"><div class="container-inline cnp_bform_no_change">',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_default_no_payment_fncc_lbl'))?$config->get('cnpuber.cnp_recurring_default_no_payment_fncc_lbl'):"Default number of payments",
        ];
		$form['cnp_recurring_default_no_payments_fnnc']=[
            "#type"=> "textfield",
            "#title"=> $this->t(""),
			"#attributes"=>array("maxlength"=>3),
            '#suffix' => '</div></div>',
            "#default_value" => ($config->get('cnpuber.cnp_recurring_default_no_payments_fnnc'))?$config->get('cnpuber.cnp_recurring_default_no_payments_fnnc'):"",
        ];
		
		$form['recurr_option_div_end'] = array(
            '#markup' => '</div>',
        );
		 $form["hs_div_end"]=[
			'#suffix' => '</div>',
		];
		
		
    return $form;
   
   }
   //get All accound iD's
   public function getAccountIDs()
   {
		$database = \Drupal::database();
        $prefix= $database->tablePrefix();
        $table_name = $prefix.'dp_cnp_uber_jbcnpaccountsinfo';
        $query = $database->query("SELECT * FROM ".$table_name);
        $result = $query->fetchAll();
        $opt=array();
        $opt['']="-select-";
        foreach($result as $acc )
        {
            $opt[$acc->cnpaccountsinfo_orgid]=$acc->cnpaccountsinfo_orgid." [".$acc->cnpaccountsinfo_orgname."]";
        }
		ksort($opt);
        return $opt;
   }
  
    public function changeOptionsAjax(array &$form, FormStateInterface $form_state) {
	return $form['cnp_camp_urls'];
    }
	
	public function RefreshAccounts(array &$form, FormStateInterface $form_state)
	{
		return $form['cnp_accid'];
	}
	
  /**
   * Get options for second field.
   */
    public function getOptions(FormStateInterface $form_state,$config) {
		$camrtrnval['']=array();
        if ($form_state->getValue('cnp_accid') == '') {
			
			$camrtrnval['']="--Select Campaign Name--";
			$accid=$config->get('cnpuber.cnp_accid');
			$opt=$this->cnp_getCnPUserEmailAccountList($accid)[0];
			//$opt=(array)$opt;
			//print_r($opt);
			if(!empty($opt))
			{
				if(is_array($opt))
				{
					//sort($opt);
					//print_r();
					for($in = 0 ; $in < count($opt);$in++)
					{ 
						//$camrtrnval[$opt->name."(".$opt->alias.")"] = $opt->name."(".$opt->alias.")" ;
						//$camrtrnval[$opt[$in]->name." (".$opt[$in]->alias.")"] = $opt[$in]->name." (".$opt[$in]->alias.")" ;
						$camrtrnval[$opt[$in]->alias] = $opt[$in]->name." (".$opt[$in]->alias.")" ;
					}
				}
				else
				{
					//print_r($opt);
					
					//$camrtrnval[$opt->name." (".$opt->alias.")"] = $opt->name." (".$opt->alias.")" ;
					$camrtrnval[$opt->alias] = $opt->name." (".$opt->alias.")" ;
				}
			}
			else
			{
				$camrtrnval[""]="Campaign URL Alias are not found";
			}
			
		  if($config->get('cnpuber.cnp_camp_urls')!="")
		  {
			//$camrtrnval[$config->get('cnpuber.cnp_camp_urls')]=$config->get('cnpuber.cnp_camp_urls');
		  }
		  else
		  {
			  $camrtrnval['']="--Select Campaign Name--";
		  }
        }
        else {
			$optionData= $this->cnp_getCnPUserEmailAccountList($form_state->getValue('cnp_accid'));
			//print_r($options);
			
			/*$paymentCards=$optionData[1];
			$response = new AjaxResponse();
			$response->addCommand(new HtmlCommand('#edit-cnp-payment-methdos', $paymentCards));
			*/
			$camrtrnval['']="--Select Campaign Name--";
			$cnpsel="";
			//$config=$this->config("cnpuber.mainsettings");
			
			
			
				$camrtrnval['']="--Select Campaign Name--";
				$options=$optionData[0];
				
				  if(count($options)==1)
				  {
					  //$camrtrnval[$options->name." (".$options->alias.")"] = $options->name." (".$options->alias.")" ;
					  $camrtrnval[$options->alias] = $options->name." (".$options->alias.")" ;
				  }
				  else
				  {
						//sort($options);
						for($inc = 0 ; $inc < count($options);$inc++)
						{ 
							//if($responsearr[$inc]->alias == $cnpcampaignalias){ $cnpsel ="selected='selected'";}else{$cnpsel ="";}
							//$camrtrnval .= "<option value='".$options[$inc]->alias."' ".$cnpsel.">".$options[$inc]->name." (".$options[$inc]->alias.")</option>";
							//$camrtrnval[$options[$inc]->alias] = $options[$inc]->name."(".$options[$inc]->alias.")" ;
							//$camrtrnval[$options[$inc]->name." (".$options[$inc]->alias.")"] = $options[$inc]->name." (".$options[$inc]->alias.")" ;
							$camrtrnval[$options[$inc]->alias] = $options[$inc]->name." (".$options[$inc]->alias.")" ;
						}
				  }
        }
		
		/*sort($camrtrnval,SORT_NATURAL | SORT_FLAG_CASE);
		$sortedOptions=array();
		if(count($camrtrnval)>0)
		{
			for($i=0;$i<count($camrtrnval);$i++)
			{
				$sortedOptions[$camrtrnval[$i]]=$camrtrnval[$i];
			}
		}*/
		//asort($camrtrnval);
			natcasesort($camrtrnval);
		//print_r($sortedOptions);
		//return $sortedOptions;
		return $camrtrnval;
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
	 // print_r($cnprtrnpaymentstxt);
	  //echo ;
	  //exit("No Records");
	  if(count($cnprtrnpaymentstxt)>0)
	  {
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
	  }
	  else
	  {
		  $Clist=array();
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
			//$responsearr=(object)array(""=>"No Campaigns Found");
			$responsearr=array();
		}
       return $responsearr;
            
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
	
	public function checkDefaultPayments(array &$form, FormStateInterface $form_state) {
		return $form['cnp_default_payment'];
    }
    public function getPayOptions($form_state,$config)
	{
		$opt=array();
		if($form_state->getValue("cnp_payment_methdos1"))
		{
			$data=$form_state->getValue("cnp_payment_methdos1");
			if($data['CustomPayment'])
			{
				if($form_state->getValue("cnp_customPayment_titles"))
				{
					//$opt["eCheck"]="eCheck2112";
					$opt["Credit Card"]="Credit Card";
					$opt["eCheck"]="eCheck";
					$pt=$form_state->getValue("cnp_customPayment_titles");
					$arr=explode(";",$pt,-1);
					foreach($arr as $a)
					{
					   $opt[$a]=$a;
					}
				}
				else
				{
					$opt["Credit Card"]="Credit Card";
					$opt["eCheck"]="eCheck";
					$svaed_pay_methods=$config->get("cnpuber.cnp_customPayment_titles");
					$arr=explode(";",$svaed_pay_methods,-1);
					foreach($arr as $a)
					{
					   $opt[$a]=$a;
					}
				}
			}
			else
			{
				$opt["Credit Card"]="Credit Card";
				$opt["eCheck"]="eCheck";
				
			}
			
			
		}
		else
		{
			if($config->get("cnpuber.cnp_payment_methdos1"))
			{
				$opt["Credit Card"]="Credit Card";
				$opt["eCheck"]="eCheck";
				$svaed_pay_methods=$config->get("cnpuber.cnp_customPayment_titles");
				$arr=explode(";",$svaed_pay_methods,-1);
				foreach($arr as $a)
				{
				   $opt[$a]=$a;
				}
			}
			else
			{
				$opt["Credit Card"]="Credit Card";
				$opt["eCheck"]="eCheck";
				$opt["COD"]="COD";
				
			}
			
		}
		//$data=$config->get("cnpuber.cnp_payment_methdos1");
		
		return $opt;
		
		
		
	}
	
	public function updateDefaultPayments(array &$form, FormStateInterface $form_state)
	{
		return $form['cnp_default_payment'];
	}
}