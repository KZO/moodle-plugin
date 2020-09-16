<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Description.
 *
 * @since Moodle 3.7
 * @package	local_instilled_media_gallery
 * @copyright  2020 Instilled <support@instilled.com>
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage( 'local_instilled_media_gallery',
        get_string('pluginname', 'local_instilled_media_gallery'));

    $ADMIN->add( 'localplugins', $settings );

    $settings->add(new admin_setting_configtext('local_instilled_media_gallery/username',
        get_string('username', 'local_instilled_media_gallery'),
        get_string('username_help', 'local_instilled_media_gallery'), '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configpasswordunmask('local_instilled_media_gallery/apikey',
        get_string('apikey', 'local_instilled_media_gallery'),
        get_string('apikey_help', 'local_instilled_media_gallery'), '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('local_instilled_media_gallery/tenanturl',
        get_string('tenanturl', 'local_instilled_media_gallery'),
        get_string('tenanturl_help', 'local_instilled_media_gallery'), '', PARAM_URL, 100));

    // This is the ID of the container on Instilled that will store videos.
    // For example, if an Instilled tenant is setup with a default container at 
    // https://instilled.com/containers/1741037833319093342, the ID is 1741037833319093342.
    $settings->add(new admin_setting_configtext('local_instilled_media_gallery/defaultcontainer',
        get_string('defaultcontainer', 'local_instilled_media_gallery'),
        get_string('defaultcontainer_help', 'local_instilled_media_gallery'), '', PARAM_TEXT, 50));
    
    // This is the ID of the group on Instilled that has access to upload and edit videos.
    $settings->add(new admin_setting_configtext('local_instilled_media_gallery/teachergroup',
        get_string('teachergroup', 'local_instilled_media_gallery'),
        get_string('teachergroup_help', 'local_instilled_media_gallery'), '', PARAM_TEXT, 50));
}