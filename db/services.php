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
            'report_kent_get_courses_for_activity'
        ),
        'requiredcapability' => 'moodle/site:config',
        'restrictedusers' => 0,
        'enabled' => 1
    )
);

$functions = array(
    'report_kent_get_courses_for_activity' => array(
        'classname'   => 'report_kent\external\activities',
        'methodname'  => 'get_courses_for_activity',
        'description' => 'Get a list of courses with a given activity.',
        'type'        => 'read',
        'ajax'        => true
    )
);
