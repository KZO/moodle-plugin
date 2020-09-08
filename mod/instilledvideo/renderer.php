<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/instilled_media_gallery/lib.php');

global $USER;
if (!property_exists($USER, 'instilledaccesskey') || !$USER->instilledaccesskey) {
  $instilled = new \local_instilled_media_gallery\instilled();
  $instilled->authenticate_user();
}

class mod_instilledvideo_renderer extends plugin_renderer_base {
  public function display_video($instilledvideo, $editing = false) {
    global $USER;
    $id = optional_param('id', 0, PARAM_INT);
    $tenant_url = get_config('local_instilled_media_gallery', 'tenanturl');

    $output = '';

    if(!$instilledvideo) {
      $output .= $this->output->heading(get_string("errornovideo", "instilledvideo"));
    } else {
      $show_comments = $instilledvideo->showcomments ? '' : '&display=vid';
      $output .= '<div>' . $instilledvideo->intro . '</div>';
      $attr = array(
        'id' => 'instilled-video-iframe',
        'height' => '640',
        'width' => '400',
        'allowfullscreen' => 'true',
        'src' => $tenant_url .'/player/medium/'. $instilledvideo->mediumid .'?embed=true&'.$show_comments.'&overlay=false&username=' . $USER->username .'&accessKey='. $USER->instilledaccesskey,
        'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
        'style' => 'width: 100%; height: 640px; border: none'
      );
      $output = html_writer::tag('iframe', '', $attr);
    }

    return $output;
  }
}
