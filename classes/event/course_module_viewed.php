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
 * Plugin event classes are defined here.
 *
 * @package    mod
 * @subpackage instilledvideo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_instilledvideo\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The course_module_viewed event class.
 *
 * @package    mod_instilledskeleton
 * @copyright  2020 Instilled <support@instilled.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_viewed extends \core\event\course_module_viewed {

  // For more information about the Events API, please visit:
  // https://docs.moodle.org/dev/Event_2
  protected function init() {
    $this->data['crud'] = 'r';
    $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    $this->data['objecttable'] = 'instilledvideo';
  }

}
