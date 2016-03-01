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

namespace report_kent\reports\turnitin;

defined('MOODLE_INTERNAL') || die();

class audit {
    /**
     * Returns a list of Turnitin v2 assignments.
     */
    public static function get_assignments($orderby) {
        global $DB;

        $sql = <<<SQL
            SELECT
                t.id,
                GROUP_CONCAT(DISTINCT cco.name ORDER BY cco.path SEPARATOR ' / ') AS category,
                c.shortname AS module,
                t.name AS assignment,
                t.numparts AS parts,
                CASE t.anon
                    WHEN 1 THEN 'Yes'
                    ELSE 'No'
                END anonymous_marking,
                CASE
                    t.allowlate WHEN 1 THEN 'Yes'
                    ELSE 'No'
                END allow_late_submission,
                CASE
                    t.reportgenspeed
                    WHEN 0 THEN 'Generate reports immediately, first report is final'
                    WHEN 1 THEN 'Generate reports immediately, reports can be overwritten until due date'
                    ELSE 'Generate reports on due date'
                END report_generation_speed,
                CASE t.submitpapersto
                    WHEN 0 THEN 'No Repository'
                    WHEN 1 THEN 'Standard Repository'
                    ELSE 'Institutional Repository (Where Applicable)'
                END store_student_papers,
                CASE t.studentreports
                    WHEN 1 THEN 'Yes'
                    ELSE 'No'
                END student_originality_report,
                CASE
                    WHEN cm.availability IS NULL THEN 'No'
                    ELSE 'Yes'
                END access_restricted
            FROM {course} c
                JOIN {course_categories} cc
                    ON c.category = cc.id
                JOIN {course_categories} cco
                    ON CONCAT(cc.path,'/') like CONCAT(cco.path,'/%')
                JOIN {course_modules} cm
                    ON cm.course = c.id
                JOIN {modules} m
                    ON m.id = cm.module
                        AND m.name = 'turnitintooltwo'
                JOIN {turnitintooltwo} t
                    ON c.id=t.course
                        AND cm.instance = t.id
            GROUP BY c.shortname, t.name
SQL;

        if (!empty($orderby)) {
            $sql .= "ORDER BY $orderby";
        }

        return $DB->get_records_sql($sql, array());
    }
}
