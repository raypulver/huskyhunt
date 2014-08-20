<?php require_once '../hh-config.php'; ?>
<?php 

/*    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
error_reporting(-1); */
    $module_id = $_GET['id'];
    $netid = $_GET['netid'];
    $USER = new HuskyHuntUser($netid);
 // end new code
    if (!is_numeric($module_id) || is_null($USER)) {
        // TODO REDIRECT;
        exit();
    }

    $module = new HuskyHuntModule($module_id);
   
    $message = $module->insight; 

    require_once 'http.php';
    require_once 'oauth_client.php';
    
    require_once 'facebook.config.php';

    $client = new oauth_client_class;
    #$client->debug = 1;
    #$client->debug_http = 1; 

    $client->server = 'Facebook'; 

    $client->redirect_uri = BASE_URL . '/share/facebook.php?id=' . $module_id . '&netid=' . $netid;

    $client->client_id = OAUTH_CLIENT_ID;
    $client->client_secret = OAUTH_CLIENT_SECRET;
   
    $client->scope = 'publish_actions';

    if (($success = $client->Initialize())) {

        if (($success = $client->Process())) {
        
            if (strlen($client->access_token)) {

                $data = Array(
                    'message' => $message
                );

                $success = $client->CallAPI(
                    'https://graph.facebook.com/me/feed',
                    'POST', $data, array('FailOnAccessError'=>true), $update);
                

                if ($success)
                    $USER->share_success($module_id, HH_SOCIAL_FACEBOOK, $update);
                    header('Location: /#/game/shared');

            }
        }

        $success = $client->Finalize($success);
    }

    if ($client->exit) 
        exit;

    if ($success) {
        $client->ResetAccessToken();
//        echo '<script>window.location = "/#/game/shared"</script>';
    } else {
      echo 'failure because: ' . $client->error;
    }
 
