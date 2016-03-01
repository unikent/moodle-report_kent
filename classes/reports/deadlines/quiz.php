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

namespace report_kent\reports\deadlines;

defined('MOODLE_INTERNAL') || die();

class quiz {
    public static function get_deadlines($showpast) {
        global $DB;

        $where = $showpast ? '' : 'WHERE q.timeclose > unix_timestamp()';

        $sql = <<<SQL
    SELECT
        a.quiz_id as id,
        "Quiz" as type,
        a.quiz_name as name,
        a.course_short_name as course,
        a.time_open as start,
        a.time_close as end,
        a.quiz_attempts as activity,
        COUNT(ra.id) as enrolled_students
    FROM
        (
            SELECT
                c.id as course_id,
                c.shortname as course_short_name,
                q.id as quiz_id,
                q.name as quiz_name,
                q.timeopen as time_open,
                q.timeclose as time_close,
                COUNT(a.id) as quiz_attempts
            FROM
                {quiz} q
            INNER JOIN {course} c ON c.id = q.course
            LEFT OUTER JOIN {quiz_attempts} a ON a.quiz = q.id
            $where
            GROUP BY q.id
            ORDER BY time_close ASC
        ) a
    INNER JOIN {context} ctx
        ON ctx.instanceid = a.course_id
        AND ctx.contextlevel=:contextlevel
    INNER JOIN {role_assignments} ra
        ON ra.contextid = ctx.id
    INNER JOIN {role} r
        ON r.id=ra.roleid
    WHERE
        r.shortname IN ("student", "sds_student")
    GROUP BY a.quiz_id
SQL;

        return $DB->get_records_sql($sql, array(
            'contextlevel' => \CONTEXT_COURSE
        ));
    }
}
