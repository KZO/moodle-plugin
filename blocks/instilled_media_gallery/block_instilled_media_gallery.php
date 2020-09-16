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
 * Description.
 *
 * @since Moodle 3.7
 * @package	block_instilled_media_gallery
 * @copyright  2020 Instilled <support@instilled.com>
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_instilled_media_gallery extends block_base {
    /**
     * Set the initial properties for the block
     */
    protected function init() {
        $this->title = get_string('pluginname', 'block_instilled_media_gallery');
    }

    /**
     * Gets the content for this block
     */
    public function get_content() {
        if (!is_null($this->content)) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $course = $this->page->course;
        $context = context_course::instance($course->id);

        if (!has_capability('mod/instilledvideo:addinstance', $context)) {
            return;
        }

        if ($context = $this->get_course_context()) {
            $this->content->text = $this->get_instilled_media_gallery_link($context->instanceid);
        }

        return $this->content;
    }

    /**
     * Set the applicable formats for this block to course-view
     * @return array
     */
    public function applicable_formats() {
        return array(
        'course-view' => true
        );
    }

    /**
     * Returns a link to the media gallery.
     *
     * @return string
     */
    private function get_instilled_media_gallery_link($courseId) {
        $galleryurl = new moodle_url('/local/instilled_media_gallery/index.php', array(
        'courseid' => $courseId
        ));

        $link = html_writer::tag('a', get_string('nav_mediagallery', 'block_instilled_media_gallery'), array(
        'href' => $galleryurl->out(false)
        ));

        return $link;
    }

    /**
     * Is this context part of any course? If yes return course context.
     *
     * @return context_course context of the enclosing course, false if not found or exception
     */
    private function get_course_context() {
        // Check the current page context.  If the context is not of a course or module then return false.
        $context = context::instance_by_id($this->page->context->id);
        $iscoursecontext = $context instanceof context_course;
        if (!$iscoursecontext) {
            return false;
        }

        // If the context if a module then get the parent context.
        $coursecontext = ($context instanceof context_module) ? $context->get_course_context() : $context;

        return $coursecontext;
    }
}
