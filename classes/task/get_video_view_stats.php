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
 * Adhoc task that updates all of the existing forum_post records with no wordcount or no charcount.
 *
 * @package    mod
 * @subpackage instilledvideo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_instilledvideo\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/instilledvideo/lib.php');
require_once($CFG->dirroot.'/mod/instilledvideo/locallib.php');
 
class get_video_view_stats extends \core\task\scheduled_task {
 
  /**
   * Return the task's name as shown in admin screens.
   *
   * @return string
   */
  public function get_name() {
    return get_string('getvideoviewstats', 'mod_instilledvideo');
  }

  /**
   * Execute the task.
   */
  public function execute() {
    global $DB;
    mtrace('My task started');
    $videos = $DB->get_records('instilledvideo');

    $response = $this->get_container_stats();
    $parsed = $this->parse_json_response($response);

    foreach ($parsed as $medium_id => $users) {
      $instance = $DB->get_record('instilledvideo', array('mediumid' => $medium_id), '*', IGNORE_MISSING);
      if (!$instance) continue;

      $video_duration = $users['duration'];
      echo $video_duration;
      unset($users['duration']);

      $grades = array();
      foreach ($users as $username => $time_viewed) {
        $user = $DB->get_record('user', array('username' => $username), 'id', IGNORE_MISSING);
        if (!$user) continue;

        $grade = round($time_viewed / $video_duration * 100);

        // If more than 90% of the video is viewed, give full credit.
        // This is because Instilled does not always calculate full video viewings correctly.
        if ($grade > 90) {
          $grade = 100;
        }

        $grades[$user->id] = (object)array(
          'rawgrade' => $grade,
          'userid' => $user->id
        );
      }

      \instilledvideo_grade_item_update($instance, $grades);
    }

    mtrace('My task ended');
  }

  protected function get_container_stats() {
    $method = 'GET';
    $tenant_url = get_config('instilledvideo', 'tenanturl');
    $parent_container = get_config('instilledvideo', 'parentcontainer');
    $url = $tenant_url . '/api/reports/media_viewed_aggregated_by_session?include=media,users&page_size_primary=100000000&page_size_related=100000000&container_id=' . $parent_container;

    $stats = \mod_instilledvideo\instilledvideo::call_api($method, $url);
    $stats = json_decode($stats);
    return $stats;
  }

  protected function parse_json_response($response) {
    $parsed = [];

    foreach ($response->linked->media as $medium) {
      $parsed[$medium->id]['duration'] = $medium->trt_msec;
    }

    foreach ($response->report_entities as $report) {
      $medium_id = $report->links->media;
      $username = $report->links->viewed_by->username;
      $time_viewed = $report->time_viewed_msec;
      if (!array_key_exists($username, $parsed[$medium_id])) {
        $parsed[$medium_id][$username] = $time_viewed;
      } else if ($parsed[$medium_id][$username] < $time_viewed) {
        $parsed[$medium_id][$username] = $time_viewed;
      }
    }

    return $parsed;
  }
}
