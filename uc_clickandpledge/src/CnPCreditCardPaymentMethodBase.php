<?php

namespace Drupal\uc_clickandpledge;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\uc_order\OrderInterface;
use Drupal\uc_payment\PaymentMethodPluginBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Component\Serialization\Yaml;
use DOMDocument;
use SoapClient;
use Exception;
//use Drupal\Core\Entity\EntityInterface;
//use Drupal\uc_tax\Entity\TaxRate;
/**
 * Defines a base credit card payment method plugin implementation.
 */
abstract class CnPCreditCardPaymentMethodBase extends PaymentMethodPluginBase {

  /**
   * Returns the set of fields which are used by this payment method.
   *
   * @return array
   *   An array with keys 'cvv', 'owner', 'start', 'issue', 'bank' and 'type'.
   */
  public function getEnabledFields() {
    return [
      'cvv' => TRUE,
      'owner' => TRUE,
      'start' => FALSE,
      'issue' => FALSE,
      'bank' => FALSE,
      'type' => FALSE,
    ];
  }

  /**
   * Returns the set of card types which are used by this payment method.
   *
   * @return array
   *   An array with keys as needed by the chargeCard() method and values
   *   that can be displayed to the customer.
   */
  public function getEnabledTypes() {
	$config = \Drupal::config('cnpuber.mainsettings');
	$accCards=explode("#",$config->get('cnpuber.cnp_payment_credit_card_options_hidden'),-1);
	$cards=array();
	//print_r($accCards);
	if(count($accCards)>0)
	{
		
		for($c=0;$c<count($accCards);$c++)
		{
			if($accCards[$c]=="Master")
			{
				$cards['mastercard']=$this->t('mastercard');
			}
			else
			{
				$cards[strtolower($accCards[$c])]=$this->t($accCards[$c]);
			}
		}
	}
	else
	{
		$cards=array();
	}
	//print_r($cards);
	//exit();
	//$accCards['maestro']="Maestro";
	//$accCards['mastercard']="Mastercard";
	
    /*return [
      'visa' => $this->t('Visa'),
      'mastercard' => $this->t('MasterCard'),
      'discover' => $this->t('Discover'),
      'amex' => $this->t('American Express'),
    ];*/
	return $cards;
  }

