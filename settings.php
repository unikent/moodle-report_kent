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
    $ADMIN->add('reports', new admin_externalpage('reportconnectcourse', 'Connect Courses', new \moodle_url("/report/kent/reports/connect/index.php")));
    $ADMIN->add('reports', new admin_externalpage('reportstudentactivity', 'Student Activity', new \moodle_url("/report/kent/reports/studentactivity/index.php")));
    $ADMIN->add('reports', new admin_externalpage('reportfilesize', 'Filesize', new \moodle_url("/report/kent/reports/filesize/index.php")));
    $ADMIN->add('reports', new admin_externalpage('reportkentplayer', 'Kentplayer Report', new \moodle_url("/report/kent/reports/kentplayer/index.php")));
    $ADMIN->add('reports', new admin_externalpage('reportdeadlines', 'Deadlines', new \moodle_url("/report/kent/reports/deadlines/index.php")));

    // No report settings.
    $settings = null;
}
