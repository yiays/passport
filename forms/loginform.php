<?php
require_once('models/forms.php');

$loginform = new Form('Access your passport', '/account/login', 'POST');
$loginform->fields []= new FormField('text', 'username', 'username', '', true, false, null, true, 3, 64);
$loginform->fields []= new FormField('password', 'password', 'password', '', true, false, null, false, 6, 128);
$loginform->buttons []= new FormButton('button', 'Cancel', 'data-cancel');
$loginform->buttons []= new FormButton('submit', 'Login');
?>