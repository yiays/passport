<?php
require_once('../passport.conn.php');
require_once('api/models/forms.php');
require_once('api/auth.php');

//Create form
$registerform = new Form('Create your passport', '/account/register', 'POST');

//Define form fields
$registerform->fields []= new FormField('text', 'username', 'new-username', '', true, false, null, true, 3, 64);
$registerform->fields []= new FormField('email', 'email', 'new-email', '', true, false, null, false, null, 255);
$registerform->fields []= new FormField('password', 'password', 'new-password', '', true, false, null, false, 6, 72);

//Define form buttons
$registerform->buttons []= new FormButton('button', 'Cancel', 'data-cancel');
$registerform->buttons []= new FormButton('submit', 'Register');

//Define form rules
$registerform->rules []= function($data) {
    //Ensure that the username is unique before registration
    global $passportconn;

    $username = $passportconn->escape_string($data['username']);
    $result = $passportconn->query("SELECT Username FROM user WHERE Username = \"$username\"");
    if(!$result){
        return new FormValidationResult(false, 'Failed to check if username is unique.');
    }
    if($result->num_rows>0){
        return new FormValidationResult(false, 'This username is taken.');
    }
    return new FormValidationResult(true);
};

$registerform->rules []= function($data){
    //Ensure that the email is unique before registration
    global $passportconn;

    $email = $passportconn->escape_string($data['email']);
    $result = $passportconn->query("SELECT Email FROM user WHERE Email = \"$email\"");
    if(!$result){
        return new FormValidationResult(false, 'Failed to check if email is unique.');
    }
    if($result->num_rows>0){
        return new FormValidationResult(false, 'This email address is already associated with an account.');
    }
    return new FormValidationResult(true);
};

$registerform->submit = function($data){
    global $passportconn;
    
    $username = $passportconn->escape_string($data['username']);
    $password = $passportconn->escape_string(password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => 9]));
    $email = $passportconn->escape_string($data['email']);
    $result = $passportconn->query("INSERT INTO user(Username,Password,Email) VALUES(\"$username\",\"$password\",\"$email\")");
    if(!$result){
        return "Failed to register new account! $passportconn->error";
    }
    
    $result = $passportconn->query("SELECT LAST_INSERT_ID()");
    if(!$result){
        return "Failed to get new account data! $passportconn->error";
    }
    
    $row = $result->fetch_row();
    $session = passport\create_token($row[0], "Unnamed device used for registration.");
    return $session->fetch();
};
?>