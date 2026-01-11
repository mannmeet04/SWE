<?php
define('PASSWORD_SALT', 'HSG_Lernwebseite_2025_Sicheres_Salt_!@#$%^&*()');

function hashPassword($password) {
    return password_hash($password . PASSWORD_SALT, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    if (password_verify($password . PASSWORD_SALT, $hash)) {
        return true;
    }
    if ($hash === sha1(PASSWORD_SALT . $password)) {
        $newHash = hashPassword($password);
        return true;
    }
    return false;
}
function createTestPassword($password) {
    return hashPassword($password);
}
?>