  /**
   * Returns the set of transaction types allowed by this payment method.
   *
   * @return array
   *   An array with values UC_CREDIT_AUTH_ONLY, UC_CREDIT_PRIOR_AUTH_CAPTURE,
   *   UC_CREDIT_AUTH_CAPTURE, UC_CREDIT_REFERENCE_SET, UC_CREDIT_REFERENCE_TXN,
   *   UC_CREDIT_REFERENCE_REMOVE, UC_CREDIT_REFERENCE_CREDIT, UC_CREDIT_CREDIT
   *   and UC_CREDIT_VOID.
   */
  public function getTransactionTypes() {
    return [
      UC_CREDIT_AUTH_CAPTURE,
      UC_CREDIT_AUTH_ONLY,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayLabel($label) {
      
    $build['#attached']['library'][] = 'uc_clickandpledge/cnp_uc_credit.styles';
    $build['label'] = [
      '#plain_text' => $label,
    ];
    $cc_types = $this->getEnabledTypes();
    foreach ($cc_types as $type => $description) {
      $build['image'][$type] = [
        '#theme' => 'image',
        '#uri' => drupal_get_path('module', 'uc_clickandpledge') . '/images/' . $type . '.gif',
        '#alt' => $description,
        '#attributes' => ['class' => ['uc-credit-cctype', 'uc-credit-cctype-' . $type]],
      ];
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'txn_type' => UC_CREDIT_AUTH_CAPTURE,
        //'cnp_uber_title'=>'',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'uc_clickandpledge/cnp_uc_credit.styles';
   
   
    
    
    $form['txn_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Transaction type'),
      '#default_value' => $this->configuration['txn_type'],
      '#options' => [
        UC_CREDIT_AUTH_CAPTURE => $this->t('Authorize and capture immediately'),
        UC_CREDIT_AUTH_ONLY => $this->t('Authorization only'),
      ],
    ];
    
    /*$form['cnp_uber_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Title'),
      '#default_value' => $this->configuration['cnp_uber_title']
    ];*/
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['txn_type'] = $form_state->getValue('txn_type');
    //$this->configuration['cnp_uber_title'] = $form_state->getValue('cnp_uber_title');
  }

  /**
   * {@inheritdoc}
   */
  public function cartDetails(OrderInterface $order, array $form, FormStateInterface $form_state) {
	$config = \Drupal::config('cnpuber.mainsettings');
	//$config->get("cnpuber.cnp_accid")
   //print($config->get("cnpuber.cnp_accid"));
   
  
	//print_r("Test".$config->get('cnpuber.cnp_status_enable')['yes']);
   
   if($config->get('cnpuber.cnp_status_enable')['yes']==="yes")
   {
   
    $build = [
      '#type' => 'container',
      //'#attributes' => ['class' => 'uc-credit-form'],
    ];
    $build['#attached']['library'][] = 'uc_clickandpledge/cnp_uc_credit.styles';
   /* $build['cc_policy'] = [
      '#prefix' => '<p class="tester">',
      '#markup' => $this->t('Your billing information must match the billing address for the credit card entered below or we will be unable to process your payment.'),
      '#suffix' => '</p>',
    ];*/
	
	$build['fef_some_text_start'] = array(
		'#prefix' => '<div class="cnp_payment_heading"><p>Secured Credit Card payment with Click and Pledge API.</p>',
		'#suffix' => '</div>',
	);
	
	$cnplogo="<img alt='Click&Pledge Secured' src='".base_path().drupal_get_path('module', 'uc_clickandpledge')."/images/logo.jpg'>";
	
	$build['cnp_logo'] = array(
		'#prefix' => '<div class="cnp_logo">'.$cnplogo,
		'#suffix' => '</div>',
	);
	
	$PayOption="";
	if(isset($_POST['panes']['payment']['details']['fef_payment_options']))
	{
		$PayOption=$_POST['panes']['payment']['details']['fef_payment_options'];
		if($PayOption=="Recurring")
		{
			$build['cnppayoption'] = [
				'#type' => 'hidden',
				'#value' => $PayOption,
				"#attributes"=>array("id"=>"cnppayoption"),
			];
		}
		else
		{
			$build['cnppayoption'] = [
				'#type' => 'hidden',
				'#value' => $PayOption,
				"#attributes"=>array("id"=>"cnppayoption"),
			];
		}
	}
	else
	{
		$build['cnppayoption'] = [
				'#type' => 'hidden',
				'#value' => $PayOption,
				"#attributes"=>array("id"=>"cnppayoption"),
			];
	}
	if($config->get('cnpuber.cnp_mode')=="Yes")
	{
		$build['transaction_mode'] = array(
		//'#prefix' => '<div class=""><p><b>Set this as a recurring payment</b></p>',
		'#prefix' => '<div class=""><p><b class="cnp_testmode">[ Test Mode ]</b></p>',
		'#suffix' => '</div>',
		);
	}
	
	if($config->get('cnpuber.cnp_recurr_oto')['oto']==="oto" && $config->get('cnpuber.cnp_recurr_recur')[1]==1)
	{
		$build['fef_recurring_payment_lbl'] = array(
		//'#prefix' => '<div class=""><p><b>Set this as a recurring payment</b></p>',
		'#prefix' => '<div class="cnp_form_recurring_label"><p><b>'.$config->get('cnpuber.cnp_recurr_label').'</b></p>',
		'#suffix' => '</div>',
		);
		$build['fef_payment_options_lbl'] = array(
			//'#prefix' => '<div class=""><p><b>Payment Options *</b></p>',
			'#prefix' => '<div class="cnp_form_recurr_settings"><p><b>'.$config->get('cnpuber.cnp_recurr_settings').' <span class="cnpstar_color">*</span></b></p>',
			'#suffix' => '</div>',
		);
		$build['cnp_form_payment_options_start'] = array(
			'#prefix' => '<div class="cnp_form_payment_options">',
		);
			 $build['fef_payment_options'] = [
				'#type' => 'radios',
				'#attributes' => array("data-payment-options"=>"fef_payment_options"),
				//'#required' => TRUE,
				"#options"=>array("One Time Only"=>"One Time Only","Recurring"=>"Recurring"),
				"#prefix"=>'<div class="">',
				"#suffix"=>'</div>',
				"#default_value"=>$config->get('cnpuber.cnp_default_payment_options'),
				'#ajax' => [
					'callback' => [$this, 'displayRecurringFormUber'],
				],
			];
		$build['cnp_form_payment_options_end'] = array(
			'#suffix' => '</div>',
		);
		//toggle to display recurring options form
		if($config->get('cnpuber.cnp_default_payment_options')==="Recurring")
		{
			$cssClass="recurring_div_show uc-credit-form cnp_uc_credit_form";
		}
		else
		{
			$cssClass="recurring_div_hide uc-credit-form cnp_uc_credit_form";
		}
		
		
		$build['fef_recurr_options_div_starts'] = array(
		'#prefix' => '<div class="'.$cssClass.'" id="fef_recurr_options_division">'
		);
			
			//check recurring type checkbox values. if both are checked,display dropdown with both
			//options or else display individually
			
			$rto=$config->get('cnpuber.cnp_recurr_type_option');
			
			if($rto['Installment']==="Installment" && $rto['Subscription']==="Subscription")
			{
				$build['cnp_form_fef_recuring_type_option_start'] = array(
					'#prefix' => '<div class="cnp_form_recuring_type_option">',
				);
				
					$build['fef_recuring_type_option'] = [
						'#type' => 'select',
						'#title' => t($config->get('cnpuber.cnp_recurring_types').'<span style="color:red"> *</span>'),
						'#options' =>$rto,
						"#default_value"=>$config->get('cnpuber.cnp_default_recurring_type'),
					];
					
				$build['cnp_form_fef_recuring_type_option_end'] = array(
					'#suffix' => '</div><div class="clearfix cnp-test-bottom"></div>',
				);
			}
			else
			{
				
				$othopt=$config->get('cnpuber.cnp_recurr_type_option');
				
				if($othopt['Installment']==="Installment" && $othopt['Subscription']===0)
				{
					$build['fef_recurr_options_label_display'] = array(
					'#prefix' => '<div class="cnp_form_recurr_options_label"><p>'.$config->get('cnpuber.cnp_recurring_types').': '.$othopt['Installment'].'</p>',
					'#suffix' => '</div>',
					);
					$build['fef_recuring_type_option'] = [
					  '#type' => 'hidden',
					  '#value' => $othopt['Installment'],
					];
				}
				else if($othopt['Installment']===0 && $othopt['Subscription']==="Subscription")
				{
					$build['fef_recurr_options_label_display'] = array(
					'#prefix' => '<div class="cnp_form_recurr_options_label"><p>'.$config->get('cnpuber.cnp_recurring_types').': '.$othopt['Subscription'].'</p>',
					'#suffix' => '</div>',
					);
					$build['fef_recuring_type_option'] = [
					  '#type' => 'hidden',
					  '#value' => $othopt['Subscription'],
					];
				}
			}
			//DISPLAY PERIODCITY: IF IT IS ONE OPTION DISPLAY DIRECTLY OR MULTIPLE OPTIONS
			//DISPLAY ALL OPTIONS AS DROPDOWN
			$periodcityOpt=$config->get('cnpuber.cnp_recurring_periodicity_options');
			$periodOpt=array();
			foreach($periodcityOpt as $popts)
			{
				if($popts !== 0)
				{
					$periodOpt[$popts]=$popts;
				}
			}
			if(count($periodOpt)==1)
			{
				
				$build['fef_cnp_periodcity_label_display'] = array(
				'#prefix' => '<div class="cnp_form_periodcity_label"><p>'.$config->get('cnpuber.cnp_recurring_periodicity').': '.array_values($periodOpt)[0].'</p>',
				'#suffix' => '</div>',
				);
				$build['fef_cnp_periodcity'] = [
				  '#type' => 'hidden',
				  '#value' => array_values($periodOpt)[0],
				];
				
			}
			else
			{
				$build['cnp_form_periodcity_start'] = array(
					'#prefix' => '<div class="cnp_form_periodcity">',
				);


					$build['fef_cnp_periodcity'] = [
						'#type' => 'select',
						'#title' => t($config->get('cnpuber.cnp_recurring_periodicity').'<span style="color:red"> *</span>'),
						'#options' =>$periodOpt,
					];
				
				$build['cnp_form_periodcity_end'] = array(
					'#suffix' => '<div class="clearfix"></div></div>',
				);
				
			}
		//display default no.of payments based on no.of payments radio buttons
		//echo $config->get('cnp.cnp_recurring_default_no_payments_open_filed');
		
		$nop=$config->get('cnpuber.cnp_recurring_no_of_payments_options');
		if($nop==="fixednumber")
		{
			//echo "fixednumber";
			$config->get('cnpuber.cnp_recurring_default_no_payment_fncc_lbl');
			$build['fef_cnp_noof_payments_label_display'] = array(
				'#prefix' => '<div class="cnp_form_noof_payments_label"><p class="noptext">'.$config->get('cnpuber.cnp_recurring_no_of_payments').': '.$config->get('cnpuber.cnp_recurring_default_no_payments_fnnc').'</p>',
				'#suffix' => '</div>',
			);
			$build['fef_cnp_noof_payments'] = [
				  '#type' => 'hidden',
				  '#default_value' => $config->get('cnpuber.cnp_recurring_default_no_payments_fnnc'),
				];
		}
		elseif($nop==="indefinite_openfield")
		{
			//echo $config->get('cnp.cnp_recurring_default_no_payments_open_filed');
			//echo $config->get('cnp.cnp_recurring_max_no_payment_open_filed');
			
			$build['cnp_form_noof_payments_start'] = array(
				'#prefix' => '<div class="cnp_form_noof_payments uc-credit-form cnp_uc_credit_form">',
			);

			$build['fef_cnp_noof_payments'] = [
				  '#type' => 'number',
				  //"#required"=>true,
				  "#title"=>t($config->get('cnpuber.cnp_recurring_no_of_payments'))."<span class='redcolor'>*</span>",
				  '#default_value' =>$config->get('cnpuber.cnp_recurring_default_no_payments_open_filed'),
			];
			
			$build['cnp_form_noof_payments_end'] = array(
				'#suffix' => '<div class="clearfix"></div></div>',
			);
			
		}
		elseif($nop==="openfield")
		{
			//echo $config->get('cnp.cnp_recurring_default_no_payments');
			//echo $config->get('cnp.cnp_recurring_max_no_payment');
			
			$build['cnp_form_noof_payments_start'] = array(
				'#prefix' => '<div class="cnp_form_noof_payments uc-credit-form cnp_uc_credit_form">',
			);
			
			$build['fef_cnp_noof_payments'] = [
				  '#type' => 'number',
				  //"#required"=>true,
				  "#title"=>t($config->get('cnpuber.cnp_recurring_no_of_payments'))." <span class='redcolor'>*</span>",
				  '#default_value' =>$config->get('cnpuber.cnp_recurring_default_no_payments'),
			];	
			
			$build['cnp_form_noof_payments_end'] = array(
				'#suffix' => ' <div class="clearfix"></div></div>',
			);
		}
		else
		{
			//$config->get('cnp.cnp_recurring_default_no_payment_fncc_lbl');
			$build['fef_cnp_noof_payments_label_display'] = array(
				'#prefix' => '<div class="cnp_form_noof_payments_label"><p class="noptext">'.$config->get('cnpuber.cnp_recurring_no_of_payments').': <b>Indefinite Recurring Only</b></p>',
				'#suffix' => '<div class="clearfix"></div></div>',
			);
			$build['fef_cnp_noof_payments'] = [
				  '#type' => 'hidden',
				  '#default_value' =>"Indefinite Recurring Only",
				];
		}
		
		$build['fef_recurr_options_div_end'] = array(
		'#suffix' => '<div class="clearfix"></div></div>',
		);
		
		//}
	} //1st condition end================================
	else if($config->get('cnpuber.cnp_recurr_oto')['oto']===0 && $config->get('cnpuber.cnp_recurr_recur')[1]==1)
	{
		
		$build['fef_recurring_payment_lbl'] = array(
		//'#prefix' => '<div class=""><p><b>Set this as a recurring payment</b></p>',
		'#prefix' => '<div class="cnp_form_recurring_label"><p><b>'.$config->get('cnpuber.cnp_recurr_label').'</b></p>',
		'#suffix' => '</div>',
		);
		 $build['fef_payment_options'] = [
			'#type' => 'hidden',
			//'#required' => TRUE,
			"#value"=>$config->get('cnpuber.cnp_default_payment_options'),
		];
		
		
		/*if($config->get('cnp.cnp_default_payment_options')!=="One Time Only")
		{*/
		
		$build['fef_recurr_options_div_starts'] = array(
		'#prefix' => '<div class="" id="fef_recurr_options_division">'
		);
			
			//check recurring type checkbox values. if both are checked,display dropdown with both
			//options or else display individually
			
			$rto=$config->get('cnpuber.cnp_recurr_type_option');
			if($rto['Installment']==="Installment" && $rto['Subscription']==="Subscription")
			{
				$build['cnp_form_fef_recuring_type_option_start'] = array(
					'#prefix' => '<div class="cnp_form_recuring_type_option">',
				);
					$build['fef_recuring_type_option'] = [
						'#type' => 'select',
						'#title' => t($config->get('cnpuber.cnp_recurring_types').'<span style="color:red"> *</span>'),
						'#options' =>$rto,
						"#default_value"=>$config->get('cnpuber.cnp_default_recurring_type'),
					];
				$build['cnp_form_fef_recuring_type_option_end'] = array(
					'#suffix' => ' </div><div class="clearfix"></div>',
				);
			}
			else
			{
				$othopt=$config->get('cnpuber.cnp_recurr_type_option');
				
				if($othopt['Installment']==="Installment" && $othopt['Subscription']===0)
				{
					$build['fef_recurr_options_label_display'] = array(
					'#prefix' => '<div class="cnp_form_recurr_options_label"><p>'.$config->get('cnpuber.cnp_recurring_types').': '.$othopt['Installment'].'</p>',
					'#suffix' => '</div>',
					);
					$build['fef_recuring_type_option'] = [
					  '#type' => 'hidden',
					  '#value' => $othopt['Installment'],
					];
				}
				else if($othopt['Installment']===0 && $othopt['Subscription']==="Subscription")
				{
					$build['fef_recurr_options_label_display'] = array(
					'#prefix' => '<div class="cnp_form_recurr_options_label"><p>'.$config->get('cnpuber.cnp_recurring_types').': '.$othopt['Subscription'].'</p>',
					'#suffix' => '</div>',
					);
					$build['fef_recuring_type_option'] = [
					  '#type' => 'hidden',
					  '#value' => $othopt['Subscription'],
					];
				}
				
			}
			//DISPLAY PERIODCITY: IF IT IS ONE OPTION DISPLAY DIRECTLY OR MULTIPLE OPTIONS
			//DISPLAY ALL OPTIONS AS DROPDOWN
			$periodcityOpt=$config->get('cnpuber.cnp_recurring_periodicity_options');
			$periodOpt=array();
			foreach($periodcityOpt as $popts)
			{
				if($popts !== 0)
				{
					$periodOpt[$popts]=$popts;
				}
			}
			if(count($periodOpt)==1)
			{
				
				$build['fef_cnp_periodcity_label_display'] = array(
				'#prefix' => '<div class="cnp_form_periodcity_label"><p>'.$config->get('cnpuber.cnp_recurring_periodicity').': '.array_values($periodOpt)[0].'</p>',
				'#suffix' => '<div class="clearfix"></div></div>',
				);
				$build['fef_cnp_periodcity'] = [
				  '#type' => 'hidden',
				  '#value' => array_values($periodOpt)[0],
				];
				
			}
			else
			{
				$build['cnp_form_periodcity_start'] = array(
					'#prefix' => '<div class="cnp_form_periodcity">',
				);
				$build['fef_cnp_periodcity'] = [
					'#type' => 'select',
					'#title' => t($config->get('cnpuber.cnp_recurring_periodicity').'<span style="color:red"> *</span>'),
					'#options' =>$periodOpt,
				];
				$build['cnp_form_periodcity_end'] = array(
					'#prefix' => '<div class="clearfix"></div></div>',
				);
			}
		//display default no.of payments based on no.of payments radio buttons
		//echo $config->get('cnp.cnp_recurring_default_no_payments_open_filed');
		$nop=$config->get('cnpuber.cnp_recurring_no_of_payments_options');
		if($nop==="fixednumber")
		{
			//echo "fixednumber";
			$config->get('cnpuber.cnp_recurring_default_no_payment_fncc_lbl');
			$build['fef_cnp_noof_payments_label_display'] = array(
				'#prefix' => '<div class="cnp_form_noof_payments_label"><p class="noptext">'.$config->get('cnpuber.cnp_recurring_no_of_payments').': '.$config->get('cnpuber.cnp_recurring_default_no_payments_fnnc').'</p>',
				'#suffix' => '<div class="clearfix"></div></div>',
			);
			$build['fef_cnp_noof_payments'] = [
				  '#type' => 'hidden',
				  '#default_value' => $config->get('cnpuber.cnp_recurring_default_no_payments_fnnc'),
				];
		}
		elseif($nop==="indefinite_openfield")
		{
			//echo $config->get('cnp.cnp_recurring_default_no_payments_open_filed');
			//echo $config->get('cnp.cnp_recurring_max_no_payment_open_filed');
			
			$build['cnp_form_noof_payments_start'] = array(
				'#prefix' => '<div class="cnp_form_noof_payments uc-credit-form cnp_uc_credit_form">',
			);
			
			$build['fef_cnp_noof_payments'] = [
				  '#type' => 'textfield',
				  "#title"=>t($config->get('cnpuber.cnp_recurring_no_of_payments')),
				  '#default_value' =>$config->get('cnpuber.cnp_recurring_default_no_payments_open_filed'),
			];
			
			$build['cnp_form_noof_payments_end'] = array(
				'#suffix' => '<div class="clearfix"></div></div>',
			);
		}
		elseif($nop==="openfield")
		{
			//echo $config->get('cnp.cnp_recurring_default_no_payments');
			//echo $config->get('cnp.cnp_recurring_max_no_payment');
			$build['cnp_form_noof_payments_start'] = array(
				'#prefix' => '<div class="cnp_form_noof_payments uc-credit-form cnp_uc_credit_form">',
			);
			$build['fef_cnp_noof_payments'] = [
				  '#type' => 'textfield',
				  "#title"=>t($config->get('cnpuber.cnp_recurring_no_of_payments')),
				  '#default_value' =>$config->get('cnpuber.cnp_recurring_default_no_payments'),
			];	
			$build['cnp_form_noof_payments_end'] = array(
				'#suffix' => '<div class="clearfix"></div></div>',
			);
		}
		else
		{
			//$config->get('cnp.cnp_recurring_default_no_payment_fncc_lbl');
			$build['fef_cnp_noof_payments_label_display'] = array(
				'#prefix' => '<div class="cnp_form_noof_payments_label"><p class="noptext">'.$config->get('cnpuber.cnp_recurring_no_of_payments').': <b>Indefinite Recurring Only</b></p>',
				'#suffix' => '</div>',
			);
			$build['fef_cnp_noof_payments'] = [
				  '#type' => 'hidden',
				  '#default_value' =>"Indefinite Recurring Only",
				];
		}
		
		$build['fef_recurr_options_div_end'] = array(
		'#suffix' => '<div class="clearfix"></div></div>',
		);
		//}
	}//2nd Condition End
	else if($config->get('cnpuber.cnp_recurr_oto')['oto']==="oto" && $config->get('cnpuber.cnp_recurr_recur')[1]==0)
	{
		$build['fef_recurring_payment_lbl'] = array(
		//'#prefix' => '<div class=""><p><b>Set this as a recurring payment</b></p>',
		//'#prefix' => '<div class=""><p><b>1234</b></p>',
		//'#suffix' => '</div>',
		);
		 $build['fef_payment_options'] = [
			'#type' => 'hidden',
			"#value"=>"One Time Only",
		];
		
		
	}
	
	
    $order->payment_details = [];

    // Encrypted data in the session is from the user
    // returning from the review page.
    $session = \Drupal::service('session');
    if ($session->has('sescrd')) {
      $order->payment_details = uc_credit_cache($session->get('sescrd'));
      $build['payment_details_data'] = [
        '#type' => 'hidden',
        '#value' => base64_encode($session->get('sescrd')),
      ];
      $session->remove('sescrd');
    }
    elseif (isset($_POST['panes']['payment']['details']['payment_details_data'])) {
      // Copy any encrypted data that was POSTed in.
      $build['payment_details_data'] = [
        '#type' => 'hidden',
        '#value' => $_POST['panes']['payment']['details']['payment_details_data'],
      ];
    }

    $fields = $this->getEnabledFields();
    if (!empty($fields['type'])) {
      $build['cc_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Card type'),
        '#options' => $this->getEnabledTypes(),
        '#default_value' => isset($order->payment_details['cc_type']) ? $order->payment_details['cc_type'] : NULL,
      ];
    }
    //print_r($fields);
	//echo ;
	//$config->get('cnpuber.cnp_mode')
	//
	$build['credit_form_wrapper_start'] = array(
		'#prefix' => '<div class="uc-credit-form cnp_uc_credit_form">',
		);
	
	
    if (!empty($fields['owner'])) {
      $build['cc_owner'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name on Card'),
        '#default_value' => isset($order->payment_details['cc_owner']) ? $order->payment_details['cc_owner'] : '',
        '#attributes' => ['autocomplete' => 'off'],
        '#size' => 32,
        '#maxlength' => 64,
		'#required' => TRUE,
      ];
    }
	$build['clearfix1'] = array(
		'#prefix' => '<div class="clearfix">',
		'#suffix' => '</div>',
	);
    // Set up the default CC number on the credit card form.
    if (!isset($order->payment_details['cc_number'])) {
      $default_num = NULL;
    }
    elseif (!$this->validateCardNumber($order->payment_details['cc_number'])) {
      // Display the number as-is if it does not validate,
      // so it can be corrected.
      $default_num = $order->payment_details['cc_number'];
    }
    else {
      // Otherwise default to the last 4 digits.
      //$default_num = $this->t('(Last 4) @digits', ['@digits' => substr($order->payment_details['cc_number'], -4)]);
      $default_num = $default_num = $order->payment_details['cc_number'];
    }
	if($config->get('cnpuber.cnp_mode')=="Yes")
	{
	 $build['cc_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Credit Card Number'),
      '#value' => "4111111111111111",
      '#attributes' => ['autocomplete' => 'off',"disabled"=>"disabled"],
      '#size' => 20,
      '#maxlength' => 19,
	  '#required' => TRUE,
    ];
	}
	else
	{
		$build['cc_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Credit Card Number'),
      '#default_value' => $default_num,
      '#attributes' => ['autocomplete' => 'off'],
      '#size' => 20,
      '#maxlength' => 19,
	  '#required' => TRUE,
    ];
	}
   
$build['clearfix2'] = array(
		'#prefix' => '<div class="clearfix">',
		'#suffix' => '</div>',
	);
	//echo $config->get("cnp_mode");
	
	
    if (!empty($fields['start'])) {
      $month = isset($order->payment_details['cc_start_month']) ? $order->payment_details['cc_start_month'] : NULL;
      $year = isset($order->payment_details['cc_start_year']) ? $order->payment_details['cc_start_year'] : NULL;
      $year_range = range(date('Y') - 10, date('Y'));
      $build['cc_start_month'] = [
        '#type' => 'number',
        '#title' => $this->t('Start date'),
        '#options' => [
          "01" => $this->t('01 - January'),
          "02" => $this->t('02 - February'),
          "03" => $this->t('03 - March'),
          "04" => $this->t('04 - April'),
          "05" => $this->t('05 - May'),
          "06" => $this->t('06 - June'),
          "07" => $this->t('07 - July'),
          "08" => $this->t('08 - August'),
          "09" => $this->t('09 - September'),
          "10" => $this->t('10 - October'),
          "11" => $this->t('11 - November'),
          "12" => $this->t('12 - December'),
        ],
        '#default_value' => $month,
        '#required' => TRUE,
      ];
	  if($config->get('cnpuber.cnp_mode')=="Yes")
	{
      $build['cc_start_year'] = [
        '#type' => 'select',
        '#title' => $this->t('Start year'),
        '#title_display' => 'invisible',
        '#options' => array_combine($year_range, $year_range),
        '#default_value' => $year,
        //'#field_suffix' => $this->t('(if present)'),
        '#required' => TRUE,
		'#attributes' => ["disabled"=>"disabled"]
      ];
	}
	else
	{
		$build['cc_start_year'] = [
        '#type' => 'select',
        '#title' => $this->t('Start year'),
        '#title_display' => 'invisible',
        '#options' => array_combine($year_range, $year_range),
        '#default_value' => $year,
       // '#field_suffix' => $this->t('(if present)'),
        '#required' => TRUE,
      ];
	}
    }

    $month = isset($order->payment_details['cc_exp_month']) ? $order->payment_details['cc_exp_month'] : date('m');
    $year = isset($order->payment_details['cc_exp_year']) ? $order->payment_details['cc_exp_year'] : date('Y');
    $year_range = range(date('Y'), date('Y') + 20);
	if($config->get('cnpuber.cnp_mode')=="Yes")
	{
    $build['cc_exp_month'] = [
      '#type' => 'select',
      '#title' => $this->t('Expiration date'),
      '#options' => [
          "01" => $this->t('01 - January'),
          "02" => $this->t('02 - February'),
          "03" => $this->t('03 - March'),
          "04" => $this->t('04 - April'),
          "05" => $this->t('05 - May'),
          "06" => $this->t('06 - June'),
          "07" => $this->t('07 - July'),
          "08" => $this->t('08 - August'),
          "09" => $this->t('09 - September'),
          "10" => $this->t('10 - October'),
          "11" => $this->t('11 - November'),
          "12" => $this->t('12 - December'),
        ],
      '#default_value' => "06",
      '#required' => TRUE,
	  //'#attributes' => ["disabled"=>"disabled"]
    ];
	 
		$build['cc_exp_year'] = [
		  '#type' => 'select',
		  '#title' => $this->t('Expiration year'),
		  '#title_display' => 'invisible',
		  '#options' => array_combine($year_range, $year_range),
		  '#default_value' => date("Y",strtotime("+1 year")),
		  //'#field_suffix' => $this->t('(if present)'),
		  '#required' => TRUE,
		  //'#attributes' => ["disabled"=>"disabled"]
		  
		];
	}
	else
	{
		$build['cc_exp_month'] = [
      '#type' => 'select',
      '#title' => $this->t('Expiration date'),
      '#options' => [
          "01" => $this->t('01 - January'),
          "02" => $this->t('02 - February'),
          "03" => $this->t('03 - March'),
          "04" => $this->t('04 - April'),
          "05" => $this->t('05 - May'),
          "06" => $this->t('06 - June'),
          "07" => $this->t('07 - July'),
          "08" => $this->t('08 - August'),
          "09" => $this->t('09 - September'),
          "10" => $this->t('10 - October'),
          "11" => $this->t('11 - November'),
          "12" => $this->t('12 - December'),
        ],
      '#default_value' => $month,
      '#required' => TRUE,
    ];
		$build['cc_exp_year'] = [
		  '#type' => 'select',
		  '#title' => $this->t('Expiration year'),
		  '#title_display' => 'invisible',
		  '#options' => array_combine($year_range, $year_range),
		  '#default_value' => $year,
		  //'#field_suffix' => $this->t('(if present)'),
		  '#required' => TRUE,
		];

	}
$build['clearfix3'] = array(
		'#prefix' => '<div class="clearfix">',
		'#suffix' => '</div>',
	);
    if (!empty($fields['issue'])) {
      // Set up the default Issue Number on the credit card form.
      if (empty($order->payment_details['cc_issue'])) {
        $default_card_issue = NULL;
      }
      elseif (!$this->validateIssueNumber($order->payment_details['cc_issue'])) {
        // Display the Issue Number as is if it does not validate so it can be
        // corrected.
        $default_card_issue = $order->payment_details['cc_issue'];
      }
      else {
        // Otherwise mask it with dashes.
        $default_card_issue = str_repeat('-', strlen($order->payment_details['cc_issue']));
      }

      $build['cc_issue'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Issue number'),
        '#default_value' => $default_card_issue,
        '#attributes' => ['autocomplete' => 'off'],
        '#size' => 2,
        '#maxlength' => 2,
        //'#field_suffix' => $this->t('(if present)'),
      ];
    }

    if (!empty($fields['cvv'])) {
      // Set up the default CVV on the credit card form.
      if (empty($order->payment_details['cc_cvv'])) {
        $default_cvv = NULL;
      }
      elseif (!$this->validateCvv($order->payment_details['cc_cvv'])) {
        // Display the CVV as is if it does not validate so it can be corrected.
        $default_cvv = $order->payment_details['cc_cvv'];
      }
      else {
        // Otherwise mask it with dashes.
        //$default_cvv = str_repeat('-', strlen($order->payment_details['cc_cvv']));
		 $default_cvv = $order->payment_details['cc_cvv'];
      }
	if($config->get('cnpuber.cnp_mode')=="Yes")
	{
      $build['cc_cvv'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Card Verification(CVV)'),
        '#default_value' => '123',
        '#attributes' => ['autocomplete' => 'off',"disabled"=>"disabled"],
        '#size' => 4,
		'#required' => TRUE,
        '#maxlength' => 4,
        '#field_suffix' => [
          '#theme' => 'uc_credit_cvv_help',
          '#method' => $order->getPaymentMethodId(),
        ],
      ];
	}
	else
	{
		$build['cc_cvv'] = [
        '#type' => 'textfield',
		'#required' => TRUE,
        '#title' => $this->t('Card Verification(CVV)'),
        '#default_value' => $default_cvv,
        '#attributes' => ['autocomplete' => 'off'],
        '#size' => 4,
        '#maxlength' => 4,
        '#field_suffix' => [
          '#theme' => 'uc_credit_cvv_help',
          '#method' => $order->getPaymentMethodId(),
        ],
      ];
	}
    }

    if (!empty($fields['bank'])) {
      $build['cc_bank'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Issuing bank'),
        '#default_value' => isset($order->payment_details['cc_bank']) ? $order->payment_details['cc_bank'] : '',
        '#attributes' => ['autocomplete' => 'off'],
        '#size' => 32,
        '#maxlength' => 64,
      ];
    }
	$build['credit_form_wrapper_end'] = array(
		'#suffix' => '</div>',
		);
   }
   else
   {
	   $build['fef_cnpuber_creditcard_diabled'] = array(
			'#prefix' => '<div><p><b>Click&Pledge Credit Card payment gateway is Disabled</b></p>',
			'#suffix' => '</div>',
		);
		$build['is_cc_enabled'] = [
			'#type' => 'hidden',
			'#value' => 'no',
		];
   }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function cartReviewTitle() {
    return $this->t('Credit card');
  }

