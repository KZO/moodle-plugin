<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
  $settings = new admin_settingpage( 'local_instilled_media_gallery', get_string('pluginname', 'local_instilled_media_gallery'));
 
  $ADMIN->add( 'localplugins', $settings );
  
  $settings->add(new admin_setting_configtext('local_instilled_media_gallery/username', get_string('username', 'local_instilled_media_gallery'),
    get_string('username_help', 'local_instilled_media_gallery'), '', PARAM_TEXT, 50));

  $settings->add(new admin_setting_configtext('local_instilled_media_gallery/apikey', get_string('apikey', 'local_instilled_media_gallery'),
    get_string('apikey_help', 'local_instilled_media_gallery'), '', PARAM_TEXT, 50));

  $settings->add(new admin_setting_configtext('local_instilled_media_gallery/tenanturl', get_string('tenanturl', 'local_instilled_media_gallery'),
    get_string('tenanturl_help', 'local_instilled_media_gallery'), '', PARAM_TEXT, 50));

  $settings->add(new admin_setting_configtext('local_instilled_media_gallery/defaultcontainer', get_string('defaultcontainer', 'local_instilled_media_gallery'),
    get_string('defaultcontainer_help', 'local_instilled_media_gallery'), '', PARAM_TEXT, 50));
}