<?php
require_once "db.php";

abstract class UserNameError {
    const Empty  = 0;
    const Invalid = 1;
    const Taken   = 2;
    const Ok      = 3;
}

function validateUsername($username) {
    $db = new Db('enotio');

    if (empty(trim($_POST["username"]))) {
        return UserNameError::Empty;
    } else if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        return UserNameError::Invalid;
    } else if ($db->checkUserExists(trim($username)))  {
        return UserNameError::Taken;
    } else {
        return UserNameError::Ok;
    }

    $db->close();
unset($db);
}
?>