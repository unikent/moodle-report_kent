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

namespace report_kent\reports\course;

defined('MOODLE_INTERNAL') || die();

/**
 * Report turnitin view.
 */
class turnitin
{
    /**
     * Get fast info for courses.
     */
    private static function get_fast_info($course) {
        global $DB;

        $cache = \cache::make('report_kent', 'kentreports');
        if ($content = $cache->get("course_tii_{$course}")) {
            return $content;
        }

        $content = $DB->get_records('course', null, '', 'id');

        // Nope! We must build fastinfo.
        $sql = <<<SQL
            SELECT
                t.course,
                COUNT(ts.id) as submissions,
                SUM(Case
                    When ts.submission_grade IS NOT NULL THEN 1
                    ELSE 0
                END) as grades
            FROM {turnitintooltwo_submissions} ts
            INNER JOIN {turnitintooltwo} t
                ON t.id=ts.turnitintooltwoid
            GROUP BY t.course
SQL;

        foreach ($DB->get_records_sql($sql) as $data) {
            $content[$data->course]->submissions = $data->submissions;
            $content[$data->course]->grades = $data->grades;
        }

        foreach ($content as $id => $data) {
            $cache->set("turnitin_{$id}", $data);
        }

        return $content[$course];
    }

    /**
     * Returns true if a given course uses grademark.
     */
    public static function uses_grademark($courseid) {
        $info = self::get_fast_info($courseid);
        return isset($info->grades) ? $info->grades > 0 : 0;
    }

    /**
     * Count grademark courses in a category.
     */
    public static function count_grademark($category) {
        $total = 0;

        // Loop and count.
        $courses = $category->get_courses();
        foreach ($courses as $course) {
            if (self::uses_grademark($course->id)) {
               $total++;
            }
        }

        return $total;
    }
}
