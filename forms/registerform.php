<?php
require_once('models/forms.php');

$registerform = new Form('Create your passport', '/account/register', 'POST');
$registerform->fields []= new FormField('text', 'username', 'new-username', '', true, false, null, true, 3, 64);
$registerform->fields []= new FormField('email', 'email', 'new-email', '', true, false, null, false, null, 255);
$registerform->fields []= new FormField('password', 'password', 'new-password', '', true, false, null, false, 6, 128);
$registerform->buttons []= new FormButton('button', 'Cancel', 'data-cancel');
$registerform->buttons []= new FormButton('submit', 'Register');
?>