  /**
   * {@inheritdoc}
   */
  public function cartReview(OrderInterface $order) {
    $fields = $this->getEnabledFields();

    if (!empty($fields['type'])) {
      $review[] = ['title' => $this->t('Card type'), 'data' => $order->payment_details['cc_type']];
    }
    if (!empty($fields['owner'])) {
      $review[] = ['title' => $this->t('Card owner'), 'data' => $order->payment_details['cc_owner']];
    }
    $review[] = ['title' => $this->t('Card number'), 'data' => $this->displayCardNumber($order->payment_details['cc_number'])];
    if (!empty($fields['start'])) {
      $start = $order->payment_details['cc_start_month'] . '/' . $order->payment_details['cc_start_year'];
      $review[] = ['title' => $this->t('Start date'), 'data' => strlen($start) > 1 ? $start : ''];
    }
    $review[] = ['title' => $this->t('Expiration'), 'data' => $order->payment_details['cc_exp_month'] . '/' . $order->payment_details['cc_exp_year']];
    if (!empty($fields['issue'])) {
      $review[] = ['title' => $this->t('Issue number'), 'data' => $order->payment_details['cc_issue']];
    }
    if (!empty($fields['bank'])) {
      $review[] = ['title' => $this->t('Issuing bank'), 'data' => $order->payment_details['cc_bank']];
    }

    return $review;
  }

