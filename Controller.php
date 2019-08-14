<?php 

class Controller {

    private $session;

    private $apiContext;

    const BASE_URL = 'http://localhost/lessons/paypal/subscription/';

    public function __construct(&$session) 
    {
        $this->session = &$session;
    }

    public function __call($funcName, $args)
    {
        // This will include all the files and classes to your autoloader
        require __DIR__ . '/vendor/autoload.php';

        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AUA0vvs7vganK5I5g3EAJAgtpxxlt4WzlOK9MuMHDavYnWux5XkocZ2FaOfbzNBODM5kDf2LbeknZ5It',     // ClientID
                'EM-6C_MAmtbve4UrBMNib7tRfQEl-0spuGYubHhSUQK9fLWqa6R6p1Y1ongtE7VnXj29Mu8gFLfVARD3'      // ClientSecret
            )
        );

        $methodName = $funcName . '_Paypal';

        $this->$methodName();
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


    public function createPlan_Paypal()
    {
        
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
            // ->setCycles("12") - we use this if setType('FIXED')


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
            $createdPlan = $plan->create($this->apiContext);
        } catch (Exception $ex) {
            print_r($ex->getMessage());
            die();
        }

        // echo "<pre>";
        // print_r($createdPlan);
        // echo "</pre>";

        $this->activatePlan($createdPlan);

    }

    
    private function activatePlan($createdPlan)
    {
        try {

            $patch = new PayPal\Api\Patch();
            $value = new PayPal\Common\PayPalModel('{
                "state":"ACTIVE"
            }');

            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);

            $patchRequest = new PayPal\Api\PatchRequest();
            $patchRequest->addPatch($patch);

            $createdPlan->update($patchRequest, $this->apiContext);

        } catch (Exception $ex) {
            print_r($ex->getMessage());
            die();
        }
    }


    public function showPlans_Paypal()
    {
        // status could be CREATED, ACTIVE, INACTIVE or ALL
        $params = array('page_size' => '20', 'status'=>'ALL');
        $planList = PayPal\Api\Plan::all($params, $this->apiContext);

        echo "<pre>";
        print_r($planList->toArray());
        echo "<pre>";
    }

    // delete Paypal plan with an argument of the plan ID:
    public function deletePlan_Paypal($planId='P-5SD21591397201814RMZDHXY')
    {
        $plan = PayPal\Api\Plan::get($planId, $this->apiContext);
        $plan->delete($this->apiContext);
    }


    // subscribe to one of the plans
    public function subscribe_Paypal()
    {
        $agreement = new PayPal\Api\Agreement();
        $agreement->setName('Some subscription name')
            ->setDescription('Initial payment of $15 followed by a recurring payment of $15 on the '.date('jS').' of every mon') // we can start date to 1 month from now if we take the first payment via the setup fee
            ->setStartDate(gmdate("Y-m-d\TH:i:s\Z", strtotime("+1 month", time())));

        $plan = new PayPal\Api\Plan();
        $plan->setId('P-61D888833N314604JR5WQ47Q');

        $agreement->setPlan($plan);

        $payer = new PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {

            $agreement = $agreement->create($this->apiContext);
            $approvalUrl = $agreement->getApprovalLink();

        } catch (Exception $ex) {
            print_r($ex->getMessage());
            die();
        }

        header("Location: {$approvalUrl}");
    }

    // final step of agreement - if successful
    public function agreement_Paypal()
    {
        if (!empty($_GET['success'])) {
            $success = $_GET['success'];

            if ($success && !empty($_GET['token'])) {
                $token = $_GET['token'];

                $agreement = new \PayPal\Api\Agreement();

                try {
                    $agreement->execute($token, $this->apiContext);
                } catch (Exception $ex) {
                    exit(1);
                }

                $this->updateStatus(true);

            } else {
                // payment failed, perhaps send the user elsewhere and log the error
            }
        }
    }

    private function updateStatus($value) 
    {
        $array = [
            'subscription' => $value
        ];

        file_put_contents('database/db.txt', serialize($array));
    }
    

}