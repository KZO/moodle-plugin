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

require_once ($CFG->dirroot . '/mod/instilledvideo/locallib.php');

/**
 * Adhoc task that sends a video to instilled for processing.
 */
class send_video_to_instilled extends \core\task\adhoc_task {

  public function execute() {
    mtrace('My task started');
    $data = $this->get_custom_data();

    $medium = $this->create_medium($data);
    $actions = $medium->media->meta->actions;
    $action = $this->get_action($actions, 'ORIGINAL_RENDITION_CREATE');

    $original_video = $this->create_original_video($action->href);
    $actions = $original_video->original_videos->meta->actions;
    $action = $this->get_action($actions, 'ORIGINAL_RENDITION_UPLOAD_WITH_PRESIGNED_URL');

    mtrace('UPLOADING VIDEO');
    $this->upload_video($action->href, $data->coursemodule);
    mtrace('UPLOADING VIDEO COMPLETE');

    $this->process_video($medium->media->id);
    $this->update_course_table($data->instance, $medium->media->id);
    mtrace('My task ended');
  }

  protected function create_medium($data) {
    $method = 'POST';
    $tenant_url = get_config('instilledvideo', 'tenanturl');
    $parent_container = get_config('instilledvideo', 'parentcontainer');
    $url = $tenant_url . '/api/containers/'. $parent_container .'/medium';
    $post_data = json_encode(array('media'=>array('title' => $data->name, 'content_type' => 'VIDEO')), JSON_FORCE_OBJECT);

    $medium = \mod_instilledvideo\instilledvideo::call_api($method, $url, $post_data);
    $medium = json_decode($medium);
    return $medium;
  }

  protected function create_original_video($url) {
    $method = 'POST';
    $post_data = json_encode(array('original_videos'=>array('includes' => 'parent,medium,container.medium,containers')), JSON_FORCE_OBJECT);

    $original_video = \mod_instilledvideo\instilledvideo::call_api($method, $url, $post_data);
    $original_video = json_decode($original_video);
    return $original_video;
  }

  protected function upload_video($url, $course_id) {
    global $CFG;

    $context = \context_module::instance($course_id);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_instilledvideo', 'content', 0, 'sortorder DESC, id ASC', false);

    $file_name = '';
    $file_size = '';
    $file_type = '';
    $file_path = '';
    foreach ($files as $f) {
      $file_name = $f->get_filename();
      $file_size = $f->get_filesize();
      $mime_type = $f->get_mimetype();
      $file_path = $CFG->dirroot . '/mod/instilledvideo/' . $file_name;
      $f->copy_content_to($file_path);
    }

    $curl = curl_init();

    $file_handler = fopen($file_path, 'r');

    try {
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_PUT, 1);
      curl_setopt($curl, CURLOPT_INFILE, $file_handler);
      curl_setopt($curl, CURLOPT_INFILESIZE, $file_size);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: ' . $mime_type,
        'Content-Length: ' . $file_size,
        'Connection: keep-alive'
      ));

      $response = curl_exec($curl);
      fclose($file_handler);
    } catch (Exception $err) {
      echo $err;
    }

    if (curl_errno($curl)) {
      throw new \Exception(curl_error($curl));
    }

    curl_close($curl);

    return $response;
  }

  protected function process_video($medium_id) {
    $method = 'POST';
    $tenant_url = get_config('instilledvideo', 'tenanturl');
    $url = $tenant_url . '/api/media/' . $medium_id .'/process_video_rendition';

    $response = \mod_instilledvideo\instilledvideo::call_api($method, $url);
    $response = json_decode($response);
    return $response;
  }

  protected function update_course_table($instance_id, $medium_id) {
    global $DB;
    $DB->set_field('instilledvideo', 'mediumid', $medium_id, array('id' => $instance_id));
  }

  protected function get_action($arr, $key) {
    foreach($arr as $action) {
      if ($action->label === $key) {
        return $action;
      }
    }
  }
}
