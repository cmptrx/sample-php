<?php

require_once('config.php');
require_once('vendor/autoload.php');

if (!session_id())
    session_start();

if (isset($_GET["code"]) && $_GET["code"] != "") {

    \Clef\Clef::initialize(APP_ID, APP_SECRET);
    try {
        $response = \Clef\Clef::get_login_information($_GET["code"]);
        $result = $response->info;

        // reset the user's session
        if (isset($result->id) && ($result->id != '')) {
            //remove all the variables in the session
            session_unset();
            // destroy the session
            session_destroy();
            if (!session_id())
                session_start();

            $clef_id = $result->id;

            $_SESSION['name']     = $result->first_name .' '. $result->last_name;
            $_SESSION['email']    = $result->email;
            $_SESSION['user_id']  = $clef_id;
            $_SESSION['logged_in_at'] = time();  // timestamp in unix time

            require_once('mysql.php');

            $user = get_user($clef_id, $mysql);
            if (!$user) {
                insert_user($clef_id, $result->first_name, $mysql);
            }

            // send them to the member's area!
            header("Location: members_area.php");
        }
    } catch (Exception $e) {
       echo "Login with Clef failed: " . $e->getMessage();
    }
}
?>

