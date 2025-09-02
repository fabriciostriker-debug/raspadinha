<?php
// Session configuration file
// Set session cookie lifetime to 7 days (604800 seconds)
ini_set('session.gc_maxlifetime', 604800); // server-side session lifetime
session_set_cookie_params(604800); // client-side cookie lifetime
?>
