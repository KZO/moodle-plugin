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
 * Log events for mod_instilledgallery
 *
 * @package   mod_instilledgallery
 * @copyright 2020 Instilled <support@instilled.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module' => 'instilledgallery', 'action' => 'view', 'mtable' => 'instilledgallery', 'field' => 'name'),
    array('module' => 'instilledgallery', 'action' => 'add', 'mtable' => 'instilledgallery', 'field' => 'name'),
    array('module' => 'instilledgallery', 'action' => 'update', 'mtable' => 'instilledgallery', 'field' => 'name'),
);
