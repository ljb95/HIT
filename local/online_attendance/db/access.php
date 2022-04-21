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
 * See https://docs.moodle.org/dev/Access_API for details.
 *
 * @package    local_obf
 * @copyright  2013-2015, Discendum Oy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$capabilities = array(

    // Can configure plugin settings.
    'local/online_attendance:progress_view' => array(
        'captype'       => 'view',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'student'           => CAP_ALLOW,
            'teacher'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
            'coursecreator'     => CAP_ALLOW,
            'manager'           => CAP_ALLOW
        )
    ),
    'local/online_attendance:progress_viewall' => array(
        'captype'       => 'view',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'student'           => PREVENT,
            'teacher'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
            'coursecreator'     => CAP_ALLOW,
            'manager'           => CAP_ALLOW
        )
    ),
    'local/online_attendance:attendance_status' => array(
        'captype'       => 'view',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'student'           => PREVENT,
            'teacher'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
            'coursecreator'     => CAP_ALLOW,
            'manager'           => CAP_ALLOW
        )
    ),
    'local/online_attendance:attendance_edit' => array(
        'captype'       => 'write',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'student'           => PREVENT,
            'teacher'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
            'coursecreator'     => CAP_ALLOW,
            'manager'           => CAP_ALLOW
        )
    ),
);
