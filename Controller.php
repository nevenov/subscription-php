<?php 

class Controller {

    private $session;

    const BASE_URL = 'http://localhost/lessons/paypal/subscription/';

    public function __construct(&$session) 
    {
        $this->session = &$session;
    }

    public function pricing()
    {
        require_once('layout/views/pricing.php');
    }

    public function showVideos()
    {
        if ($this->canWatchVideos()) {
            $canWatchVideos = true;
        } else {
            $canWatchVideos = false;
        }
        require_once('layout/views/videos.php');
    }

    public function login()
    {
        $login = new FakeLogin($this->session);
        $login->login();
        return header("Location: " . self::BASE_URL);
    }

    public function logout()
    {
        $logout = new FakeLogin($this->session);
        $logout->logout();
        return header("Location: " . self::BASE_URL);
    }

    private function canWatchVideos() 
    {
        if($this->isLoggedIn() && $this->isSubscriptionActive()) {
            return true;
        } else {
            return false;
        }
    }

    private function isLoggedIn() 
    {
        if (isset($this->session['logged_in']) && $this->session['logged_in']==true) {
            return true;
        } else {
            return false;
        }
    }

    private function isSubscriptionActive() 
    {
        $array = unserialize(file_get_contents('database/db.txt'));
        return $array['subscription'];
    }


    public function createPlan()
    {
        // This will include all the files and classes to your autoloader
        require __DIR__ . '/vendor/autoload.php';

        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AUA0vvs7vganK5I5g3EAJAgtpxxlt4WzlOK9MuMHDavYnWux5XkocZ2FaOfbzNBODM5kDf2LbeknZ5It',     // ClientID
                'EM-6C_MAmtbve4UrBMNib7tRfQEl-0spuGYubHhSUQK9fLWqa6R6p1Y1ongtE7VnXj29Mu8gFLfVARD3'      // ClientSecret
            )
        );

        $plan = new \PayPal\Api\Plan();

        $plan->setName('Pro Plan')
            ->setDescription('Unlimited access. HD video available')
            ->setType('INFINITE'); // or FIXED. The plan has a fixed number of payment cycles.
        
        $paymentDefinition = new \PayPal\Api\PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR') // or TRIAL (visitor will be able to test and then buy plan)
            ->setFrequency('Month') // or WEEK, DAY, YEAR, MONTH
            ->setFrequencyInterval("1") // The interval at which the customer will be charged. Value can not be greater than 12 months
            ->setAmount(new \PayPal\Api\Currency(array('value' => 15, 'currency' => 'USD')));


        $merchantPreferences = new \PayPal\Api\MerchantPreferences();           
        $merchantPreferences
            // The URL where the customer can approve the agreement  
            ->setReturnUrl(self::BASE_URL."?action=agreement&success=true") 
            // The URL where the customer can cancel the agreement  
            ->setCancelUrl(self::BASE_URL."?action=agreement&success=false")
            // Allowed values: YES, NO. Default is NO. 
            // Indicates whether Paypal automatically bills the outstanding balance in the next billing cycle
            ->setAutoBillAmount("yes")  
            // Action to take if a failure occurs during initial payment. Allowed values:
            // CONTINUE, CANCEL. Default is continue.
            ->setInitialFailAmountAction("CONTINUE")
            // Total number of failed attempts allowed. 
            // Default is 0, representing an infinite number of failed attempts.
            ->setMaxFailAttempts("0")
            // The currency and amount of the set-up fee for the agreement. 
            // This fee is the initial, non-recurring payment amount that is due immediatelly 
            // when the billing agreement is created. 
            ->setSetupFee(new \PayPal\Api\Currency(array('value' => 15, 'currency' => 'USD')));
            
        

        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);

        try {
            $createdPlan = $plan->create($apiContext);
        } catch (Exception $ex) {
            print_r($ex->getMessage());
            die();
        }

        echo "<pre>";
        print_r($createdPlan);
        echo "</pre>";

    }
    

}