<?php

$labels = array();
$labels['password_revive']   = 'Password Revive';
$labels['email_label']       = 'Email to:';
$labels['select_petname']    = 'Favorite Pet Name';
$labels['select_movie']      = 'Favorite Movie';
$labels['select_highschool'] = 'Last High School';
$labels['login_email_label'] = 'Username';
$labels['login_button']      = 'Go!';
$labels['login_title']       = 'Retrieve Lost Password';
$labels['login_footer']      = 'Note: Open a Support Ticket for further assistance.';
$labels['settings_button']   = 'Save';
$labels['settings_title']    = 'When you Forget Your Password, a Random Password will be Generated and Emailed to the one you set here.';
$labels['settings_note']     = 'If Checked, will disable/turn off this entire Password Lost and Found ability.';
$labels['settings_question_label'] = 'Security Question:';
$labels['settings_disabled_label'] = 'Disabled:';

// used as the header email
// From: "fromname" <admin@domain.tld>
$labels['email_from_header'] = 'From: [product_name] <admin.email@planetxmail.com>';

// email subject line
$labels['email_subject'] = '[product_name] - Notification';

// email message body
// [temporary_password] MUST exist in the message, or this plugin is pointless!
// [product_name] and [support_url] are optional, they are taken directly from roundcube config.
// These [-] markers will of course be replaced with real values.
$labels['email_message'] = 'Hello,

[temporary_password]

Please, Login as soon as possible and Update Your Password.  A
temporary one has been provided above.

If you received this without your Approval, please login immediately
and change or disable your Password Revive Settings until the problem
is resolved.  Notify your System Administrator of hacking attempts.
Although this is unlikely to happen, because your Security Question,
Security Answer and Email Login Account all must match.  If you feel
your Account is in danger, please contact support at:

[support_url]

Thank You,
[product_name]
';

// used in fancy jQuery popups
$messages = array();
$messages['noemail']             = 'Email cannot be empty.';
$messages['noemailsame']         = 'Email cannot be the same as this Email.';
$messages['noemailbad']          = 'Email not Valid Format.';
$messages['noanswer']            = 'Security Question cannot be empty.';
$messages['successfullysaved']   = 'Successfully stored.';
$messages['unsuccessfullysaved'] = 'ERROR: Information not stored.';
$messages['login_success_msg']   = 'A Temporary Password has been mailed to you.';
$messages['login_dbfail_msg']    = 'Database error has occured.';
$messages['login_fail_msg']      = 'Unable to find that Account or Security Question and Answer were incorrect.';

?>