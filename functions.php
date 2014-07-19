<?php 

function post_value($key, $default = null) {

    $value = $default;

    if (array_key_exists($key, $_POST))
        $value = $_POST[$key];

    return $value;

}

function get_value($key, $default = null) {

    $value = $default;

    if (array_key_exists($key, $_GET))
        $value = $_GET[$key];

    return $value;

}

function session_value($key, $default = null) {

    $value = $default;

    if (array_key_exists($key, $_SESSION))
        $value = $_SESSION[$key];

    return $value;

}


function request_value($key, $default = null) {

    $value = $default;

    if (array_key_exists($key, $_REQUEST))
        $value = $_REQUEST[$key];

    return $value;

}

function html_debug() {
    
    $argc = func_num_args();

    if ($argc > 0) {

        echo '<pre>';
        
        for ($index = 0; $index < $argc; $index++) { 
        
            $value = func_get_arg($index);

            printf("argument %d: ", $index);
            if (is_null($value)) { 
                print("::NULL::");
            } elseif (empty($value)) { 
                print("::EMPTY::");
            } else {
                var_dump($value);
            }
            print('<br /><br />');
        }

        echo '</pre>';
        exit();
    }
}

function redirect($relative_page) {

    header(sprintf('Location: %s/%s', BASE_URL, $relative_page)); 
    exit();

}


