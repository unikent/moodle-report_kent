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

namespace report_kent\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_value;
use external_single_structure;
use external_multiple_structure;
use external_function_parameters;

/**
 * Reporting services for activities.
 */
class activities extends external_api
{
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_courses_for_activity_parameters() {
        return new external_function_parameters(array(
            'moduleid' => new external_value(
                PARAM_INT,
                'The ID of the module',
                VALUE_REQUIRED
            ),
            'categoryid' => new external_value(
                PARAM_INT,
                'Optionally filter by category',
                VALUE_OPTIONAL
            )
        ));
    }

    /**
     * Grab a list of modules.
     *
     * @param $moduleid
     * @param $categoryid
     * @return array [string]
     * @throws \invalid_parameter_exception
     */
    public static function get_courses_for_activity($moduleid, $categoryid = null) {
        global $DB;

        $params = self::validate_parameters(self::get_courses_for_activity_parameters(), array(
            'moduleid' => $moduleid,
            'categoryid' => $categoryid
        ));
        $moduleid = $params['moduleid'];
        $categoryid = $params['categoryid'];

        $module = $DB->get_record('modules', array('id' => $moduleid), '*', \MUST_EXIST);

        $report = new \report_kent\reports\course\core();
        $categories = array();
        if (!empty($categoryid)) {
            $categories[] = $report->get_category($categoryid);
        } else {
            $categories = $report->get_categories();
        }

        $courses = array();
        foreach ($categories as $category) {
            foreach ($category->get_courses($module->name) as $course) {
                $courses[] = array(
                    'id' => $course->id,
                    'shortname' => $course->shortname,
                    'fullname' => $course->fullname,
                    'count' => $course->get_activity_count($module->name),
                );
            }
        }
        return $courses;
    }

    /**
     * Returns description of search() result value.
     *
     * @return external_description
     */
    public static function get_courses_for_activity_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'The module ID.'),
            'shortname' => new external_value(PARAM_TEXT, 'The module shortname.'),
            'fullname' => new external_value(PARAM_INT, 'The module fullname.'),
            'count' => new external_value(PARAM_INT, 'The activity count.')
        )));
    }
}
