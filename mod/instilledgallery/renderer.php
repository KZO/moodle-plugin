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
 * Reder file for mod_instilledgallery
 *
 * @package   mod_instilledgallery
 * @copyright 2020 Instilled <support@instilled.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/instilled_media_gallery/locallib.php');

class mod_instilledgallery_renderer extends plugin_renderer_base {

    public function display_video($instilledgallery) {
        global $USER, $COURSE;

        $instilled = new \local_instilled_media_gallery\instilled();
        $instilledaccesskey = $instilled->authenticate_user();

        $id = optional_param('id', 0, PARAM_INT);
        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');

        $output = '';

        if (!$instilledgallery) {
            $output .= $this->output->heading(get_string("errornovideo", "instilledgallery"));
        } else {
            $showcomments = $instilledgallery->showcomments ? '' : '&display=vid';
            $output .= '<div>' . $instilledgallery->intro . '</div>';
            $attr = array(
                'id' => 'instilled-student-gallery-iframe',
                'height' => '700px',
                'width' => '100%',
                'allowfullscreen' => 'true',
                'src' => $tenanturl.'/moodle/media-gallery?containerId='.$instilledgallery->containerid.'&username=' . $USER->username .'&accessKey='. $instilledaccesskey,
                'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
                'style' => 'border: 1px solid #d0d0d0;'
            );
            $output .= html_writer::tag('iframe', '', $attr);
        }

        return $output;
    }
}
