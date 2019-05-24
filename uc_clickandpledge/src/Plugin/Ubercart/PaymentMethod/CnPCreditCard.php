<?php
namespace Drupal\uc_clickandpledge\Plugin\Ubercart\PaymentMethod;
use Drupal\Core\Form\FormStateInterface;
use Drupal\uc_clickandpledge\CnPCreditCardPaymentMethodBase;
use Drupal\uc_order\OrderInterface;

//use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * @UbercartPaymentMethod(
 *   id = "clickandpledge_payment_gateway",
 *   name = @Translation("Click & Pledge Credit Card"),
 * )
 */
class CnPCreditCard extends CnPCreditCardPaymentMethodBase
{
    
    
   /* public function __construct() {
        $connection= \Drupal::database();
		$prefix=$connection->tablePrefix();
        $table_name = $prefix.'dp_cnp_uber_jbcnpaccountsinfo';
		$sql = "SELECT * FROM " .$table_name;
        $query = $connection->query($sql);
		$query->allowRowCount = TRUE;
		if ($query->rowCount()==-1) {
                    $rpath=\Drupal::request()->getSchemeAndHttpHost().\Drupal::request()->getBaseUrl();
                    $this->my_goto($rpath."/admin/cnpauth");
		}
		
	
    }*/
    /*public function my_goto($path) { 
     $response = new RedirectResponse($path, 302);
     $response->send();
     return;
    }*/
     /**
   * {@inheritdoc}
   */
  
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'debug' => FALSE,
     
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug'),
      '#description' => $this->t('Log debug payment information to dblog when card is "charged" by this gateway.'),
      '#default_value' => $this->configuration['debug'],
    ];
    
    
   
    

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    
    $this->configuration['debug'] = $form_state->getValue('debug');
    
    return parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function chargeCard(OrderInterface $order, $amount, $txn_type, $reference = NULL) {
    $user = \Drupal::currentUser();

    // cc_exp_month and cc_exp_year are also validated by
    // CreditCardPaymentMethodBase::validateExpirationDate().
    $month = $order->payment_details['cc_exp_month'];
    $year = $order->payment_details['cc_exp_year'];
    if ($year < 100) {
      $year = $year + 2000;
    }

    // Card is expired at 0:00 on the first day of the next month.
    $expiration_date = mktime(0, 0, 0, $month + 1, 1, $year);

    // Conditions for failure are described in file documentation block above.
    // All other transactions will succeed.
    if ($order->payment_details['cc_number'] == '0000000000000000' ||
      (isset($order->payment_details['cc_cvv']) && $order->payment_details['cc_cvv'] == '000') ||
      ($expiration_date - REQUEST_TIME) <= 0     ||
      $amount == 12.34                           ||
      $order->billing_first_name == 'Fictitious' ||
      $order->billing_phone == '8675309'            ) {
      $success = FALSE;
    }
    else {
      $success = TRUE;
    }

    // The information for the payment is in the $order->payment_details array.
    if ($this->configuration['debug']) {
      \Drupal::logger('uc_credit')->notice('Test gateway payment details @details.', ['@details' => print_r($order->payment_details, TRUE)]);
    }

    if ($success) {
      $message = $this->t('Credit card charged: @amount', ['@amount' => uc_currency_format($amount)]);
      uc_order_comment_save($order->id(), $user->id(), $message, 'admin');
    }
    else {
      $message = $this->t('Credit card charge failed.');
      uc_order_comment_save($order->id(), $user->id(), $message, 'admin');
    }

    $result = [
      'success' => $success,
      'comment' => $this->t('Card charged, resolution code: 0022548315'),
      'message' => $success ? $this->t('Credit card payment processed successfully.') : $this->t('Credit card charge failed.'),
      'uid' => $user->id(),
    ];

    return $result;
  }
  
}
?>