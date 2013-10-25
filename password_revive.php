<?php
/**
 * Password Revive
 *
 * Adds ability to retrieve a lost password.
 *
 * @version 0.1
 * @author Matt Kukowski
 * @url http://roundcube.net/plugins/password_revive
 */
class password_revive extends rcube_plugin
{
  public $task = 'login|settings';

  function init()
  {
    $rcmail = rcmail::get_instance();

    if ($rcmail->task == 'login') {
      $this->add_hook('login_failed', array($this, 'password_revive_login_failed'));
    }
    else if ($rcmail->task == 'settings')
    {
      $this->register_action('plugin.password_revive', array($this, 'password_revive_init'));
      $this->register_action('plugin.password_revive-save', array($this, 'password_revive_save'));
      $this->add_texts('localization/', array('password_revive', 'noemail', 'noanswer'));
      $rcmail->output->add_label('password_revive');
      $this->include_script('password_revive.js');
    }
  }

  function password_revive_init()
  {
    $this->add_texts('localization/');
    $this->register_handler('plugin.body', array($this, 'password_revive_form'));
    $rcmail = rcmail::get_instance();
    $rcmail->output->set_pagetitle($this->gettext('password_revive'));
    $rcmail->output->send('plugin');
  }

  function password_revive_save()
  {
    $rcmail = rcmail::get_instance();
    $user   = $rcmail->user;

    $this->add_texts('localization/');
    $this->register_handler('plugin.body', array($this, 'password_revive_form'));
    $rcmail->output->set_pagetitle($this->gettext('password_revive'));

    $email    = trim(strtolower(get_input_value('email', RCUBE_INPUT_POST, true)));
    $question = trim(strtolower(get_input_value('securityquestion', RCUBE_INPUT_POST, true)));
    $answer   = trim(strtolower(get_input_value('securityanswer', RCUBE_INPUT_POST, true)));
    $disabled = trim(get_input_value('disabled', RCUBE_INPUT_POST, true));

    if ($email == '')
      $rcmail->output->command('display_message', $this->gettext('noemail'), 'error');
    else if (strtolower($_SESSION['username']) == $email)
      $rcmail->output->command('display_message', $this->gettext('noemailsame'), 'error');
    else if (! rcube_utils::check_email($email))
      $rcmail->output->command('display_message', $this->gettext('noemailbad'), 'error');
    else if ($answer == '')
      $rcmail->output->command('display_message', $this->gettext('noanswer'), 'error');
    else
    {
      $arr_prefs = $user->get_prefs();
      $arr_prefs['password_revive']['email']    = $email;
      $arr_prefs['password_revive']['question'] = $question;
      $arr_prefs['password_revive']['answer']   = $answer;
      $arr_prefs['password_revive']['disabled'] = ($disabled) ? '1' : '0';

      if ($user->save_prefs($arr_prefs))
        $rcmail->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');
      else
        $rcmail->output->command('display_message', $this->gettext('unsuccessfullysaved'), 'error');
    }

    rcmail_overwrite_action('plugin.password_revive');
    $rcmail->output->send('plugin');
  }

  function password_revive_form()
  {
    $rcmail = rcmail::get_instance();

    $user = $rcmail->user;
    $arr_prefs = $user->get_prefs();

    $email    = $arr_prefs['password_revive']['email'];
    $question = $arr_prefs['password_revive']['question'];
    $answer   = $arr_prefs['password_revive']['answer'];
    $disabled = $arr_prefs['password_revive']['disabled'];

    $summary = html::div(array('class' => 'box'), $this->gettext('settings_title'));
    $note    = html::div(array('class' => 'box'), $this->gettext('settings_note'));

    $table = new html_table(array('cols' => 2));

    $table->add('title', $this->gettext('email_label'));
    $field = new html_inputfield(array('name' => 'email', 'value' => "$email", 'required' => 'required', 'size' => 20, 'maxlength' => '100'));
    $table->add('input', $field->show());

    $select = new html_select(array('name' => 'securityquestion'));
    $select->add($this->gettext('select_petname'), 'petname');
    $select->add($this->gettext('select_movie'), 'movie');
    $select->add($this->gettext('select_highschool'), 'highschool');
    $table->add('title', $this->gettext('settings_question_label'));
    $field = new html_inputfield(array('name' => 'securityanswer', 'value' => "$answer", 'required' => 'required', 'size' => 20, 'maxlength' => '50'));
    $table->add('input', $select->show($question) . $field->show());

    $table->add('title', $this->gettext('settings_disabled_label'));
    $field = new html_checkbox(array('name' => 'disabled', 'value' => '1'));
    $table->add('input', $field->show($disabled) . $note);

    $out = html::div(array('class' => 'box'),
        html::div(array('id' => 'prefs-title', 'class' => 'boxtitle'), $this->gettext('password_revive')) .
        html::div(array('class' => 'boxcontent'), $summary . $table->show() .
        html::p(null,
            $rcmail->output->button(array(
                'command' => 'plugin.password_revive-save',
                'type' => 'input',
                'class' => 'button mainaction',
                'value' => $this->gettext('settings_button')
        )))));

    $rcmail->output->add_gui_object('password_reviveform', 'password_revive-form');

    return $rcmail->output->form_tag(array(
        'id' => 'password_revive-form',
        'name' => 'password_revive-form',
        'method' => 'post',
        'action' => './?_task=settings&_action=plugin.password_revive-save',
    ), $out);
  }

