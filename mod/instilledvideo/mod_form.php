<?php
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/instilled_media_gallery/lib.php');

global $USER, $PAGE;

$instilled = new \local_instilled_media_gallery\instilled();
if (!property_exists($USER, 'instilledaccesskey') || !$USER->instilledaccesskey) {
  $instilled->authenticate_user();
}

$PAGE->requires->js_call_amd('mod_instilledvideo/video-selector', 'init');

class mod_instilledvideo_mod_form extends moodleform_mod {

  public function definition() {
    global $CFG, $USER;

    $mform =& $this->_form;

    // General options.
    $mform->addElement('header', 'general', get_string('general', 'form'));

    $link = $this->get_instilled_media_gallery_link();
    $html = '<div class="form-group row"><div class="col-md-3"></div><div class="col-md-9">'.get_string('youcanupload', 'instilledvideo').' '.$link.'</div></div>';
    $mform->addElement('html', $html);

    $mform->addElement('text', 'name', get_string('videotitle', 'instilledvideo'), array('size' => '64'));

    if (!empty($CFG->formatstringstriptags)) {
      $mform->setType('name', PARAM_TEXT);
    } else {
      $mform->setType('name', PARAM_CLEANHTML);
    }

    $mform->addRule('name', null, 'required', null, 'client');
    $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

    // Adding the standard "intro" and "introformat" fields.
    if ($CFG->branch >= 29) {
      $this->standard_intro_elements();
    } else {
        $this->add_intro_editor();
    }

    $tenant_url = get_config('local_instilled_media_gallery', 'tenanturl');
    $default_container = get_config('local_instilled_media_gallery', 'defaultcontainer');

    // Video file.
    $attr = array(
      'id' => 'instilled-file-picker-iframe',
      'height' => '300px',
      'width' => '100%',
      'allowfullscreen' => 'true',
      'src' => $tenant_url.'/moodle/file-picker?containerId='.$default_container.'&username=' . $USER->username .'&accessKey='. $USER->instilledaccesskey,
      'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
      'style' => 'border: 1px solid #d0d0d0;'
    );

    $iframe = html_writer::tag('iframe', '', $attr);
    $html = '<div class="form-group row fitem"><div class="col-md-3"><label class="col-form-label d-inline" for="id_instilledvideo">'.
        get_string('videofile', 'instilledvideo')
      .'</label></div><div class="col-md-9">'. $iframe .'</div></div>';
    $mform->addElement('html', $html);

    $mform->addElement('html', '<div style="display: none;">');
    $mform->addElement('text', 'mediumid', get_string('videofile', 'instilledvideo'));
    $mform->setType('mediumid', PARAM_TEXT);
    $mform->addRule('mediumid', null, 'required', null, 'client');
    $mform->addElement('html', '</div>');

    $this->standard_coursemodule_elements();

    // Grade settings taken from scorm activity module
    $this->standard_grading_coursemodule_elements();

    $this->add_action_buttons();
  }

  private function get_instilled_media_gallery_link() {
    $course_id = required_param('course', PARAM_INT);

    $media_gallery_url = new moodle_url('/local/instilled_media_gallery/index.php', array(
      'courseid' => $course_id
    ));

    $link = html_writer::tag('a', get_string('instilledmediagallery', 'instilledvideo'), array(
      'href' => $media_gallery_url->out(false)
    ));

    return $link;
  }

}
