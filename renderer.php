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
 * Instilled Video activity module
 *
 * @package    mod
 * @subpackage instilledvideo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/mod/instilledvideo/locallib.php');

class mod_instilledvideo_renderer extends plugin_renderer_base {

  /**
   * Returns html to display the instilledvideo model
   * @param object $instilledvideo The instilledvideo activity with which the model is associated
   * @param boolean $editing true if the current user can edit the model, else false.
   */
  public function display_video($instilledvideo, $editing = false) {
    global $USER;
    $id = optional_param('id', 0, PARAM_INT);
    $tenant_url = get_config('instilledvideo', 'tenanturl');

    if (!property_exists($USER, 'instilledaccesskey')) {
      $instilled = new \mod_instilledvideo\instilledvideo();
      $instilled->authenticate_user();
    }

    $output = '';

    if(!$instilledvideo) {
      $output .= $this->output->heading(get_string("errornovideo", "instilledvideo"));
    } else { 
      $output .= '<div style="width: 100%; height: 500px; position: relative;">';
      $output .= '<iframe allowfullscreen width="640" height="400" allow="microphone; camera" frameborder="0" src="'. $tenant_url .'/player/medium/'. $instilledvideo->mediumid .'?embed=true&display=vid&overlay=false&username=' . $USER->username .'&accessKey='. $USER->instilledaccesskey .'" style="position: absolute; width: 100%; height: 100%; border: none" />';
      $output .= '</div>';
    }

    return $output;
  }
}
