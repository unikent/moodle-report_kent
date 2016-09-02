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

namespace report_kent\reports;

defined('MOODLE_INTERNAL') || die();

/**
 * Readinglists report class
 */
class readinglists
{
    /**
     * Gather readinglists info.
     */
    public static function get_data() {
        global $DB;

        $cache = \cache::make('report_kent', 'kentreports');
        $coursedata = $cache->get('readinglistdata');

        // Build the data.
        $courses = $DB->get_records('course');
        foreach ($courses as $course) {
            if ($course->id <= 1 || isset($coursedata[$course->id])) {
                continue;
            }

            $coursedata[$course->id] = static::rebuild_course($course, false);
        }

        $cache->set('readinglistdata', $coursedata);
        return $coursedata;
    }

    /**
     * Rebuild info for a course.
     */
    public static function rebuild_course($course, $recache = true) {
        $listapi = new \mod_aspirelists\course($course);
        $currentlist = $listapi->has_list(false);
        $pastlist = $currentlist ? 1 : $listapi->has_list();
        $data = array(
            'shortname' => $course->shortname,
            'fullname' => $course->fullname,
            'currentlist' => $currentlist,
            'pastlist' => $pastlist
        );

        if ($recache) {
            $cache = \cache::make('report_kent', 'kentreports');
            $coursedata = $cache->get('readinglistdata');
            $coursedata[$course->id] = $data;
            $cache->set('readinglistdata', $coursedata);
        }

        return $data;
    }
}
