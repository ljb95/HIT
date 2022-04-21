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
 * See https://docs.moodle.org/dev/Events_API for details.
 *
 * @package    local_online_attendance
 * @copyright  jinotech
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$observers = array(
    array(
        'eventname' => '\core\event\course_created',
        'callback'  => 'local_online_attendance_observer::course_created',
    ),
    array(
        'eventname' => 'core\event\course_module_created',
        'callback'  => 'local_online_attendance_observer::course_module_created',
    ),
    array(
        'eventname' => 'core\event\course_module_deleted',
        'callback'  => 'local_online_attendance_observer::course_module_deleted',
    ),
    array(
        'eventname' => 'core\event\course_module_deleted',
        'callback'  => 'local_online_attendance_observer::course_module_deleted',
    )
);