  function password_revive_login_failed($args)
  {
    $securityquestion = trim(rcube_utils::get_input_value('securityquestion', rcube_utils::INPUT_POST));
    $securityanswer   = trim(rcube_utils::get_input_value('securityanswer', rcube_utils::INPUT_POST));
    $account          = trim(rcube_utils::get_input_value('account', rcube_utils::INPUT_POST));
    $submitted        = trim(rcube_utils::get_input_value('submitted', rcube_utils::INPUT_POST));

    if ($submitted === 'password_revive')
    {
      $this->add_texts('localization/');
      $rcmail = rcmail::get_instance();
      $this->load_config();

      $account = strtolower($account);

      // valid email
      $account = rcube_utils::check_email($account) ? $account : false;

      // we are not logged in, so we have to get prefs directly
      // at the same time check to see is submitted account exists
      if ($account)
      {
        $db = $rcmail->get_dbh();

        $res = $db->query("SELECT preferences FROM " . $db->table_name('users') . " WHERE username='$account'", null);
        if ($db->num_rows($res))
        {
          $arr_prefs = $db->fetch_array($res);
          $arr_prefs = unserialize($arr_prefs[0]);

          // get user prefs
          $prefs_email    = $arr_prefs['password_revive']['email'];
          $prefs_question = $arr_prefs['password_revive']['question'];
          $prefs_answer   = $arr_prefs['password_revive']['answer'];
          $prefs_disabled = $arr_prefs['password_revive']['disabled'];
        }
      }

      // get configs
      $admin_email  = trim($rcmail->config->get('password_revive_admin_notify'));
      $query        = trim($rcmail->config->get('password_revive_query'));
      $encryption   = trim($rcmail->config->get('password_revive_encryption'));
      $support_url  = trim($rcmail->config->get('support_url'));
      $product_name = trim($rcmail->config->get('product_name'));

      // if false, '' or NULL assume PLAIN encryption
      if (! $encryption) {
        $encryption = 'PLAIN';
      }

      $encryption = strtoupper($encryption);

      // generate a random 8 character password to send to user
      $temp_password = substr(sha1(mt_rand().microtime(true)), 0, 8);

      if ($encryption == 'PLAIN')
        $_temp_password = $temp_password;
      else if ($encryption == 'MD5')
        $_temp_password = md5($temp_password);
      else if ($encryption == 'SHA1')
        $_temp_password = sha1($temp_password);
      else
        $_temp_password = false;

      // valid all emails
      $admin_email = rcube_utils::check_email($admin_email) ? $admin_email : false;
      $prefs_email = rcube_utils::check_email($prefs_email) ? $prefs_email : false;

      // essential params need to be set there
      if ($_temp_password AND $account AND $query AND $securityquestion AND $securityanswer AND $prefs_email AND $prefs_disabled == '0')
      {
        // security question and which one picked need to both match or fail
        if (strtolower($securityanswer) == strtolower($prefs_answer) AND strtolower($securityquestion) == strtolower($prefs_question))
        {
          // since $account was verified as a real user in the rcube DB, use it to get user/domain parts
          list($username, $domain) = explode('@', $account);

          // replace query place holders with actual values
          $sql = str_replace('%l', $db->quote($username, 'text'), $query);
          $sql = str_replace('%d', $db->quote($domain, 'text'), $sql);
          $sql = str_replace('%u', $db->quote($account, 'text'), $sql);
          $sql = str_replace('%p', $db->quote($_temp_password, 'text'), $sql);

          // change the password to generated temporary one for account
          $res = $db->query($sql, null);

          // if no db error, formulate email message, subject and header and mail it off
          if (! $db->is_error() AND $db->affected_rows($res))
          {
            // message
            $message = str_replace(array('[temporary_password]','[support_url]','[product_name]'),
                                   array($temp_password,         $support_url,   $product_name),
                                   $this->gettext('email_message'));

            // subject
            $subject = str_replace('[product_name]', $product_name, $this->gettext('email_subject'));

            // from header
            $header = str_replace('[product_name]', $product_name, $this->gettext('email_from_header'));

            // mail the requesting person new temp password
            @mail($prefs_email, $subject, $message, $header);

            // mail the rcube admin if set in config
            if ($admin_email)
              @mail($admin_email, $subject, $message, $header);

            $rcmail->output->command('display_message', $this->gettext('login_success_msg'), 'confirmation');
          }
          else {
            $rcmail->output->command('display_message', $this->gettext('login_dbfail_msg'), 'error');
          }
        }
        else {
          $rcmail->output->command('display_message', $this->gettext('login_fail_msg'), 'error');
        }
      }
      else {
        $rcmail->output->command('display_message', $this->gettext('login_fail_msg'), 'error');
      }
    }

    $this->add_hook('template_object_message', array($this, 'password_revive_login_form'));

    return $args;
  }

