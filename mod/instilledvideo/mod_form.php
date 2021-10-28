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

use core_grades\component_gradeitems;

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

        $this->instilledvideo_grading_coursemodule_elements();

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

    private function instilledvideo_grading_coursemodule_elements() {
        global $COURSE, $CFG;

        $this->gradedorrated = 'graded';

        $itemnumber = 0;
        $gradefieldname = component_gradeitems::get_field_name_for_itemnumber('mod_instilledvideo', $itemnumber, 'grade');
        $gradecatfieldname = component_gradeitems::get_field_name_for_itemnumber('mod_instilledvideo', $itemnumber, 'gradecat');
        $gradepassfieldname = component_gradeitems::get_field_name_for_itemnumber('mod_instilledvideo', $itemnumber, 'gradepass');

        $mform =& $this->_form;
        $isupdate = !empty($this->_cm);
        $gradeoptions = array('isupdate' => $isupdate,
                              'currentgrade' => false,
                              'hasgrades' => false,
                              'canrescale' => $this->_features->canrescale,
                              'useratings' => $this->_features->rating);

        if ($this->_features->hasgrades) {
            if ($this->_features->gradecat) {
                $mform->addElement('header', 'modstandardgrade', get_string('gradenoun'));
            }

            //if supports grades and grades arent being handled via ratings
            if ($isupdate) {
                $gradeitem = grade_item::fetch(array('itemtype' => 'mod',
                                                        'itemmodule' => $this->_cm->modname,
                                                        'iteminstance' => $this->_cm->instance,
                                                        'itemnumber' => 0,
                                                        'courseid' => $COURSE->id));

                if ($gradeitem) {
                    $gradeoptions['currentgrade'] = $gradeitem->grademax;
                    $gradeoptions['currentgradetype'] = $gradeitem->gradetype;
                    $gradeoptions['currentscaleid'] = $gradeitem->scaleid;
                    $gradeoptions['hasgrades'] = $gradeitem->has_grades();
                }
            }

            $p1 = get_string('instilledvideo:gradehelp1', 'instilledvideo') . ' <strong>' . get_string('instilledvideo:videotimeviewed', 'instilledvideo') . '</strong>'
                . get_string('instilledvideo:gradehelp2', 'instilledvideo') . ' <em>' . get_string('instilledvideo:point', 'instilledvideo') . '</em>.';
            $p2 = get_string('instilledvideo:gradehelp3', 'instilledvideo') . ' <em>' . get_string('instilledvideo:point', 'instilledvideo') . '</em> '
                . get_string('instilledvideo:gradehelp4', 'instilledvideo');

            $mform->addElement('html', '<p>'.$p1.'</p>');
            $mform->addElement('html', '<p>'.$p2.'</p>');
            $mform->addElement('modgrade', $gradefieldname, get_string('gradenoun'), $gradeoptions);
            $default = isset($gradeitem->gradetype) ? $gradeitem->gradetype : 0;
            $mform->setDefault($gradefieldname, $default);

            if ($this->_features->advancedgrading
                    and !empty($this->current->_advancedgradingdata['methods'])
                    and !empty($this->current->_advancedgradingdata['areas'])) {

                if (count($this->current->_advancedgradingdata['areas']) == 1) {
                    // if there is just one gradable area (most cases), display just the selector
                    // without its name to make UI simplier
                    $areadata = reset($this->current->_advancedgradingdata['areas']);
                    $areaname = key($this->current->_advancedgradingdata['areas']);
                    $mform->addElement('select', 'advancedgradingmethod_'.$areaname,
                        get_string('gradingmethod', 'core_grading'), $this->current->_advancedgradingdata['methods']);
                    $mform->addHelpButton('advancedgradingmethod_'.$areaname, 'gradingmethod', 'core_grading');
                    $mform->hideIf('advancedgradingmethod_'.$areaname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');

                } else {
                    // the module defines multiple gradable areas, display a selector
                    // for each of them together with a name of the area
                    $areasgroup = array();
                    foreach ($this->current->_advancedgradingdata['areas'] as $areaname => $areadata) {
                        $areasgroup[] = $mform->createElement('select', 'advancedgradingmethod_'.$areaname,
                            $areadata['title'], $this->current->_advancedgradingdata['methods']);
                        $areasgroup[] = $mform->createElement('static', 'advancedgradingareaname_'.$areaname, '', $areadata['title']);
                    }
                    $mform->addGroup($areasgroup, 'advancedgradingmethodsgroup', get_string('gradingmethods', 'core_grading'),
                        array(' ', '<br />'), false);
                }
            }

            if ($this->_features->gradecat) {
                $mform->addElement('select', $gradecatfieldname,
                        get_string('gradecategoryonmodform', 'grades'),
                        grade_get_categories_menu($COURSE->id, $this->_outcomesused));
                $mform->addHelpButton($gradecatfieldname, 'gradecategoryonmodform', 'grades');
                $mform->hideIf($gradecatfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');
            }

            // Grade to pass.
            $mform->addElement('text', $gradepassfieldname, get_string($gradepassfieldname, 'grades'));
            $mform->addHelpButton($gradepassfieldname, $gradepassfieldname, 'grades');
            $mform->setDefault($gradepassfieldname, '');
            $mform->setType($gradepassfieldname, PARAM_RAW);
            $mform->hideIf($gradepassfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');
        }
    }
}
