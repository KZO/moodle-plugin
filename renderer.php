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

class mod_instilledvideo_renderer extends plugin_renderer_base {

  /**
   * Returns html to display the instilledvideo model
   * @param object $instilledvideo The instilledvideo activity with which the model is associated
   * @param boolean $editing true if the current user can edit the model, else false.
   */
  public function display_model($instilledvideo, $editing = false) {
    global $DB;
    
    $output = '';
    
    $model = $DB->get_record('instilledvideo', array('id' => $instilledvideo->id));

    if(!$model) {
        $output .= $this->output->heading(get_string("errornovideo", "instilledvideo"));
    } else {
        $output .= '<div style="width: 100%; height: 500px; position: relative;">';
        $output .= '<iframe src="https://front-103.kzoinnovations.com/featured" style="position: absolute; width: 100%; height: 100%; border: none" />';
        $output .= '</div>';
    }

    echo '<pre>';
    print_r($model);
    echo '</pre>';

    return $output;
  }
}
