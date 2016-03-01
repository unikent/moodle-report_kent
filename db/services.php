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
 * Kent's custom reports in one plugin.
 *
 * @package    report_kent
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$services = array(
    'Kent reporting service' => array(
        'functions' => array (
            'report_kent_get_course_info',
            'report_kent_get_activity_info',
        ),
        'requiredcapability' => 'moodle/site:config',
        'restrictedusers' => 0,
        'enabled' => 1
    )
);

$functions = array(
    'report_kent_get_course_info' => array(
        'classname'   => 'report_kent\external\modulereport',
        'methodname'  => 'get_course_info',
        'description' => 'Get module information.',
        'type'        => 'read',
        'ajax'        => true
    ),
    'report_kent_get_activity_info' => array(
        'classname'   => 'report_kent\external\modulereport',
        'methodname'  => 'get_activity_info',
        'description' => 'Get activity information.',
        'type'        => 'read',
        'ajax'        => true
    )
);