  /**
   * {@inheritdoc}
   */
  public function orderView(OrderInterface $order) {
    $build = [];

    // Add the hidden span for the CC details if possible.
    $account = \Drupal::currentUser();
    if ($account->hasPermission('view cc details')) {
      $rows = [];

      if (!empty($order->payment_details['cc_type'])) {
        $rows[] = $this->t('Card type: @type', ['@type' => $order->payment_details['cc_type']]);
      }

      if (!empty($order->payment_details['cc_owner'])) {
        $rows[] = $this->t('Card owner: @owner', ['@owner' => $order->payment_details['cc_owner']]);
      }

      if (!empty($order->payment_details['cc_number'])) {
        $rows[] = $this->t('Card number: @number', ['@number' => $this->displayCardNumber($order->payment_details['cc_number'])]);
      }

      if (!empty($order->payment_details['cc_start_month']) && !empty($order->payment_details['cc_start_year'])) {
        $rows[] = $this->t('Start date: @date', ['@date' => $order->payment_details['cc_start_month'] . '/' . $order->payment_details['cc_start_year']]);
      }

      if (!empty($order->payment_details['cc_exp_month']) && !empty($order->payment_details['cc_exp_year'])) {
        $rows[] = $this->t('Expiration: @expiration', ['@expiration' => $order->payment_details['cc_exp_month'] . '/' . $order->payment_details['cc_exp_year']]);
      }

      if (!empty($order->payment_details['cc_issue'])) {
        $rows[] = $this->t('Issue number: @number', ['@number' => $order->payment_details['cc_issue']]);
      }

      if (!empty($order->payment_details['cc_bank'])) {
        $rows[] = $this->t('Issuing bank: @bank', ['@bank' => $order->payment_details['cc_bank']]);
      }

      $build['cc_info'] = [
        '#markup' => implode('<br />', $rows) . '<br />',
      ];
    }

    // Add the form to process the card if applicable.
    if ($account->hasPermission('process credit cards')) {
      $build['terminal'] = [
        '#type' => 'link',
        '#title' => $this->t('Process card'),
        '#url' => Url::fromRoute('uc_credit.terminal', [
          'uc_order' => $order->id(),
          'uc_payment_method' => $order->getPaymentMethodId(),
        ]),
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function customerView(OrderInterface $order) {
    $build = [];

    if (!empty($order->payment_details['cc_number'])) {
      $build['#markup'] = $this->t('Card number') . ':<br />' . $this->displayCardNumber($order->payment_details['cc_number']);
    }

    return $build;

  }

  /**
   * {@inheritdoc}
   */
  public function orderEditDetails(OrderInterface $order) {
    return $this->t('Use the terminal available through the<br />%button button on the View tab to<br />process credit card payments.', ['%button' => $this->t('Process card')]);
  }

  /**
   * {@inheritdoc}
   */
  public function cartProcess(OrderInterface $order, array $form, FormStateInterface $form_state) {
	$config = \Drupal::config('cnpuber.mainsettings');
    if (!$form_state->hasValue(['panes', 'payment', 'details', 'cc_number'])) {
      return;
    }

    $fields = $this->getEnabledFields();

    // Fetch the CC details from the $_POST directly.
    $cc_data = $form_state->getValue(['panes', 'payment', 'details']);
    $cc_data['cc_number'] = str_replace(' ', '', $cc_data['cc_number']);

    // Recover cached CC data in form state, if it exists.
    if (isset($cc_data['payment_details_data'])) {
      $cache = uc_credit_cache(base64_decode($cc_data['payment_details_data']));
      unset($cc_data['payment_details_data']);
    }

    // Account for partial CC numbers when masked by the system.
    if (substr($cc_data['cc_number'], 0, strlen(t('(Last4)'))) == $this->t('(Last4)')) {
      // Recover the number from the encrypted data in the form if truncated.
      if (isset($cache['cc_number'])) {
        $cc_data['cc_number'] = $cache['cc_number'];
      }
      else {
        $cc_data['cc_number'] = '';
      }
    }

    // Account for masked CVV numbers.
    if (!empty($cc_data['cc_cvv']) && $cc_data['cc_cvv'] == str_repeat('-', strlen($cc_data['cc_cvv']))) {
      // Recover the number from the encrypted data in $_POST if truncated.
      if (isset($cache['cc_cvv'])) {
        $cc_data['cc_cvv'] = $cache['cc_cvv'];
      }
      else {
        $cc_data['cc_cvv'] = '';
      }
    }

    // Go ahead and put the CC data in the payment details array.
    $order->payment_details = $cc_data;

    // Default our value for validation.
    $return = TRUE;

	//validate c&p no.of payments
	//print_r($cc_data);
	if($cc_data['cnppayoption'] =="Recurring")
	{
		if($cc_data["fef_cnp_noof_payments"] == "Indefinite Recurring Only")
		{
			if($cc_data['fef_recuring_type_option']== "Installment")
			{
				//fef_recuring_type_option
				$form_state->setErrorByName('panes][payment][details][fef_recuring_type_option', $this->t('Recurring type installment not allow indefinite number of payments'));
				$return = FALSE;
			}
		}
	}
	
	if($cc_data["fef_cnp_noof_payments"] != "Indefinite Recurring Only")
	{
		if($cc_data['fef_cnp_noof_payments']<=0 && $cc_data['fef_cnp_noof_payments']!="")
		{
			//$form_state->setError($element['fef_cnp_noof_payments'], t('No.of payments should be more than 1'));
			//return false;
			$form_state->setErrorByName('panes][payment][details][fef_cnp_noof_payments', $this->t('No.of payments should be more than 1'));
			$return = FALSE;
		}
		else
		{
			$noofPays=$cc_data['fef_cnp_noof_payments'];
			//print_r($noofPays."hi");
			//exit();
			if($cc_data['fef_recuring_type_option']== "Installment")
			{
				if($noofPays<=1)
				{
					//$form_state->setError($element['fef_cnp_noof_payments'], t('Please enter Number of payments value between 2 to 998 for installment'));
					//return false;
					$form_state->setErrorByName('panes][payment][details][fef_cnp_noof_payments', $this->t('Please enter Number of payments value between 2 to 998 for installment'));
					$return = FALSE;
				}
				if($noofPays>=999)
				{
					//$form_state->setError($element['fef_cnp_noof_payments'], t('Please enter Number of payments value between 2 to 998 for installment'));
					//return false;
					$form_state->setErrorByName('panes][payment][details][fef_cnp_noof_payments', $this->t('Please enter Number of payments value between 2 to 998 for installment'));
					$return = FALSE;
				}
			}
			else
			{
				if($noofPays<=1)
				{
					//$form_state->setError($element['fef_cnp_noof_payments'], t('Please enter Number of payments value between 2 to 999 for Subscription'));
					//return false;
					if(!($cc_data['cnppayoption'] =="One Time Only"))
					{
						$form_state->setErrorByName('panes][payment][details][fef_cnp_noof_payments', $this->t('Please enter Number of payments value between 2 to 999 for Subscription'));
						$return = FALSE;
					}
				}
				if($noofPays>=1000)
				{
					//$form_state->setError($element['fef_cnp_noof_payments'], t('Please enter Number of payments value between 2 to 999 for Subscription'));
					//return false;
					$form_state->setErrorByName('panes][payment][details][fef_cnp_noof_payments', $this->t('Please enter Number of payments value between 2 to 999 for Subscription'));
					$return = FALSE;
				}
			}
			//no.of payments values should not be more than max.no.of payments defiend in dashbaord
			if($config->get('cnpuber.cnp_recurring_no_of_payments_options')=="openfield")
			{
				$maxNo=$config->get('cnpuber.cnp_recurring_max_no_payment');
				//echo $maxNo;
				if($maxNo != "")
				{
					if($maxNo != 0)
					{
						if($maxNo<$noofPays)
						{
							//$form_state->setError($element['fef_cnp_noof_payments'], t('Please enter Number of payments value between 2 to '.$maxNo.' for Subscription'));
							//return false;
							$form_state->setErrorByName('panes][payment][details][fef_cnp_noof_payments', $this->t('Please enter Number of payments value between 2 to '.$maxNo.' for Subscription'));
							$return = FALSE;
						}
					}
				}
			}
		}
	}
	
	//validate accepted cards
	
	//$card_type = CreditCard::detectType($cc_data['cardnumber']);
	
    // Make sure an owner value was entered.
    if (!empty($fields['owner']) && empty($cc_data['cc_owner'])) {
      $form_state->setErrorByName('panes][payment][details][cc_owner', $this->t('Enter the owner name as it appears on the card.'));
      $return = FALSE;
    }
	
    // Validate the credit card number.
    if (!$this->validateCardNumber($cc_data['cc_number'])) {
      $form_state->setErrorByName('panes][payment][details][cc_number', $this->t('You have entered an invalid credit card number.'));
      $return = FALSE;
    }

    // Validate the start date (if entered).
    if (!empty($fields['start']) && !$this->validateStartDate($cc_data['cc_start_month'], $cc_data['cc_start_year'])) {
      $form_state->setErrorByName('panes][payment][details][cc_start_month', $this->t('The start date you entered is invalid.'));
      $form_state->setErrorByName('panes][payment][details][cc_start_year');
      $return = FALSE;
    }

    // Validate the card expiration date.
    if (!$this->validateExpirationDate($cc_data['cc_exp_month'], $cc_data['cc_exp_year'])) {
      $form_state->setErrorByName('panes][payment][details][cc_exp_month', $this->t('The credit card you entered has expired.'));
      $form_state->setErrorByName('panes][payment][details][cc_exp_year');
      $return = FALSE;
    }

    // Validate the issue number (if entered). With issue numbers, '01' is
    // different from '1', but is_numeric() is still appropriate.
    if (!empty($fields['issue']) && !$this->validateIssueNumber($cc_data['cc_issue'])) {
      $form_state->setErrorByName('panes][payment][details][cc_issue', $this->t('The issue number you entered is invalid.'));
      $return = FALSE;
    }

    // Validate the CVV number if enabled.
    if (!empty($fields['cvv']) && !$this->validateCvv($cc_data['cc_cvv'])) {
      $form_state->setErrorByName('panes][payment][details][cc_cvv', $this->t('You have entered an invalid CVV number.'));
      $return = FALSE;
    }

    // Validate the bank name if enabled.
    if (!empty($fields['bank']) && empty($cc_data['cc_bank'])) {
      $form_state->setErrorByName('panes][payment][details][cc_bank', $this->t('You must enter the issuing bank for that card.'));
      $return = FALSE;
    }

    // Initialize the encryption key and class.
    $key = uc_credit_encryption_key();
    $crypt = \Drupal::service('uc_store.encryption');

    // Store the encrypted details in the session for the next pageload.
    // We are using base64_encode() because the encrypt function works with a
    // limited set of characters, not supporting the full Unicode character
    // set or even extended ASCII characters that may be present.
    // base64_encode() converts everything to a subset of ASCII, ensuring that
    // the encryption algorithm does not mangle names.
    $session = \Drupal::service('session');
    $session->set('sescrd', $crypt->encrypt($key, base64_encode(serialize($order->payment_details))));

    // Log any errors to the watchdog.
    uc_store_encryption_errors($crypt, 'uc_credit');

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function orderLoad(OrderInterface $order) {
    // Load the CC details from the credit cache if available.
    $order->payment_details = uc_credit_cache();

    // Otherwise load any details that might be stored in the data array.
    if (empty($order->payment_details) && isset($order->data->cc_data)) {
      $order->payment_details = uc_credit_cache($order->data->cc_data);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function orderSave(OrderInterface $order) {
    // Save only some limited, PCI compliant data.
    $cc_data = $order->payment_details;
    $cc_data['cc_number'] = substr($cc_data['cc_number'], -4);
    unset($cc_data['cc_cvv']);

    // Stuff the serialized and encrypted CC details into the array.
    $crypt = \Drupal::service('uc_store.encryption');
    $order->data->cc_data = $crypt->encrypt(uc_credit_encryption_key(), base64_encode(serialize($cc_data)));
    uc_store_encryption_errors($crypt, 'uc_credit');
  }

  /**
   * {@inheritdoc}
   */
  public function orderSubmit(OrderInterface $order) {
    // Attempt to process the credit card payment.
    if (!$this->processPayment($order, $order->getTotal(), $this->configuration['txn_type'])) {
      return $this->t('We were unable to process your credit card payment. Please verify your details and try again.');
    }
  }

  /**
   * Process a payment through the credit card gateway.
   *
   * @param \Drupal\uc_order\OrderInterface $order
   *   The order that is being processed.
   * @param float $amount
   *   The amount of the payment we're attempting to collect.
   * @param string $txn_type
   *   The transaction type, one of the UC_CREDIT_* constants.
   * @param string $reference
   *   (optional) The payment reference, where needed for specific transaction
   *   types.
   *
   * @return bool
   *   TRUE or FALSE indicating whether or not the payment was processed.
   */
  public function processPayment(OrderInterface $order, $amount, $txn_type, $reference = NULL) {
    // Ensure the cached details are loaded.
    // @todo Figure out which parts of this call are strictly necessary.
    $this->orderLoad($order);
	
	$XMLData=$this->generateCnPXML($order);
	//print_r($XMLData);
	//exit();
	//send XML data to API
	
	$connect    = array('soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 0);
	$client     = new SoapClient('https://paas.cloud.clickandpledge.com/paymentservice.svc?wsdl', $connect);
	$soapParams = array('instruction'=>$XMLData);
	
	$response = $client->Operation($soapParams);
//echo "<pre>";
//print_r($response->OperationResult);
//exit();
	//print_r($response->OperationResult->ResultCode);

	if($response->OperationResult->ResultCode==0)
	{
		 $result = $this->chargeCard($order, $amount, $txn_type, $reference);
		$user = \Drupal::currentUser();
		// If the payment processed successfully...
		if ($result['success'] === TRUE) {
			$successMsg=$response->OperationResult->ResultData;
		$txID=$response->OperationResult->TransactionNumber;
		//drupal_set_message($this->t('Your payment was successful with Order id : @orderid and Transaction id : @transaction_id', ['@orderid' => $order->order_id, '@transaction_id' => $txID]));
		$message = $this->t('Transaction id: @transaction_id', ['@transaction_id' =>  $txID]);
		uc_order_comment_save($order->id(), $user->id(), $message, 'admin');
		drupal_set_message($this->t('Your payment was successful with  Transaction id : @transaction_id', ['@transaction_id' => $txID]));
		
		  // Log the payment to the order if not disabled.
		  if (!isset($result['log_payment']) || $result['log_payment'] !== FALSE) {
			uc_payment_enter($order->id(), $this->getPluginId(), $amount, empty($result['uid']) ? 0 : $result['uid'], empty($result['data']) ? NULL : $result['data'], empty($result['comment']) ? '' : $result['comment']);
		  }
		}

	}
	else
	{
		 //$result = $this->chargeCard($order, $amount, $txn_type, $reference);
		  // Otherwise display the failure message in the logs.
		  \Drupal::logger('uc_payment')->warning('Payment failed for order @order_id: @message', ['@order_id' => $order->id(), '@message' => $result['message'], 'link' => $order->toLink($this->t('view order'))->toString()]);
		
	}
    //exit("XML Preparing");
    

    return $result['success'];
  }

  /**
   * Called when a credit card should be processed.
   *
   * @param \Drupal\uc_order\OrderInterface $order
   *   The order that is being processed. Credit card details supplied by the
   *   user are available in $order->payment_details[].
   * @param float $amount
   *   The amount that should be charged.
   * @param string $txn_type
   *   The transaction type, one of the UC_CREDIT_* constants.
   * @param string $reference
   *   (optional) The payment reference, where needed for specific transaction
   *   types.
   *
   * @return array
   *   Returns an associative array with the following members:
   *   - "success": TRUE if the transaction succeeded, FALSE otherwise.
   *   - "message": a human-readable message describing the result of the
   *     transaction.
   *   - "log_payment": TRUE if the transaction should be regarded as a
   *     successful payment.
   *   - "uid": The user ID of the person logging the payment, or 0 if the
   *     payment was processed automatically.
   *   - "comment": The comment string, markup allowed, to enter in the
   *     payment log.
   *   - "data": Any data that should be serialized and stored with the payment.
   *
   * @todo Replace the return array with a typed object.
   */
  abstract protected function chargeCard(OrderInterface $order, $amount, $txn_type, $reference = NULL);

  /**
   * Returns a credit card number with appropriate masking.
   *
   * @param string $number
   *   Credit card number as a string.
   *
   * @return string
   *   Masked credit card number - just the last four digits.
   */
  protected function displayCardNumber($number) {
    /*if (strlen($number) == 4) {
      return $this->t('(Last 4) @digits', ['@digits' => $number]);
    }

    return str_repeat('-', 12) . substr($number, -4);*/
	return $number;
  }

  /**
   * Validates a credit card number during checkout.
   *
   * @param string $number
   *   Credit card number as a string.
   *
   * @return bool
   *   TRUE if card number is valid according to the Luhn algorithm.
   *
   * @see https://en.wikipedia.org/wiki/Luhn_algorithm
   */
  protected function validateCardNumber($number) {
    $id = substr($number, 0, 1);
    $types = $this->getEnabledTypes();
    if (($id == 3 && empty($types['amex'])) ||
      ($id == 4 && empty($types['visa'])) ||
      ($id == 5 && empty($types['mastercard'])) ||
      ($id == 6 && empty($types['discover'])) ||
      !ctype_digit($number)) {
      return FALSE;
    }

    $total = 0;
    for ($i = 0; $i < strlen($number); $i++) {
      $digit = substr($number, $i, 1);
      if ((strlen($number) - $i - 1) % 2) {
        $digit *= 2;
        if ($digit > 9) {
          $digit -= 9;
        }
      }
      $total += $digit;
    }

    if ($total % 10 != 0) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Validates a CVV number during checkout.
   *
   * @param string $cvv
   *   CVV number as a string.
   *
   * @return bool
   *   TRUE if CVV has the correct number of digits.
   */
  protected function validateCvv($cvv) {
    $digits = [];

    $types = $this->getEnabledTypes();
    if (!empty($types['visa']) ||
      !empty($types['mastercard']) ||
      !empty($types['discover'])) {
      $digits[] = 3;
    }
    if (!empty($types['amex'])) {
      $digits[] = 4;
    }

    // Fail validation if it's non-numeric or an incorrect length.
    if (!is_numeric($cvv) || (count($digits) > 0 && !in_array(strlen($cvv), $digits))) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Validates a start date on a card.
   *
   * @param int $month
   *   The 1 or 2-digit numeric representation of the month, i.e. 1, 6, 12.
   * @param int $year
   *   The 4-digit numeric representation of the year, i.e. 2008.
   *
   * @return bool
   *   TRUE for cards whose start date is blank (both month and year) or in the
   *   past, FALSE otherwise.
   */
  protected function validateStartDate($month, $year) {
    if (empty($month) && empty($year)) {
      return TRUE;
    }

    if (empty($month) || empty($year)) {
      return FALSE;
    }

    if ($year > date('Y')) {
      return FALSE;
    }
    elseif ($year == date('Y')) {
      if ($month > date('n')) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Validates an expiration date on a card.
   *
   * @param int $month
   *   The 1 or 2-digit numeric representation of the month, i.e. 1, 6, 12.
   * @param int $year
   *   The 4-digit numeric representation of the year, i.e. 2008.
   *
   * @return bool
   *   TRUE if expiration date is in the future, FALSE otherwise.
   */
  protected function validateExpirationDate($month, $year) {
    if ($year < date('Y')) {
      return FALSE;
    }
    elseif ($year == date('Y')) {
      if ($month < date('n')) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Validates an issue number on a card.
   *
   * @param string $issue
   *   The issue number.
   *
   * @return bool
   *   TRUE if the issue number if valid, FALSE otherwise.
   */
  protected function validateIssueNumber($issue) {
    if (empty($issue) || (is_numeric($issue) && $issue > 0)) {
      return TRUE;
    }

    return FALSE;
  }

	public function displayRecurringFormUber(array $form, FormStateInterface $form_state)
	{
		//print_r($_POST['panes']['payment']['details']['fef_payment_options']);
		$ajax_response = new AjaxResponse();
		//$values = $form_state->getValue($form['#parents']);
		//$payOption=$values['payment_information']['add_payment_method']['payment_details']['fef_payment_options'];
		$payOption=$_POST['panes']['payment']['details']['fef_payment_options'];
		if($payOption=="Recurring")
		{
			$ajax_response->addCommand(new CssCommand('#fef_recurr_options_division', array('display' => 'block')));
		}
		else
		{
			$ajax_response->addCommand(new CssCommand('#fef_recurr_options_division', array('display' => 'none')));
		}
		return $ajax_response;
		
	}
	
	public function generateCnPXML($order)
    {
		$connection= \Drupal::database();
        $config = \Drupal::config('cnpuber.mainsettings');
		$cc_data = $order->payment_details;
		//collect tax information
		$Tdata=uc_tax_uc_calculate_tax($order);
		$Tobj=$Tdata['tax']->data['tax'];
		
		$lit=$this->getProtectedValue($Tobj, "line_item_types");
		$taxObj=$this->getProtectedValue($Tobj, "settings");
		$shippingTax=0;
		$taxOfTax=0;
        //print_r($config);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement('CnPAPI', '');
        $root->setAttribute("xmlns", "urn:APISchema.xsd");
        $root = $dom->appendChild($root);
		
        $version = $dom->createElement("Version", "1.0");
        $version = $root->appendChild($version);

        $engine = $dom->createElement('Engine', '');
        $engine = $root->appendChild($engine);

        $application = $dom->createElement('Application', '');
        $application = $engine->appendChild($application);

        $applicationid = $dom->createElement('ID', 'CnP_Drupal_Uber');
        $applicationid = $application->appendChild($applicationid);

        $applicationname = $dom->createElement('Name', 'CnP_Drupal_Uber');
        $applicationid = $application->appendChild($applicationname);

            //Getting drupal Version
            $drupalVersion=\Drupal::VERSION;


            //Getting Commerce Version
            $path = drupal_get_path('module', "uc_cart") . '/uc_cart.info.yml';
            if(file_exists($path))
            {
                    $file_path = DRUPAL_ROOT."/";
                    $fullpath=$file_path.$path;
                    $file_contents = file_get_contents($fullpath);
                    //echo "<pre>";
                    $ymldata = Yaml::decode($file_contents);
                    //print_r();
                    $uberVersion=$ymldata['version'];
            }
            else
            {
                     $uberVersion="";
            }
        $versionString="2.000.000/Drupal:v".$drupalVersion."/ubercart:v".$uberVersion;
        $applicationversion = $dom->createElement('Version', $versionString);  // 2.000.000.000.20130103 Version-Minor change-Bug Fix-Internal Release Number -Release Date
        $applicationversion = $application->appendChild($applicationversion);

        $request = $dom->createElement('Request', '');
        $request = $engine->appendChild($request);

        $operation = $dom->createElement('Operation', '');
        $operation = $request->appendChild( $operation );
        
        $operationtype = $dom->createElement('OperationType', 'Transaction');
        $operationtype = $operation->appendChild($operationtype);

        $ipaddress = $dom->createElement('IPAddress', $_SERVER['REMOTE_ADDR']);
        $ipaddress = $operation->appendChild($ipaddress);

        $httpreferrer=$dom->createElement('UrlReferrer',$_SERVER['HTTP_REFERER']);
        $httpreferrer=$operation->appendChild($httpreferrer);

        $authentication = $dom->createElement('Authentication', '');
        $authentication = $request->appendChild($authentication);
        
        //getting account guid form database table
	$prefix=$connection->tablePrefix();
	$table_name = $prefix.'dp_cnp_uber_jbcnpaccountsinfo';
	$accid=$config->get('cnpuber.cnp_accid');
	$sql = "SELECT * FROM " .$table_name." where cnpaccountsinfo_orgid='$accid'";
	$query = $connection->query($sql);
	$row=$query->fetchAll();
	
    $accounttype = $dom->createElement('AccountGuid', $row[0]->cnpaccountsinfo_accountguid );
    //$accounttype = $dom->createElement('AccountGuid', "2432");
    $accounttype = $authentication->appendChild($accounttype);

    $accountid = $dom->createElement('AccountID', $config->get('cnpuber.cnp_accid'));
    $accountid = $authentication->appendChild($accountid);
    
    $cporder = $dom->createElement('Order', '');
    $cporder = $request->appendChild($cporder);
	
	if($config->get('cnpuber.cnp_mode')=="No")
	{
		$orderMode="Production";
	}
	else
	{
		$orderMode="Test";
	}
	
    $ordermode = $dom->createElement('OrderMode', $orderMode);
    $ordermode = $cporder->appendChild($ordermode);
    if($config->get('cnpuber.cnp_camp_urls'))
	{
		$camp_url=$config->get('cnpuber.cnp_camp_urls');
	}
	else
	{
		$camp_url="";
	}
    //$connectcampalias = $dom->createElement('ConnectCampaignAlias', "Feed The poor");
    $connectcampalias = $dom->createElement('ConnectCampaignAlias', $this->safeString($camp_url,50));
    $connectcampalias = $cporder->appendChild($connectcampalias);
    
	$cardholder = $dom->createElement('CardHolder', '');
        $cardholder = $cporder->appendChild($cardholder);

        $billinginfo = $dom->createElement('BillingInformation', '');
        $billinginfo = $cardholder->appendChild($billinginfo);

        $billfirst_name = $dom->createElement('BillingFirstName', $this->safeString($order->getAddress("billing")->getFirstName(),50));
        $billfirst_name = $billinginfo->appendChild($billfirst_name);

        $billlast_name = $dom->createElement('BillingLastName', $this->safeString($order->getAddress("billing")->getLastName(),50));
        $billlast_name = $billinginfo->appendChild($billlast_name);

        $bill_email = $dom->createElement('BillingEmail', $order->getEmail());
        $bill_email = $billinginfo->appendChild($bill_email);

        $billingaddress=$dom->createElement('BillingAddress','');
        $billingaddress=$cardholder->appendChild($billingaddress);

        $billingaddress1=$dom->createElement('BillingAddress1',$this->safeString($order->getAddress("billing")->getStreet1(),100));
        $billingaddress1=$billingaddress->appendChild($billingaddress1);

        if(!empty($order->getAddress("billing")->getStreet2())) {
                $billingaddress2=$dom->createElement('BillingAddress2',$this->safeString($order->getAddress("billing")->getStreet2(),100));
                $billingaddress2=$billingaddress->appendChild($billingaddress2);
        }
        if(!empty($order->getAddress("billing")->getCity())) {
        $billing_city=$dom->createElement('BillingCity',$this->safeString($order->getAddress("billing")->getCity(),50));
        $billing_city=$billingaddress->appendChild($billing_city);
        }

        if(!empty($order->getAddress("billing")->getZone())) {
        $billing_state=$dom->createElement('BillingStateProvince',$this->safeString($order->getAddress("billing")->getZone(),50));
        $billing_state=$billingaddress->appendChild($billing_state);
        }

        if(!empty($order->getAddress("billing")->getPostalCode())) {
        $billing_zip=$dom->createElement('BillingPostalCode',$this->safeString( $order->getAddress("billing")->getPostalCode(),20 ));
        $billing_zip=$billingaddress->appendChild($billing_zip);
        }
        
        $ModulePath = \Drupal::moduleHandler()->getModule('uc_clickandpledge')->getPath();
	$host = \Drupal::request()->getHost();
	//$host= \Drupal::request()->getSchemeAndHttpHost();
	$countries = simplexml_load_file("http://".$host."/".base_path().$ModulePath."/countries/Countries.xml");
	
	//$countries = simplexml_load_file("http://dev.clickandpledge.biz/web/PHP/ram/drupal-8.5.4/modules/commerce_cnp/countries/Countries.xml");
	//var_dump($countries);
	
	$billing_country_id = '';
	foreach( $countries as $country ){
		if( $country->attributes()->Abbrev == $order->getAddress("billing")->getCountry() ){
			$billing_country_id = $country->attributes()->Code;
		} 
	}
	
	if(!empty($order->getAddress("billing")->getCountry())) {
	$billing_country=$dom->createElement('BillingCountryCode',str_pad($billing_country_id, 3, "0", STR_PAD_LEFT));
	$billing_country=$billingaddress->appendChild($billing_country);
	} 
        
	/***************************** Shipping Information*************************************/
        
        $shippinginfo = $dom->createElement('ShippingInformation', '');
        $shippinginfo = $cardholder->appendChild($shippinginfo);
        
        $ShippingContactInformation=$dom->createElement('ShippingContactInformation','');
        $ShippingContactInformation=$shippinginfo->appendChild($ShippingContactInformation);
        
        if($order->getAddress("delivery")->getFirstName() != "")
        {
            $shipping_first_name=$dom->createElement('ShippingFirstName',$this->safeString($order->getAddress("delivery")->getFirstName(),50));
            $shipping_first_name=$ShippingContactInformation->appendChild($shipping_first_name);
        }
        if($order->getAddress("delivery")->getLastName() != "")
        {
            $shipping_last_name=$dom->createElement('ShippingLastName',$this->safeString($order->getAddress("delivery")->getLastName(),50));
            $shipping_last_name=$ShippingContactInformation->appendChild($shipping_last_name);
        }
        $shippingaddress=$dom->createElement('ShippingAddress','');
        $shippingaddress=$shippinginfo->appendChild($shippingaddress);
        
        if($order->getAddress("delivery")->getStreet1() != "")
        {
                $ship_address1=$dom->createElement('ShippingAddress1',$this->safeString($order->getAddress("delivery")->getStreet1(),100));
                $ship_address1=$shippingaddress->appendChild($ship_address1);
        }
        if($order->getAddress("delivery")->getStreet2() != "")
        {
                $ship_address2=$dom->createElement('ShippingAddress2',$this->safeString($order->getAddress("delivery")->getStreet2(),100));
                $ship_address2=$shippingaddress->appendChild($ship_address2);
        }
        if($order->getAddress("delivery")->getCity() != "")
        {
                $ship_city=$dom->createElement('ShippingCity',$this->safeString($order->getAddress("delivery")->getCity(), 50));
                $ship_city=$shippingaddress->appendChild($ship_city);
        }

        
        if($order->getAddress("delivery")->getZone() != "")
        {
                $ship_state=$dom->createElement('ShippingStateProvince',$this->safeString($order->getAddress("delivery")->getZone(), 50));
                $ship_state=$shippingaddress->appendChild($ship_state);
        }

        
        if($order->getAddress("delivery")->getPostalCode() != "")
        {
                $ship_zip=$dom->createElement('ShippingPostalCode',$this->safeString($order->getAddress("delivery")->getPostalCode(), 20));
                $ship_zip=$shippingaddress->appendChild($ship_zip);
        }

        $getCountryCode=$order->getAddress("delivery")->getCountry();

        $shipping_country_id = '';
        foreach( $countries as $country ){
            //print_r($country->attributes()->Abbrev."<br>");
                if( $country->attributes()->Abbrev == $order->getAddress("delivery")->getCountry() ){
                        $shipping_country_id = $country->attributes()->Code;
                } 
        }

        if($getCountryCode != "")
        {
            $ship_country=$dom->createElement('ShippingCountryCode',str_pad($shipping_country_id, 3, "0", STR_PAD_LEFT));
            $ship_country=$shippingaddress->appendChild($ship_country);
        }
        
        /*************************** Manipulate Custom Fields ***************************************/
        
        //billing custom Fields
	if($order->getAddress("billing")->getCompany()!="")
	{
		$custom_fields['Billing Company Name'] = $order->getAddress("billing")->getCompany();
	}
        if($order->getAddress("billing")->getEmail() != "")
        {
            $custom_fields['Billing Email'] = $order->getAddress("billing")->getEmail();
        }
        if($order->getAddress("billing")->getPhone() != "")
        {
            $custom_fields['Billing Phone'] = $order->getAddress("billing")->getPhone();
        }
	//shipping custom fields
        if($order->getAddress("delivery")->getCompany()!="")
	{
		$custom_fields['Shipping Company Name'] = $order->getAddress("delivery")->getCompany();
	}
        if($order->getAddress("delivery")->getEmail() != "")
        {
            $custom_fields['Shipping Email'] = $order->getAddress("delivery")->getEmail();
        }
        if($order->getAddress("delivery")->getPhone() != "")
        {
            $custom_fields['Shipping Phone'] = $order->getAddress("delivery")->getPhone();
        }
	
	if(count($custom_fields) > 0) {
		
            $customfieldlist = $dom->createElement('CustomFieldList','');
            $customfieldlist = $cardholder->appendChild($customfieldlist);
            foreach($custom_fields as $cfk=>$cfv)
            {
                $customfield = $dom->createElement('CustomField','');
                $customfield = $customfieldlist->appendChild($customfield);

                $fieldname   = $dom->createElement('FieldName',$this->safeString($cfk, 200));
                $fieldname   = $customfield->appendChild($fieldname);
                $fieldvalue  = $dom->createElement('FieldValue',$this->safeString($cfv, 500));
                $fieldvalue  = $customfield->appendChild($fieldvalue);
            }
	}
        $paymentmethod = $dom->createElement('PaymentMethod', '');
        $paymentmethod = $cardholder->appendChild($paymentmethod);
        
        $payment_type = $dom->createElement('PaymentType', 'CreditCard');
        $payment_type = $paymentmethod->appendChild($payment_type);
		
        $creditcard = $dom->createElement('CreditCard', '');
        $creditcard = $paymentmethod->appendChild($creditcard);
      
        if(count($cc_data)>0)
        {
           if(array_key_exists("cc_owner",$cc_data))
           {
               if($cc_data['cc_owner']!="")
               {
                    $credit_name = $dom->createElement('NameOnCard',$this->safeString($cc_data['cc_owner'],50));
                    $credit_name = $creditcard->appendChild($credit_name);
               }
               else
               {
                    $cfname=$order->getAddress("billing")->getFirstName();
                    $clname=$order->getAddress("billing")->getLastName();
                    $fullname=$cfname." ".$clname;
                    $credit_name = $dom->createElement('NameOnCard',$this->safeString($fullname,50));
                    $credit_name = $creditcard->appendChild($credit_name);
               }
           }
           else
           {
                $cfname=$order->getAddress("billing")->getFirstName();
                $clname=$order->getAddress("billing")->getLastName();
                $fullname=$cfname." ".$clname;
                $credit_name = $dom->createElement('NameOnCard',$this->safeString($fullname,50));
                $credit_name = $creditcard->appendChild($credit_name);
           }
          
            $credit_number = $dom->createElement('CardNumber', $this->safeString( str_replace(' ', '', $cc_data['cc_number']), 19));
            //$credit_number = $dom->createElement('CardNumber', $this->safeString( str_replace(' ', '', '4111111111111111'), 17));
            $credit_number = $creditcard->appendChild($credit_number);
           
            $credit_cvv = $dom->createElement('Cvv2', $cc_data['cc_cvv']);
            $credit_cvv = $creditcard->appendChild($credit_cvv);
            
            $month = (string)$cc_data['cc_exp_month'];
			
            $year = (string)$cc_data['cc_exp_year'];
            $year = substr($year, -2);
            $exp_date = $month ."/". $year;
            
            $credit_expdate = $dom->createElement('ExpirationDate', $exp_date);
            $credit_expdate = $creditcard->appendChild($credit_expdate);
        }
        
        $orderitemlist = $dom->createElement('OrderItemList', '');
        $orderitemlist = $cporder->appendChild($orderitemlist);
        
        $UnitPriceCalculate = $UnitTaxCalculate = $ShippingValueCalculate = $ShippingTaxCalculate = $TotalDiscountCalculate = 0;	
        $pi=103200;
        $grandTotalPrice="";
        //echo "<pre>";
        //print_r($order->getTotal());
        //print_r("Rate:".$this->configuration['rate']);
        //echo $_SESSION['unittax'];
		//exit();
		
		if(count($order->getLineItems())>0)
		{
			foreach($order->getLineItems() as $adj)
			{
				if($adj['type']=="shipping")
				{
					$shipValue=$adj['amount'];
				}
			}
		}
		else
		{
			$shipValue=0;
		}
		//get total products
		$totalProducts=0;
		$lastTax=0;
		$pL=1;
		foreach ((array) $order->products as $product)
		{
			$totalProducts++;
		}
		
		$adjustments=$order->getLineItems();
		$totalTaxApplied=$this->getTotalTax($adjustments,$product,$shipValue,$order);
		//print_r($totalTaxApplied);
		$individualTax=$totalTaxApplied/$totalProducts;
		//echo $totalProducts;
		//echo "<pre>";
		//echo "Total".$individualTax.'<br>';
		//print_r($adjustments);
		 //$taxes=uc_taxes_calculate($order)
		//print_r($taxes);
        foreach ((array) $order->products as $product) {
            ++$pi;
            
			
            $orderitem = $dom->createElement('OrderItem', '');
            $orderitem = $orderitemlist->appendChild($orderitem);

            $itemid = $dom->createElement('ItemID', $pi);
            $itemid = $orderitem->appendChild($itemid);
            
            $itemname = $dom->createElement('ItemName', $product->title->value);
            $itemname = $orderitem->appendChild($itemname);

            $quntity = $dom->createElement('Quantity', $product->qty->value);
            $quntity = $orderitem->appendChild($quntity);
            
		if($cc_data['fef_payment_options']=="Recurring")
		{
			if($cc_data['fef_recuring_type_option']=="Installment")
			{
				//installment
				if($cc_data['fef_cnp_noof_payments']=="Indefinite Recurring Only")
				{
					$no_of_payments=$cc_data['fef_cnp_noof_payments'];
					$Unit_Price = ($this->number_formatprc(($product->price->value/999),2,'.','')*100);
					$UnitPriceCalculate += ($this->number_formatprc(($product->price->value/999),2,'.','')*$product->qty->value);
				}
				else
				{
					//for selected no.of payments
					//print_r("Price".$product->price->value."<br>");
					
					$no_of_payments=$cc_data['fef_cnp_noof_payments'];
					//print_r("NOP".$no_of_payments."<br>");
					$Unit_Price=($this->number_formatprc(($product->price->value/$no_of_payments),2,'.','')*100);
					
					$UnitPriceCalculate += ($this->number_formatprc(($product->price->value/$no_of_payments),2,'.','')*$product->qty->value);
				}
				$unitprice = $dom->createElement('UnitPrice', $Unit_Price);
				$unitprice = $orderitem->appendChild($unitprice);
				
			}
			else
			{
				//subscription
				$unitprice = $dom->createElement('UnitPrice', $this->number_formatprc($product->price->value,2,'.','')*100);
				$unitprice = $orderitem->appendChild($unitprice);
				$UnitPriceCalculate += ($product->price->value*$product->qty->value);
			}
		}
		else
		{
			//One Time Only
			$unitprice = $dom->createElement('UnitPrice', $this->number_formatprc($product->price->value,2,'.','')*100);
			$unitprice = $orderitem->appendChild($unitprice);
			$UnitPriceCalculate += ($product->price->value*$product->qty->value);
		}
        //get the line ITEMS: tax, discounts,shipping etc
		
		
		
		
		//$indTax=0;
		if(count($adjustments)>0)
        {
            $totalTax=0;
            $totalDiscount=0;
            $unitD=1;
			//print_r($product->qty->value);
			$indTax=$individualTax/$product->qty->value;
			
			if($pL==$totalProducts)
			{
				$indTax=$totalTaxApplied-$lastTax;
				//echo $indTax."<br>";
				//echo "Final".$indTax."<br>";
			}
			else
			{
				$indTax=$individualTax/$product->qty->value;
				$lastTax+=$individualTax;
			}
			
			//$no_of_payments=$cc_data['fef_cnp_noof_payments'];
			$indTax=$this->number_formatprc($individualTax/$product->qty->value,2,".",'');
			$changeTax=$this->number_formatprc($indTax/$no_of_payments,2,".",'');
		
			$changeTax=$changeTax*$no_of_payments*$product->qty->value;
			
			
			
			$diffTax=$individualTax-$changeTax;
			
			
			$sumDiffTax=$this->number_formatprc($diffTax/$product->qty->value,2,".",'');
			//echo "Tax".$sumDiffTax."<br>";
			
			
			$indTax=$individualTax/$product->qty->value;
			//$indTax=$indTax+$sumDiffTax;
            foreach($adjustments as $adjustment)
            {
				
				$taxOfTax=0;
				$taxOfTaxOfTax=0;
				
                if($adjustment['type']=="tax")
                {
					//$indTax=$adjustment->getAmount()->getNumber();
					//$ItemTaxRate=$this->getProtectedValue($adjustment['data']['tax'], "settings");

					if($cc_data['fef_payment_options']=="Recurring")
					{
						
						
						if($cc_data['fef_recuring_type_option']=="Installment")
						{
							
							//installment
							if($cc_data['fef_cnp_noof_payments']=="Indefinite Recurring Only")
							{
								$no_of_payments=$cc_data['fef_cnp_noof_payments'];
								$Unit_Tax = $this->number_formatprc(($indTax/999),2,'.','')*100;
								$UnitTaxCalculate += ($this->number_formatprc(($indTax/999),2,'.','')*$product->qty->value);
								//$UnitTaxCalculate += ($this->number_formatprc(($indTax/999),2,'.',''));
							}
							else
							{
								//for selected no.of payments
								$no_of_payments=$cc_data['fef_cnp_noof_payments'];
								$Unit_Tax = $this->number_formatprc(($indTax/$no_of_payments),2,'.','')*100;
								$UnitTaxCalculate += ($this->number_formatprc(($indTax/$no_of_payments),2,'.','')*$product->qty->value);
								//$UnitTaxCalculate += ($this->number_formatprc(($indTax/$no_of_payments),2,'.',''));
								
							}
							if($unitD==1)
							{
								$itemtax = $dom->createElement('UnitTax',$Unit_Tax);
								$itemtax = $orderitem->appendChild($itemtax);
							}
							
							
						}
						else
						{
							
									
							//if subscription
							/*if($unitD===1)
							{

								$itemtax = $dom->createElement('UnitTax',$this->number_formatprc($indTax,2,'.','')*100);
								$itemtax = $orderitem->appendChild($itemtax);
								if($totalProducts==1)
								{
									
									$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.',''));
								}
								else
								{
									
									$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.','')*$product->qty->value);
								}
							}*/
							//if subscription
							if($unitD===1)
							{

								$itemtax = $dom->createElement('UnitTax',$this->number_formatprc($indTax,2,'.','')*100);
								$itemtax = $orderitem->appendChild($itemtax);
							}
							$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.','')*$product->qty->value);
							
							//$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.',''));
						}
					}
					else
					{
						//echo "if one time only:".$indTax."<br>";
						if($unitD==1)
						{
							//echo $indTax."<br>";
							$itemtax = $dom->createElement('UnitTax',$this->number_formatprc($indTax,2,'.','')*100);
							$itemtax = $orderitem->appendChild($itemtax);
						}
						$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.','')*$product->qty->value);
						
						//echo "if one time only:".$indTax."<br>";
						
						/*if($unitD==1)
						{
							
							if($totalProducts==1)
							{
							
								//echo $product->qty->value;
								$itemtax = $dom->createElement('UnitTax',$this->number_formatprc($indTax/$product->qty->value,2,'.','')*100);
								$itemtax = $orderitem->appendChild($itemtax);
								$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.',''));
							}
							else
							{
									
								//echo $indTax;
								$itemtax = $dom->createElement('UnitTax',$this->number_formatprc($indTax,2,'.','')*100);
								$itemtax = $orderitem->appendChild($itemtax);
								$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.','')*$product->qty->value);
							}
						}
						else
						{
							//echo "Here";
							//echo $indTax;
							$itemtax = $dom->createElement('UnitTax',$this->number_formatprc($indTax,2,'.','')*100);
							$itemtax = $orderitem->appendChild($itemtax);
							$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.','')*$product->qty->value);
						}*/
						
						//$UnitTaxCalculate += ($this->number_formatprc($indTax,2,'.',''));
						
					}
					$unitD++;
                }
				
				//calculate discount coupons code
				
				
            }

        }
        else
        {
            //if no adjustments
            $totalTax=0;
            $totalDiscount=0;
			$no_of_ietms=$order->getProductCount();
            $grandTotalPrice += $this->number_formatprc($order->getTotal(),2,'.','');
        }
        
        //$pi++;
		$sku_code=$dom->createElement('SKU',$this->safeString($product->model->value, 100));
		$sku_code=$orderitem->appendChild($sku_code);
          // echo $UnitTaxCalculate."<br>"; 
		  $pL++;
	}//foreach loop end
	//echo "LastTax".$lastTax;
	//calculate cart level discounts,taxes,and shipping
	//exit();
	$adjs=$order->getLineItems();
	
	
	
	
	$totalItems=1;
	if(count($adjs)>0)
	{
		foreach($adjs as $adj)
		{
			
			
			if($adj['type']=="shipping")
			{
				$shipPrice=$adj['amount'];
				
				if($cc_data['fef_payment_options']=="Recurring")
				{
					if($cc_data['fef_recuring_type_option']=="Installment")
					{
						//echo "Ins";
						//installment
						if($cc_data['fef_cnp_noof_payments']=="Indefinite Recurring Only")
						{
							$no_of_payments=$cc_data['fef_cnp_noof_payments'];
							$ShippingValueCalculate += ($this->number_formatprc(($adj['amount']/999),2,'.','')*$totalItems);
						}
						else
						{
							//for selected no.of payments
							$no_of_payments=$cc_data['fef_cnp_noof_payments'];
							$ShippingValueCalculate += ($this->number_formatprc(($adj['amount']/$no_of_payments),2,'.','')*$totalItems);
						}
					}
					else
					{
						//if subscription
						//echo "Sub";
						$ShippingValueCalculate += ($this->number_formatprc($adj['amount'],2,'.','')*$totalItems);
					}
				}
				else
				{
					//if one time only
					$ShippingValueCalculate += ($this->number_formatprc($adj['amount'],2,'.','')*$totalItems);
				}
			}
		}
	}
	
	
	if($order->isShippable())
	{
		//$shipment = $this->getShipment();
        $shipping=$dom->createElement('Shipping','');
        $shipping=$cporder->appendChild($shipping);
		
		if(count($adjs)>0)
		{
			foreach($adjs as $adj)
			{
				if($adj['type']=="shipping")
				{
					$shipTitle=$adj['title'];
				}
			}
		}
		
        $shipping_method=$dom->createElement('ShippingMethod',$this->safeString($shipTitle,50));
        $shipping_method=$shipping->appendChild($shipping_method);

        $shipping_value = $dom->createElement('ShippingValue', $ShippingValueCalculate*100);
        $shipping_value=$shipping->appendChild($shipping_value);
		if(count($lit)>0)
		{
			foreach($lit as $l)
			{
				if($l=="shipping")
				{
					if($order->isShippable())
					{
						$shippingTax=($shipPrice*$taxObj['rate'])/100;
						//$shipping_tax=$dom->createElement('ShippingTax',$shippingTax*100);
						$shipping_tax=$dom->createElement('ShippingTax',0);
						$shipping_tax=$shipping->appendChild($shipping_tax);
					}
				}
			}
		}else
		{
			$shippingTax=0;
		}
		
		
	}
	//calculate tax of tax
	if(count($lit)>0)
	{
		foreach($lit as $l)
		{
			if($l=="tax")
			{
				
				$actualTax=(($order->getSubtotal()+$shipPrice)*$taxObj['rate'])/100;
				$taxOfTax=($actualTax*$taxObj['rate'])/100;
				//echo $taxOfTax;
			}
			else
			{
				$taxOfTax=0;
			}
		}
	}
	//exit();
	//receipt headers
	
	$receipt = $dom->createElement('Receipt', '');
    $receipt = $cporder->appendChild($receipt);
	
    if($config->get('cnpuber.cnp_receipt_patron')[1]==1)
	{
		$SendReceipt="true";
	}
	else
	{
		$SendReceipt="false";
	}
    $sendreceipt=$dom->createElement("SendReceipt",$SendReceipt);
    $sendreceipt=$receipt->appendChild($sendreceipt);
    
    $language=$dom->createElement("Language","ENG");
    $language=$receipt->appendChild($language);
    
	//collect receipt t&c and header information
	
	if($config->get('cnpuber.cnp_receipt_header')!="")
	{
		$receiptHeader=$config->get('cnpuber.cnp_receipt_header');
	}
	else
	{
		$receiptHeader="";
	}
	
    /*$orginfo=$dom->createElement("OrganizationInformation",$receiptHeader);
    $orginfo=$receipt->appendChild($orginfo);*/
    
	$orginfo=$dom->createElement("OrganizationInformation",'');
	$orginfo=$receipt->appendChild($orginfo);
	$orginfo->appendChild($dom->createCDATASection($receiptHeader));
	
	if($config->get('cnpuber.cnp_terms_con')!="")
	{
		$receipttnc=$config->get('cnpuber.cnp_terms_con');
	}
	else
	{
		$receipttnc="";
	}
	
    /*$termscon=$dom->createElement("TermsCondition", $receipttnc);
    $termscon=$receipt->appendChild($termscon);
    */
	
	$termscon=$dom->createElement("TermsCondition",'');
	$termscon=$receipt->appendChild($termscon);
	$termscon->appendChild($dom->createCDATASection($receipttnc));
	
    $emailnotilist=$dom->createElement("EmailNotificationList","");
    $emailnotilist=$receipt->appendChild($emailnotilist);
   
    $notificationemail=$dom->createElement("NotificationEmail","");
    $notificationemail=$emailnotilist->appendChild($notificationemail);
    
    $transaction = $dom->createElement('Transaction', '');
    $transaction = $cporder->appendChild($transaction);
	
	$cc_data['fef_payment_methods']="CreditCard";
	if($cc_data['fef_payment_methods'] == 'CreditCard' ) {			
            if($order->getTotal()==0) {
                $trans_type=$dom->createElement('TransactionType','PreAuthorization');
                $trans_type=$transaction->appendChild($trans_type);
            } else {
                $trans_type=$dom->createElement('TransactionType','Payment');
                $trans_type=$transaction->appendChild($trans_type);
            }
	} else {
		if($order->getTotal()==0)
		{
			$trans_type=$dom->createElement('TransactionType','PreAuthorization');
			$trans_type=$transaction->appendChild($trans_type);
		}
		else
		{
			$trans_type=$dom->createElement('TransactionType','Payment');
			$trans_type=$transaction->appendChild($trans_type);
		}
	}
	$trans_desc = $dom->createElement('DynamicDescriptor', 'DynamicDescriptor');
    $trans_desc = $transaction->appendChild($trans_desc);
	
	if($cc_data['fef_payment_options']=="Recurring")
	{
		$trans_recurr=$dom->createElement('Recurring','');
		$trans_recurr=$transaction->appendChild($trans_recurr);
		
		if($cc_data['fef_recuring_type_option']=="Installment")
		{
			//installment
			if($cc_data['fef_cnp_noof_payments']=="Indefinite Recurring Only")
			{
				$no_of_payments=$cc_data['fef_cnp_noof_payments'];
				$total_installment=$dom->createElement('Installment',998);
				$total_installment=$trans_recurr->appendChild($total_installment);
			}
			else
			{
				//for selected no.of payments
				$no_of_payments=$cc_data['fef_cnp_noof_payments'];
				if($no_of_payments!="")
				{
					$total_installment=$dom->createElement('Installment',$no_of_payments);
					$total_installment=$trans_recurr->appendChild($total_installment);
				}
				else
				{
					$total_installment=$dom->createElement('Installment',1);
					$total_installment=$trans_recurr->appendChild($total_installment);
				}
			}
			$total_periodicity=$dom->createElement('Periodicity',$cc_data['fef_cnp_periodcity']);
			$total_periodicity=$trans_recurr->appendChild($total_periodicity);
			
			$RecurringMethod=$dom->createElement('RecurringMethod',$cc_data['fef_recuring_type_option']);
			$RecurringMethod=$trans_recurr->appendChild($RecurringMethod);
		}
		else
		{
			if($cc_data['fef_cnp_noof_payments']=="Indefinite Recurring Only")
			{
				$no_of_payments=$cc_data['fef_cnp_noof_payments'];
				$total_installment=$dom->createElement('Installment',999);
				$total_installment=$trans_recurr->appendChild($total_installment);
			}
			else
			{
				//for selected no.of payments
				$no_of_payments=$cc_data['fef_cnp_noof_payments'];
				if($no_of_payments!="")
				{
					$total_installment=$dom->createElement('Installment',$no_of_payments);
					$total_installment=$trans_recurr->appendChild($total_installment);
				}
				else
				{
					$total_installment=$dom->createElement('Installment',1);
					$total_installment=$trans_recurr->appendChild($total_installment);
				}
			}
			$total_periodicity=$dom->createElement('Periodicity',$cc_data['fef_cnp_periodcity']);
			$total_periodicity=$trans_recurr->appendChild($total_periodicity);
			
			$RecurringMethod=$dom->createElement('RecurringMethod',"Subscription");
			$RecurringMethod=$trans_recurr->appendChild($RecurringMethod);
		}
	
	}
	$trans_totals = $dom->createElement('CurrentTotals', '');
    $trans_totals = $transaction->appendChild($trans_totals);
	if($TotalDiscountCalculate)
    {
        $total_discount = $dom->createElement( 'TotalDiscount',  $TotalDiscountCalculate*100);
        $total_discount = $trans_totals->appendChild($total_discount);
    }
    if($UnitTaxCalculate)
    {
        $total_tax = $dom->createElement( 'TotalTax',  $UnitTaxCalculate*100);
        $total_tax = $trans_totals->appendChild($total_tax);
    }
	if($ShippingValueCalculate!="")
	{
		$TotalShipping=$dom->createElement('TotalShipping',$ShippingValueCalculate*100);
		$TotalShipping=$trans_totals->appendChild($TotalShipping);
	}
	
    //echo "sTax:".$shippingTax;
	//echo $gtAmount=$this->order->getTotalPrice()->getNumber();
	
	/*echo "UnitTax:".$UnitTaxCalculate."<br>";
	echo "TaxofTax:". $taxOfTax."<br>";
	echo "UnitPrice:".$UnitPriceCalculate."<br>";
	echo "Shipping:".$ShippingValueCalculate."<br>";
	echo "ShippingTax:".$shippingTax."<br>";
	exit();*/
	//echo "Shipping:". $this->number_formatprc($taxOfTax, 2, '.', '');
	$gtAmount = ( $this->number_formatprc($UnitPriceCalculate, 2, '.', '')+  $this->number_formatprc($UnitTaxCalculate, 2, '.', '') + $this->number_formatprc($ShippingValueCalculate, 2, '.', ''))  - ($TotalDiscountCalculate );
	//+$this->number_formatprc($shippingTax, 2, '.', '')
	if($cc_data['fef_payment_options']=="Recurring")
	{
		if($cc_data['fef_recuring_type_option']=="Installment")
		{
			//installment
			if($cc_data['fef_cnp_noof_payments']=="Indefinite Recurring Only")
			{
				$no_of_payments=$cc_data['fef_cnp_noof_payments'];
				$total_amount = $dom->createElement( 'Total', ($this->number_formatprc(($gtAmount),2,'.','')*100)  );
				$total_amount = $trans_totals->appendChild($total_amount);
			}
			else
			{
				//for selected no.of payments
				$no_of_payments=$cc_data['fef_cnp_noof_payments'];
				$total_amount = $dom->createElement( 'Total',  ($this->number_formatprc(($gtAmount),2,'.','')*100) );
				$total_amount = $trans_totals->appendChild($total_amount);
			}
		}
		else
		{
			//if subscription
			$total_amount = $dom->createElement( 'Total',  ($this->number_formatprc($gtAmount,2,'.','')*100) );
			$total_amount = $trans_totals->appendChild($total_amount);
		}
	}
	else
	{
		//if one time only
		$total_amount = $dom->createElement( 'Total',  ($this->number_formatprc($gtAmount,2,'.','')*100) );
		$total_amount = $trans_totals->appendChild($total_amount);
	}
	
		//print_r($order->getTotal());
        $strParam=$dom->saveXML();	
		//print_r($strParam);
		//exit();
		return $strParam;
	
        
    }
	public function safeString( $str,  $length=1, $start=0 )
	{
		$str = preg_replace('/\x03/', '', $str); //Remove new line characters
		return substr( htmlspecialchars( ( $str ) ), $start, $length );
	}
	public function number_formatprc($number, $decimals = 2,$decsep = '', $ths_sep = '') {
		$parts = explode('.', $number);
		if(count($parts) > 1) {	return $parts[0].'.'.substr($parts[1],0,$decimals);	} else {return $number;	}
	}
	function getProtectedValue($obj,$name) {
        $array = (array)$obj;
        $prefix = chr(0).'*'.chr(0);
        return $array[$prefix.$name];
    }
	public function getTotalTax($adjustments,$product,$shipValue,$order)
	{
		
		$taxableAmt=0;
		$totalTax=0;
		foreach($adjustments as $adjustment)
		{
			
			if($adjustment['type']=="tax")
			{
				$taxInfo=$adjustment['data']['tax'];
				$totalTax += $adjustment['amount'];
				//$totalTax += $taxInfo->getPlugin()->calculateOrderTax($order, $taxInfo);
				//$totalTax =$taxInfo->getPlugin()->calculateOrderTax($order, $taxInfo);
				//echo "Tax:".$adjustment['amount']."<br>";
				/*$taxObj=$this->getProtectedValue($taxInfo,"settings");
				$lineItemTypes=$this->getProtectedValue($taxInfo,"line_item_types");
				$taxRate=$taxObj['rate'];
				echo $taxRate;
				if(count($lineItemTypes)>0)
				{
					if(array_search("generic",$lineItemTypes))
					{
						$taxableAmt=($product->price->value*$taxRate)/100;
					}
					if(array_search("tax",$lineItemTypes))
					{
						$taxOfTax=($taxableAmt*$taxRate)/100;
					}
					if(array_search("shipping",$lineItemTypes))
					{
						$shippingTax=($shipValue*$taxRate)/100;
					}
					$final_unitTax += $taxableAmt+$taxOfTax+$shippingTax;
				}
				else
				{
					return $final_unitTax=0;
				}*/
			}
			
		}
		
		return $totalTax;
		
		//echo $final_unitTax;
		
	}
}