  function password_revive_login_form($args)
  {
    //echo '<pre>login_form' . print_r($_POST, true) . '</pre>';
    $rcmail = rcmail::get_instance();
    $this->load_config();
    $this->add_texts('localization/');

    $skin_path = $this->local_skin_path();
    if (is_file($this->home . "/$skin_path/password_revive.css")) {
        $this->include_stylesheet("$skin_path/password_revive.css");
    }

    $_user            = trim(rcube_utils::get_input_value('_user', rcube_utils::INPUT_POST));
    $_host            = trim(rcube_utils::get_input_value('_host', rcube_utils::INPUT_POST));
    $securityquestion = trim(rcube_utils::get_input_value('securityquestion', rcube_utils::INPUT_POST));
    $securityanswer   = trim(rcube_utils::get_input_value('securityanswer', rcube_utils::INPUT_POST));
    $account          = trim(rcube_utils::get_input_value('account', rcube_utils::INPUT_POST));

    // Try to guess their account (user@domain.tld), if localhost, I am not sure how to resolve domain.
    // We know domain is set based on 'username_domain' config setting.
    $username_domain = $rcmail->config->get('username_domain');
    if (strpos($_user, '@') !== false)
      $account = $_user;
    else if ($username_domain) {
      $account = $_user . '@' . $username_domain;
    } else if ($_host AND $_host != 'localhost') {
      $account = $_user . '@' . $_host;
    } else {
      $account = '';
    }

    // create all form elements needed, names/values without an '_' are specific to this plugin
    $field1 = new html_inputfield(array('class' => 'revive_input', 'name' => 'account', 'value' => "$account", 'required' => 'required', 'autocomplete' => 'off'));
    $field2 = new html_inputfield(array('name' => 'securityanswer', 'value' => "$securityanswer", 'required' => 'required', 'autocomplete' => 'off'));
    $field3 = new html_inputfield(array('class' => 'button mainaction', 'type' => 'submit', 'value' => $this->gettext('login_button')));
    $hidden1 = new html_hiddenfield(array('nl' => 1, 'name' => 'submitted', 'value' => 'password_revive'));
    $hidden2 = new html_hiddenfield(array('nl' => 1, 'name' => '_task', 'value' => 'login'));
    $hidden3 = new html_hiddenfield(array('nl' => 1, 'name' => '_action', 'value' => 'login'));
    $hidden4 = new html_hiddenfield(array('nl' => 1, 'name' => '_timezone', 'value' => '_default_'));
    $hidden5 = new html_hiddenfield(array('nl' => 1, 'name' => '_url', 'value' => '_task=login'));
    $hidden6 = new html_hiddenfield(array('nl' => 1, 'name' => '_user', 'value' => "$_user"));
    $hidden7 = new html_hiddenfield(array('nl' => 1, 'name' => '_host', 'value' => "$_host"));

    $hiddens = $hidden1->show().$hidden2->show().$hidden3->show().$hidden4->show().$hidden5->show().$hidden6->show().$hidden7->show();

    // create select options with are our security question choices
    $select = new html_select(array('name' => 'securityquestion'));
    $select->add($this->gettext('select_petname'), 'petname');
    $select->add($this->gettext('select_movie'), 'movie');
    $select->add($this->gettext('select_highschool'), 'highschool');

    // create Question table along with all fields added
    $table = new html_table(array('cols' => 2));
    $table->add(array('colspan' => '2', 'class' => 'revive_header'), $this->gettext('login_title') . html::br() . html::br());
    $table->add('revive_left', $this->gettext('login_email_label'));
    $table->add('revive_right', $field1->show());
    $table->add('revive_left', $select->show($securityquestion));
    $table->add('revive_right', $field2->show());
    $table->add(array('colspan' => '2', 'class' => 'revive_footer'), html::br() . $field3->show());
    $table->add(array('colspan' => '2', 'class' => 'revive_footer'), html::br() . $this->gettext('login_footer'));

    // wrap table with form
    $form  = html::tag('form', array('nl' => 1, 'name' => 'form',
                                     'action' => './?_task=login&_action=plugin.password_revive-submit',
                                     'method' => 'post'), $hiddens . $table->show());

    // wrap form in a div, that will give us that nice background, matching roundcubes own login form
    $form = html::div(array('class' => 'box-inner'), $form);

    // append the whole thing above/before the login message div
    $temp_args = $args;
    unset($temp_args['content']);
    $args['content'] = $form . $args['content'];
    //echo '<pre>login_form' . print_r($temp_args, true) . '</pre>';
    return $args;
  }
}
