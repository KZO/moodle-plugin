<?php

defined('MOODLE_INTERNAL') || die();

class block_instilled_media_gallery extends block_base {
  function init() {
    $this->title = get_string('pluginname', 'block_instilled_media_gallery');
  }

  function get_content() {
    if(!is_null($this->content)) {
      return $this->content;
    }

    $this->content = new stdClass();
    $this->content->text = '';
    $this->content->footer = '';

    if($context = $this->get_course_context()) {
      $this->content->text = $this->get_instilled_media_gallery_link($context->instanceid);
    }

    return $this->content;
  }

  function applicable_formats() {
    return array(
      'course-view' => true
    );
  }

  private function get_instilled_media_gallery_link($courseId) {
    $media_gallery_url = new moodle_url('/local/instilled_media_gallery/index.php', array(
      'courseid' => $courseId
    ));

    $link = html_writer::tag('a', get_string('nav_mediagallery', 'block_instilled_media_gallery'), array(
      'href' => $media_gallery_url->out(false)
    ));

    return $link;
  }

  private function get_course_context() {
    // Check the current page context.  If the context is not of a course or module then return false.
    $context = context::instance_by_id($this->page->context->id);
    $is_course_context = $context instanceof context_course;
    if (!$is_course_context) {
      return false;
    }

    // If the context if a module then get the parent context.
    $course_context = ($context instanceof context_module) ? $context->get_course_context() : $context;

    return $course_context;
  }
}
