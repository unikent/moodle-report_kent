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
 * Report course.
 */
class course
{
    use \local_kent\traits\datapod;

    const STATUS_ACTIVE = 1;
    const STATUS_RESTING = 2;
    const STATUS_EMPTY = 4;
    const STATUS_UNUSED = 8;

    /**
     * Constructor.
     */
    public function __construct($data) {
        $this->set_data($data);
    }

    /**
     * Returns fast info for a course.
     */
    private function get_fast_info() {
        global $DB;

        $cache = \cache::make('report_kent', 'kentreports');
        if ($content = $cache->get("course_{$this->id}")) {
            return $content;
        }

        // Build course info and initialise.
        $content = $DB->get_records_sql('SELECT c.*, COUNT(cc.id) = 0 AS ismanual
            FROM {course} c
            LEFT OUTER JOIN {connect_course} cc ON cc.mid = c.id
            GROUP BY c.id
        ');

        foreach ($content as $id => $course) {
            $content[$id]->enrolments = 0;
            $content[$id]->sdsenrolments = 0;
            $content[$id]->blocks = array();
            $content[$id]->activities = array();
            $content[$id]->assignsubs = 0;
            $content[$id]->modules = 0;
            $content[$id]->distinct_modules = 0;
            $content[$id]->sections = 0;
            $content[$id]->section_length = 0;
            $content[$id]->guest_enabled = 0;
            $content[$id]->guest_password = 0;
            $content[$id]->turnitins = array();
        }

        // Build enrolments.
        $sql = <<<SQL
            SELECT
                c.id as courseid, COALESCE(COUNT(ra.id), 0) cnt,
                COALESCE(SUM(CASE WHEN r.shortname = 'sds_student' THEN 1 ELSE 0 END), 0) cnt2
            FROM {course} c
            INNER JOIN {context} ctx
                    ON ctx.instanceid = c.id
                    AND ctx.contextlevel = 50
            INNER JOIN {role_assignments} ra
                    ON ra.contextid = ctx.id
            INNER JOIN {role} r
                    ON ra.roleid = r.id AND r.shortname IN ('student', 'sds_student')
            GROUP BY c.id
SQL;

        foreach ($DB->get_records_sql($sql) as $data) {
            $content[$data->courseid]->enrolments = $data->cnt;
            $content[$data->courseid]->sdsenrolments = $data->cnt2;
        }

        // Build course modules.
        $sql = <<<SQL
            SELECT CONCAT_WS('_', c.id, m.id) as id, c.id as courseid, m.id as moduleid, m.name, COALESCE(COUNT(cm.id), 0) cnt
            FROM {course} c
            INNER JOIN {course_modules} cm
                ON c.id = cm.course
            INNER JOIN {modules} m
                ON m.id = cm.module
            GROUP BY c.id, m.id
SQL;

        foreach ($DB->get_records_sql($sql) as $data) {
            $content[$data->courseid]->activities[$data->name] = $data->cnt;
        }

        // Build course module counts.
        $sql = <<<SQL
            SELECT c.id as courseid, COALESCE(COUNT(cm.id), 0) cnt, COALESCE(COUNT(DISTINCT cm.module), 0) cnt2
            FROM {course} c
            LEFT OUTER JOIN {course_modules} cm
                ON c.id = cm.course
            GROUP BY c.id
SQL;

        foreach ($DB->get_records_sql($sql) as $data) {
            $content[$data->courseid]->modules = $data->cnt;
            $content[$data->courseid]->distinct_modules = $data->cnt2;
        }

        // Build assign sub info.
        $sql = <<<SQL
            SELECT c.id as courseid, COALESCE(COUNT(asub.id), 0) as cnt
            FROM {course} c
            LEFT OUTER JOIN {assign} a
                ON a.course = c.id
            LEFT OUTER JOIN {assign_submission} asub
                ON asub.assignment = a.id
            GROUP BY c.id
SQL;

        foreach ($DB->get_records_sql($sql) as $data) {
            $content[$data->courseid]->assignsubs = $data->cnt;
        }

        // Build block info.
        $sql = <<<SQL
            SELECT CONCAT_WS('_', c.id, bi.blockname) as id, c.id as courseid, bi.blockname as name, COALESCE(COUNT(bi.id), 0) cnt
            FROM {course} c
            INNER JOIN {context} ctx
                ON ctx.instanceid = c.id AND ctx.contextlevel = ?
            INNER JOIN {block_instances} bi
                ON bi.parentcontextid = ctx.id
            GROUP BY c.id, bi.blockname
SQL;

        foreach ($DB->get_records_sql($sql, array(\CONTEXT_COURSE)) as $data) {
            $content[$data->courseid]->blocks[$data->name] = $data->cnt;
        }

        // Build section info.
        $sql = <<<SQL
            SELECT c.id as courseid, COALESCE(COUNT(cs.id), 0) as cnt, LENGTH(GROUP_CONCAT(cs.summary SEPARATOR '')) as len
            FROM {course} c
            LEFT OUTER JOIN {course_sections} cs
                ON cs.course = c.id
            GROUP BY c.id
SQL;

        foreach ($DB->get_records_sql($sql) as $data) {
            $content[$data->courseid]->sections = $data->cnt;
            $content[$data->courseid]->section_length = $data->len;
        }

        // Build enrol/guest info.
        $sql = <<<SQL
            SELECT
                e.courseid,
                SUM(
                    CASE e.status WHEN 1
                        THEN 1
                        ELSE 0
                    END
                ) statcnt,
                SUM(
                    CASE WHEN e.password <> ''
                        THEN 1
                        ELSE 0
                    END
                ) keycnt
            FROM {enrol} e
                WHERE enrol = 'guest'
            GROUP BY e.courseid
SQL;

        foreach ($DB->get_records_sql($sql) as $data) {
            $content[$data->courseid]->guest_enabled = $data->statcnt > 0;
            $content[$data->courseid]->guest_password = $data->keycnt > 0;
        }

        // Turnitin grades.
        $sql = <<<SQL
            SELECT
                CONCAT_WS('_', c.id, t.id) as id,
                c.id AS courseid,
                t.id as tiiid,
                COUNT(ts.id) as submissions,
                SUM(Case
                    WHEN ts.submission_grade IS NOT NULL
                    THEN 1
                    ELSE 0
                END) AS grades
            FROM {course} c
            INNER JOIN {turnitintooltwo} t
                ON t.course = c.id
            INNER JOIN {turnitintooltwo_submissions} ts
                ON ts.turnitintooltwoid = t.id
            GROUP BY c.id, t.id
SQL;

        foreach ($DB->get_records_sql($sql) as $data) {
            $content[$data->courseid]->turnitins[] = (object)array(
                'submissions' => $data->submissions,
                'grades' => $data->grades
            );
        }

        // Cache it all.
        foreach ($content as $id => $data) {
            $cache->set("course_$id", $data);
        }

        return $content[$this->id];
    }

    /**
     * Returns the course's state:
     *  - Active: Has students/content and is visible.
     *  - Resting: Has students/content but is not visible.
     *  - Empty: Has students but no content.
     *  - Unused: Has no students.
     */
    public function get_state($astext = false) {
        $info = $this->get_fast_info();

        $state = self::STATUS_ACTIVE;
        if ($info->enrolments == 0) {
            $state = self::STATUS_UNUSED;
        } else if ($info->modules <= 2 && $info->section_length == 0) {
            $state = self::STATUS_EMPTY;
        } else if (!$this->visible) {
            $state = self::STATUS_RESTING;
        }

        if (!$astext) {
            return $state;
        }

        switch ($state) {
            case self::STATUS_ACTIVE:
                $state = 'active';
            break;

            case self::STATUS_RESTING:
                $state = 'resting';
            break;

            case self::STATUS_EMPTY:
                $state = 'empty';
            break;

            case self::STATUS_UNUSED:
                $state = 'unused';
            break;
        }

        return $state;
    }

    /**
     * Return student count.
     */
    public function get_student_count($type = 'any') {
        $info = $this->get_fast_info();

        if ($type == 'sds') {
            return $info->sdsenrolments;
        }

        if ($type == 'manual') {
            return $info->enrolments - $info->sdsenrolments;
        }

        return $info->enrolments;
    }

    /**
     * Return activity count.
     */
    public function get_activity_count($activity = null) {
        $info = $this->get_fast_info();
        if (!empty($activity)) {
            return isset($info->activities[$activity]) ? $info->activities[$activity] : 0;
        }

        return $info->modules;
    }

    /**
     * Return distinct activity count.
     */
    public function get_distinct_activity_count() {
        $info = $this->get_fast_info();
        return $info->distinct_modules;
    }

    /**
     * Does this course have guest access enabled?
     */
    public function is_guest_enabled() {
        $info = $this->get_fast_info();
        return isset($info->guest_enabled) ? (bool)$info->guest_enabled : false;
    }

    /**
     * Does this course have guest access passworded?
     */
    public function has_guest_password() {
        $info = $this->get_fast_info();
        return isset($info->guest_password) ? (bool)$info->guest_password : false;
    }

    /**
     * Was this course manually created?
     */
    public function is_manual() {
        $info = $this->get_fast_info();
        return isset($info->ismanual) ? (bool)$info->ismanual : false;
    }

    /**
     * Return block counts.
     */
    public function get_block_count($block = null) {
        $info = $this->get_fast_info();

        if (!empty($block)) {
            return isset($info->blocks[$block]) ? $info->blocks[$block] : 0;
        }

        $total = 0;
        foreach ($info->blocks as $block => $count) {
            $total += $count;
        }

        return $total;
    }

    /**
     * Return number of assignment submissions.
     */
    public function count_assignment_submissions() {
        $info = $this->get_fast_info();
        return $info->assignsubs;
    }

    /**
     * Return number of turnitin grades.
     */
    public function count_turnitin_grades() {
        $info = $this->get_fast_info();
        $total = 0;
        foreach ($info->turnitins as $tii) {
            $total += $tii->grades;
        }

        return $total;
    }

    /**
     * Returns true if the course is grademarked.
     */
    public function is_grademark() {
        return $this->count_turnitin_grades() > 0;
    }

    /**
     * Return number of turnitin submissions.
     */
    public function count_turnitin_submissions() {
        $info = $this->get_fast_info();
        $total = 0;
        foreach ($info->turnitins as $tii) {
            $total += $tii->submissions;
        }

        return $total;
    }

    /**
     * Returns the number of inboxes that use grademark.
     */
    public function count_grademark_inboxes() {
        $info = $this->get_fast_info();
        $total = 0;
        foreach ($info->turnitins as $tii) {
            if ($tii->grades > 0) {
                $total += 1;
            }
        }

        return $total;
    }

    /**
     * Count panopto recordings.
     */
    public function count_panopto_recordings() {
        global $CFG;

        $cache = \cache::make('report_kent', 'kentreports');
        if ($content = $cache->get("course_{$this->id}_panopto")) {
            return $content;
        }

        require_once($CFG->dirroot . "/blocks/panopto/lib/panopto_data.php");

        try {
            $panoptodata = new \panopto_data($this->id);
            $livesessions = count($panoptodata->get_completed_deliveries());
        } catch (\Exception $e) {
            $livesessions = 0;
        }

        $cache->set("course_{$this->id}_panopto", $livesessions);

        return $livesessions;
    }
}
