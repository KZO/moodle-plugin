<?php
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
        } elseif (isset($updateid)) {
            $cm = $DB->get_record('course_modules', array('id' => $updateid));
            $courseid = $cm->course;
            $context = context_course::instance($courseid);
        }

        $instilled = new \local_instilled_media_gallery\instilled();
        $instilledaccesskey = $instilled->authenticate_user($context);

        $mform =& $this->_form;

        // General options.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $link = $this->get_instilled_media_gallery_link($courseid);
        $attr = array(
            'class' => 'form-group row'
        );
        $output = html_writer::start_tag('div', array('class' => 'form-group row'));
        $output .= html_writer::start_tag('div', array('class' => 'col-md-3'));
        $output .= html_writer::end_tag('div');
        $output .= html_writer::start_tag('div', array('class' => 'col-md-9'));
        $output .= get_string('youcanupload', 'instilledvideo').' '.$link;
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
    
        $mform->addElement('html', $output);

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

        // Video file.
        $attr = array(
            'id' => 'instilled-file-picker-iframe',
            'height' => '300px',
            'width' => '100%',
            'allowfullscreen' => 'true',
            'src' => $tenanturl.'/moodle/file-picker?containerId='.$defaultcontainer.'&username=' . $USER->username .'&accessKey='. $instilledaccesskey,
            'allow' => 'autoplay *; fullscreen *; encrypted-media *; camera *; microphone *;',
            'style' => 'border: 1px solid #d0d0d0;'
        );

        $iframe = html_writer::tag('iframe', '', $attr);

        $output = html_writer::start_tag('div', array('class' => 'form-group row'));
        $output .= html_writer::start_tag('div', array('class' => 'col-md-3'));
        $output .= html_writer::start_tag('label', array('class' => 'col-form-label d-inline', 'for' => 'id_instilledvideo'));
        $output .= get_string('videofile', 'instilledvideo');
        $output .= html_writer::end_tag('label');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::start_tag('div', array('class' => 'col-md-9'));
        $output .= $iframe;
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        $mform->addElement('html', $output);

        $mform->addElement('html', '<div style="display: none;">');
        $mform->addElement('text', 'mediumid', get_string('videofile', 'instilledvideo'));
        $mform->setType('mediumid', PARAM_TEXT);
        $mform->addRule('mediumid', null, 'required', null, 'client');
        $mform->addElement('html', '</div>');

        $mform->addElement('checkbox', 'showcomments', get_string('showcomments', 'instilledvideo'));
        $mform->setType('showcomments', PARAM_BOOL);

        $this->standard_coursemodule_elements();

        // Grade settings taken from scorm activity module
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
