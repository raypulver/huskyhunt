<?php require_once '../hh-config.php'; ?>
<?php 

    $module_id = session_value('MODULE_ID');
    #html_debug($module_id, $USER);
     
    if (!is_numeric($module_id) || is_null($USER)) {
        // TODO REDIRECT;
        exit();
    }

    $module = new HuskyHuntModule($module_id);
   
    $status = $module->insight; 

    if (strlen($status) > 140) {
        // TODO redirect
        exit();
    }

    require_once 'http.php';
    require_once 'oauth_client.php';
    
    require_once 'twitter.config.php';

    $client = new oauth_client_class;
    #$client->debug = 1;
    #$client->debug_http = 1; 

    $client->server = 'Twitter'; 

    $client->redirect_uri = BASE_URL . '/share/twitter.php';

    $client->client_id = OAUTH_CLIENT_ID;
    $client->client_secret = OAUTH_CLIENT_SECRET;
   
    if (($success = $client->Initialize())) {

        if (($success = $client->Process())) {
        
            if (strlen($client->access_token)) {
  
                /*
                $success = $client->CallAPI(
					'https://api.twitter.com/1.1/account/verify_credentials.json', 
		    		'GET', array(), array('FailOnAccessError'=>true), $user);
                */

                $args = Array(
                    'status'        => $status, 
                    'wrap_links'    => 'true'
                );

                $success = $client->CallAPI(
                    'https://api.twitter.com/1.1/statuses/update.json',
                    'POST', $args, array('FailOnAccessError'=>true), $update);
                
                if ($success) {
                    $USER->share_success($module_id, HH_SOCIAL_TWITTER, $update);
                }
        
            }
        }

        $success = $client->Finalize($success);
    }

    if ($client->exit) 
        exit;

    if ($success) {
        $client->ResetAccessToken();
        redirect('share/success.php');   
    } else {
        redirect('share/failure.php');   
    }

    


