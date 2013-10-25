/**
 * password_revive
 *
 * Plugin that adds a new tab to the settings section to create password retrieval
 *
 * @version 1.0
 * @author Matt Kukowski <codin247@yahoo.com>
 */
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt)
  {
    var tab = $('<span>').attr('id', 'settingstabpluginpassword_revive').addClass('tablink');

    var button = $('<a>').attr('href', rcmail.env.comm_path + '&_action=plugin.password_revive').html(rcmail.gettext('password_revive', 'password_revive')).appendTo(tab);

    rcmail.add_element(tab, 'tabs');
    rcmail.register_command('plugin.password_revive-save', function() {
      rcmail.gui_objects.password_reviveform.submit();
    }, true);
  })
}