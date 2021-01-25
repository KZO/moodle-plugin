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
 * Task for mod_instilledvideo
 *
 * @package   mod_instilledvideo
 * @copyright 2020 Instilled <support@instilled.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_instilledvideo\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/instilled_media_gallery/locallib.php');

/**
 * Adhoc task that gets video view stats for a specific user.
 */
class get_video_view_stats extends \core\task\adhoc_task {

    public function execute() {
        mtrace('My task started');
        $data = $this->get_custom_data();

        $username = $data[0];
        $mediumid = $data[1];
        $instanceid = $data[2];
        $grademax = $data[3];

        $instilled = new \local_instilled_media_gallery\instilled();
        $accesskey = $instilled->authenticate_user(null, $username);

        $viewdata = $this->get_video_view_data($accesskey, $username, $mediumid);
        $percentviewed = $this->calculate_percent_viewed($viewdata);
        $score = round($percentviewed / $grademax, 1);
        $this->update_gradebook($username, $instanceid, $score);

        mtrace('My task ended');
    }

    /**
     * Get video view statistics for the user and medium.
     */
    protected function get_video_view_data($accesskey, $username, $mediumid) {
        $method = 'GET';
        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');
        $url = $tenanturl . '/api/media/'. $mediumid .'?include=unique_viewed_ranges';

        $result = \local_instilled_media_gallery\instilled::call_api($method, $url, false, $username, $accesskey);
        $result = json_decode($result);

        $fragments = $result->linked->unique_viewed_ranges[0]->unique_viewed_ranges;
        $duration = $result->media->trt_msec;

        return array(
            $fragments,
            $duration,
        );
    }

    /**
     * Calculate the total percentage of video viewed.
     */
    protected function calculate_percent_viewed($viewdata) {
        $fragments = $viewdata[0];
        $duration = $viewdata[1];

        $timeviewed = 0;
        foreach ($fragments as $fragment) {
            $timeviewed += $fragment->end_msec - $fragment->start_msec;
        }
        $percentviewed = round($timeviewed / $duration * 100);

        // If more than 95% of the video is viewed, give full credit.
        // This is because Instilled does not always calculate full video viewings correctly.
        if ($percentviewed > 95) {
            $percentviewed = 100;
        }

        return $percentviewed;
    }

    /**
     * Update the gradebook with the percentage viewed
     */
    protected function update_gradebook($username, $instanceid, $percentviewed) {
        global $DB;
        $instance = $DB->get_record('instilledvideo', array('id' => $instanceid), '*', IGNORE_MISSING);
        $user = $DB->get_record('user', array('username' => $username), 'id', IGNORE_MISSING);

        $grades = array();
        $grades[$user->id] = (object)array(
            'rawgrade' => $percentviewed,
            'userid' => $user->id
        );

        \instilledvideo_grade_item_update($instance, $grades);
    }
}