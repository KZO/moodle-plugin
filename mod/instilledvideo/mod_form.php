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
 * The main mod_instilledskeleton configuration form.
 *
 * @package    mod
 * @subpackage instilledvideo
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/filelib.php');

global $PAGE;
$PAGE->requires->js_call_amd('mod_instilledvideo/video-selector', 'init');

class mod_instilledvideo_mod_form extends moodleform_mod {

  public function definition() {
    global $CFG;

    $mform =& $this->_form;

    // General options.
    $mform->addElement('header', 'general', get_string('general', 'form'));

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
      'src' => $tenant_url.'/moodle/file-picker?containerId='.$default_container,
      'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
      'style' => 'border: 1px solid #d0d0d0;'
    );

    $iframe = html_writer::tag('iframe', '', $attr);
    $html = '<div class="form-group row fitem"><div class="col-md-3"><label class="col-form-label d-inline" for="id_instilledvideo">'.
        get_string('videofile', 'instilledvideo')
      .'</label></div><div class="col-md-9">'. $iframe .'</div></div>';
    $mform->addElement('html', $html);

    $mform->addElement('text', 'mediumid', get_string('videofile', 'instilledvideo'));
    $mform->setType('mediumid', PARAM_TEXT);
    $mform->addRule('mediumid', null, 'required', null, 'client');

    $this->standard_coursemodule_elements();

    // Grade settings taken from scorm activity module
    $this->standard_grading_coursemodule_elements();

    $this->add_action_buttons();
  }

  private function get_filemanager_options_array () {
    global $COURSE;

    return array('subdirs' => 0, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1, 'accepted_types' => array('video'));
  }

}
