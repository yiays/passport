<?php
require_once('../passport.conn.php');
require_once('models/forms.php');

//Create form
$loginform = new Form('Access your passport', '/account/login', 'POST');

//Define form fields
$loginform->fields []= new FormField('text', 'username', 'username', '', true, false, null, true, 3, 64);
$loginform->fields []= new FormField('password', 'password', 'password', '', true, false, null, false, 6, 72);

//Define form buttons
$loginform->buttons []= new FormButton('button', 'Cancel', 'data-cancel');
$loginform->buttons []= new FormButton('submit', 'Login');

//Define form rules
$loginform->rules []= function($data) {
    //Ensure that the username exists
    global $conn;

    $username = $conn->escape_string($data['username']);
    $result = $conn->query("SELECT username FROM user WHERE username = \"$username\"");
    if(!$result){
        return new FormValidationResult(false, 'Failed to verify login information.');
    }
    if($result->num_rows==0){
        return new FormValidationResult(false, 'Invalid username or password.');
    }
    return new FormValidationResult(true);
};

$loginform->rules []= function($data){
    //Ensure that the password matches
    global $conn;

    $username = $conn->escape_string($data['username']);
    $result = $conn->query("SELECT Id,Password FROM user WHERE username = \"$username\"");
    if(!$result){
        return new FormValidationResult(false, 'Failed to verify login information.');
    }
    if($result->num_rows==0){
        return new FormValidationResult(false, 'Invalid username or password.');
    }
    $row = $result->fetch_row();
    if(is_null($row[1])){
        return new FormValidationResult(false, 'Please login using another method and set a password.');
    }
    if(!password_verify($data['password'], $row[1])){
        return new FormValidationResult(false, 'Invalid username or password.');
    }
    $_SESSION['uid'] = $row[0];
    return new FormValidationResult(true);
};

$loginform->submit = function(){
    global $conn;
    $result = $conn->query("SELECT * FROM user WHERE Username = \"$_SESSION[uid]\"");
    if(!$result){
        return "Failed to login! $conn->error";
    }
    if($result->num_rows != 1){
        return "Unable to find account!";
    }
    $_SESSION['user'] = $result->fetch_assoc();
    return true;
};
?>