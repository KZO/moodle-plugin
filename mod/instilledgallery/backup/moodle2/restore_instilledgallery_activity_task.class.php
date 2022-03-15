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
 * @package    mod_instilledgallery
 * @subpackage backup-moodle2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/instilledgallery/backup/moodle2/restore_instilledgallery_stepslib.php');

/**
 * Instilled Video restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_instilledgallery_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Instilled Video only has one structure step
        $this->add_step(new restore_instilledgallery_activity_structure_step('instilledgallery_structure', 'instilledgallery.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('instilledgallery', array('intro'), 'instilledgallery');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('INSTILLEDGALLERYVIEWBYID', '/mod/instilledgallery/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('INSTILLEDGALLERYINDEX', '/mod/instilledgallery/index.php?id=$1', 'course');

        return $rules;

    }
}
