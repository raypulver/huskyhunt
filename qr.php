<?php require_once './hh-config.php'; ?>
<?
    $code = get_value('code');

    $result = false;

    if (!is_null($USER)) {

        $module = $USER->next_module();
        if (!is_null($module)) {
            $USER->initialize_grade($module);

            $_SESSION['MODULE_ID'] = $module->get_id(); 

            if (count($module->questions) == 1) {

                reset($module->questions);
                $question = current($module->questions);

                $result = $question->grade_answers(Array($code));        
               
                if ($result == true) {
                    $results = Array();
                    $results[$question->get_id()] = $result;
                
                    $USER->quiz_attempt($module, $results);

                }
            } 
        }
    }

    if ($result == true) {
        redirect('share.php');
    } else {
        redirect('invalid_qr.php');
        # todo redirect to invalid qrcode page/
    }


