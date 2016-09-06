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

class assign {
    public static function get_deadlines($showpast) {
        global $DB;

        $where = $showpast ? '' : 'WHERE a.duedate > unix_timestamp()';

        $sql = <<<SQL
    SELECT
        b.assign_id as id,
        'Assign' as type,
        b.name as name,
        b.course as course,
        b.start as start,
        b.end as end,
        b.submissions as activity,
        COUNT(ra.id) as enrolled_students
    FROM
        (
            SELECT
                a.id as assign_id,
                a.course as course_id,
                a.name as name,
                a.allowsubmissionsfromdate as start,
                a.duedate as end,
                c.shortname as course,
                COUNT(ass.userid) as submissions
            FROM
                {assign} a
            INNER JOIN {course} c ON c.id = a.course
            LEFT OUTER JOIN {assign_submission} ass ON ass.assignment = a.id
            $where
            GROUP BY a.id
        ) b
    INNER JOIN {context} ctx
        ON ctx.instanceid = b.course_id
        AND ctx.contextlevel=:contextlevel
    INNER JOIN {role_assignments} ra
        ON ra.contextid = ctx.id
    INNER JOIN {role} r
        ON r.id=ra.roleid
    WHERE
        r.shortname IN ("student", "sds_student")
    GROUP BY b.assign_id
SQL;

        return $DB->get_records_sql($sql, array(
            'contextlevel' => \CONTEXT_COURSE
        ));
    }
}
