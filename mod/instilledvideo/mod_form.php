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
 * Form for mod_instilledvideo
 *
 * @package   mod_instilledvideo
 * @copyright 2020 Instilled <support@instilled.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/instilled_media_gallery/locallib.php');

class mod_instilledvideo_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $USER, $DB, $PAGE;

        $PAGE->requires->js_call_amd('mod_instilledvideo/video-selector', 'init');

        $courseid = optional_param('course', null, PARAM_INT);
        $updateid = optional_param('update', null, PARAM_INT);

        if (isset($courseid)) {
            $context = context_course::instance($courseid);
        } else if (isset($updateid)) {
            $cm = $DB->get_record('course_modules', array('id' => $updateid));
            $courseid = $cm->course;
            $context = context_course::instance($courseid);
            $activitymodule = $DB->get_record('instilledvideo', array('id' => $cm->instance));
            $mediumid = $activitymodule->mediumid;
        }

        $instilled = new \local_instilled_media_gallery\instilled();
        $instilledaccesskey = $instilled->authenticate_user($context);

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

        $tenanturl = get_config('local_instilled_media_gallery', 'tenanturl');
        $defaultcontainer = get_config('local_instilled_media_gallery', 'defaultcontainer');

        $mediumquerystring = '';
        if (isset($mediumid)) {
            $mediumquerystring = '&mediumId=' . $mediumid;
        }

        $link = $this->get_instilled_media_gallery_link($courseid);
        $output = get_string('youcanupload', 'instilledvideo').' '.$link;
        $mform->addElement('static', 'uploadhelpertext', null, $output);

        // Video file.
        $attr = array(
            'id' => 'instilled-file-picker-iframe',
            'height' => '400',
            'width' => '900',
            'allowfullscreen' => 'true',
            'src' => $tenanturl.'/moodle/file-picker?containerId='.$defaultcontainer.'&username=' . $USER->username .'&accessKey='. $instilledaccesskey . $mediumquerystring,
            'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
            'style' => 'border: 1px solid #d0d0d0;'
        );

        $iframe = html_writer::tag('iframe', '', $attr);

        $mform->addElement('static', 'iframefilepicker', get_string('videofile', 'instilledvideo'), $iframe);

        $mform->addElement('static', 'videofileidhelper', null, get_string('videofileidhelper', 'instilledvideo'));
        $mform->addElement('text', 'mediumid', get_string('videofileid', 'instilledvideo'), array('size' => '25', 'placeholder' => get_string('videofileidplaceholder', 'instilledvideo')));
        $mform->setType('mediumid', PARAM_TEXT);
        $mform->addRule('mediumid', null, 'required', null, 'client');

        $mform->addElement('checkbox', 'showcomments', get_string('showcomments', 'instilledvideo'));
        $mform->setType('showcomments', PARAM_BOOL);

        $this->standard_coursemodule_elements();

        // Grade settings taken from scorm activity module.
        $this->standard_grading_coursemodule_elements();

        $this->add_action_buttons();
    }

    private function get_instilled_media_gallery_link($courseid) {
        $mediagalleryurl = new moodle_url('/local/instilled_media_gallery/index.php', array(
            'courseid' => $courseid
        ));

        $link = html_writer::tag('a', get_string('instilledmediagallery', 'instilledvideo'), array(
            'href' => $mediagalleryurl->out(false)
        ));

        return $link;
    }
}
