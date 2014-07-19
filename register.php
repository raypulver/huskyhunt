<?php require_once './hh-config.php'; ?>
<?php
    
$contact = post_value('email_address');

if (!is_null($contact) && !is_null($USER)) {
    $USER->contact = $contact;
    $USER->save();

    redirect('index.php');
}


?>
<html>
    <head>
        <?php include BASE_PATH . '/templates/head.php'; ?>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        
        <div class="container">
                
                
    
            <div class="row">
            <div class="col-md-6 col-md-offset-3">
            <h3>Register Today!</h3> 
            <h4>We will notify you when weekly questions go live.</h4>
            <div class="well">
               
                <h2 class="text-center"> <?=NETID?> </h2> <br />
                <form role="form" method="POST" action="register.php">
                    <input type="hidden" name="netid" value="<?=NETID?>">

                    <div class="form-group">
                        <label for="email_address">Email Address</label>
                        <input type="email" class="form-control" id="email_address" name="email_address" placeholder="first.last@uconn.edu">
                    </div>
                    <!--
                    <div class="form-group">
                        <label for="email_address">Mobile Phone Number</label>
                        <h6>Normal Texting Rates Apply</h6>
                        <div class="input-group">
                            <input type="email" class="form-control" id="mobile_phone" name="mobile_phone" placeholder="860-555-1337">
                            <div class="input-group-btn">
                                <button id="mobile_provider" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Mobile Provider <span class="caret"></span></button>
                                <input type="hidden" name="mobile_provider" value="NULL" />
                                <ul class="dropdown-menu pull-right">
                                    <li><a href="#">AT&ampT</a></li>
                                    <li><a href="#">Verizon</a></li>
                                    <li><a href="#">T-Mobile</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    -->
                      <button type="submit" class="btn btn-default">Submit</button>
                </form>

            </div>
            </div>
            </div>
        </div>
    </body>
</html>

