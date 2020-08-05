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
 * Provides some custom settings for the certificate module
 *
 * @package    mod
 * @subpackage instilledvideo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->dirroot/mod/instilledvideo/lib.php");

    // General settings.
    $settings->add(new admin_setting_configtext('instilledvideo/username', get_string('username', 'instilledvideo'),
        get_string('username_help', 'instilledvideo'), '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('instilledvideo/apikey', get_string('apikey', 'instilledvideo'),
        get_string('apikey_help', 'instilledvideo'), '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('instilledvideo/tenanturl', get_string('tenanturl', 'instilledvideo'),
        get_string('tenanturl_help', 'instilledvideo'), '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('instilledvideo/parentcontainer', get_string('parentcontainer', 'instilledvideo'),
        get_string('parentcontainer_help', 'instilledvideo'), '', PARAM_TEXT, 50));
}
