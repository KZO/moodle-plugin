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
 * Reder file for mod_instilledvideo
 *
 * @package   mod_instilledvideo
 * @copyright 2020 Instilled <support@instilled.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/instilled_media_gallery/locallib.php');

class mod_instilledvideo_renderer extends plugin_renderer_base {

    public function display_video($instilledvideo) {
        global $USER;

        $instilled = new \local_instilled_media_gallery\instilled();
        $instilledaccesskey = $instilled->authenticate_user();

        $id = optional_param('id', 0, PARAM_INT);
        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');

        $output = '';

        if (!$instilledvideo) {
            $output .= $this->output->heading(get_string("errornovideo", "instilledvideo"));
        } else {
            $showcomments = $instilledvideo->showcomments ? '' : '&display=vid';
            $output .= '<div>' . $instilledvideo->intro . '</div>';
            $attr = array(
                'id' => 'instilled-video-iframe',
                'height' => '640',
                'width' => '400',
                'allowfullscreen' => 'true',
                'src' => $tenanturl .'/player/medium/'. $instilledvideo->mediumid .'?embed=true&'.$showcomments.'&overlay=false&username=' . $USER->username .'&accessKey='. $instilledaccesskey,
                'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
                'style' => 'width: 100%; min-height: 640px; border: none'
            );
            $output .= html_writer::tag('iframe', '', $attr);

            // After a user watches a video, the grade book needs to be updated with the view time.
            // Schedule multiple tasks in the future to check the video view time.

            // Check 5 minutes later.
            $task1 = new \mod_instilledvideo\task\get_video_view_stats();
            $task1->set_next_run_time(time() + 60 * 5);
            $task1->set_custom_data(array($USER->username, $instilledvideo->mediumid));
            \core\task\manager::queue_adhoc_task($task1);

            // Check 4 hours later.
            $task2 = new \mod_instilledvideo\task\get_video_view_stats();
            $task2->set_next_run_time(time() + 60 * 60 * 4);
            $task2->set_custom_data(array($USER->username, $instilledvideo->mediumid));
            \core\task\manager::queue_adhoc_task($task2);
        }

        return $output;
    }
}
