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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $category = new admin_category('kentreports', 'Kent Reports');

    $category->add('kentreports', new admin_externalpage(
        'kentoverviewreport',
        'Kent Overview',
        new \moodle_url("/report/kent/reports/overview/index.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'reportactivitiesreport',
        'Category-Based Activity Usage',
        new \moodle_url("/report/kent/reports/activities/index.php")
    ));


    $category->add('kentreports', new admin_externalpage(
        'coursecatcountsreport',
        'Category-Based Course Counts',
        new \moodle_url("/report/kent/reports/category/index.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'coursemodulecountsreport',
        'Category-Based Activity Counts',
        new \moodle_url("/report/kent/reports/category/activity.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'courseturnitincountsreport',
        'Category-Based Turnitin Counts',
        new \moodle_url("/report/kent/reports/category/turnitin.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'manualcoursereport',
        'Manual Courses',
        new \moodle_url("/report/kent/reports/courses/manual.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'reportconnectcourse',
        'Connect Courses',
        new \moodle_url("/report/kent/reports/connect/index.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'reportstudentactivity',
        'Student Activity',
        new \moodle_url("/report/kent/reports/studentactivity/index.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'reportfilesize',
        'Filesize',
        new \moodle_url("/report/kent/reports/filesize/index.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'reportkentplayer',
        'Kentplayer Report',
        new \moodle_url("/report/kent/reports/kentplayer/index.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'reportdeadlines',
        'Deadlines',
        new \moodle_url("/report/kent/reports/deadlines/index.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'reportturnitinaudit',
        'Turnitin Audit',
        new \moodle_url("/report/kent/reports/turnitinaudit/index.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'reportturnitingrademark',
        'Turnitin Grademark Usage',
        new \moodle_url("/report/kent/reports/turnitinaudit/grademark.php")
    ));

    $category->add('kentreports', new admin_externalpage(
        'readinglistcoursereport',
        'Reading Lists report',
        new \moodle_url("/report/kent/reports/courses/readinglists.php")
    ));

    $ADMIN->add('reports', $category);

    // No report settings.
    $settings = null;
}
