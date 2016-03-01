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

require_once("{$CFG->libdir}/externallib.php");

use external_api;
use external_value;
use external_single_structure;
use external_multiple_structure;
use external_function_parameters;

/**
 * Kent's corner external services.
 */
class modulereport extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_course_info_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Returns course information.
     *
     * @return array[string]
     */
    public static function get_course_info() {
        $obj = new \report_kent\reports\modulereport();
        $categories = $obj->get_modules_by_category();

        $modnames = array();
        $dbmodules = $obj->get_modules();
        foreach ($dbmodules as $id => $module) {
            $modnames[$id] = get_string('modulename', 'mod_' . $module);
        }

        // Populate table.
        $data = array();
        foreach ($categories as $cid => $catdata) {
            $category = $catdata['category'];
            $modules = $catdata['modules'];

            foreach ($modules as $mid => $count) {
                $data[] = array(
                    'categoryid' => $cid,
                    'category' => $category,
                    'activityid' => $mid,
                    'activity' => $modnames[$mid],
                    'count' => $count
                );
            }
        }

        return $data;
    }

    /**
     * Returns description of get_course_info() result value.
     *
     * @return external_multiple_structure
     */
    public static function get_course_info_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'categoryid' => new external_value(PARAM_INT, 'ID of the category.'),
                    'category' => new external_value(PARAM_TEXT, 'Name of the category.'),
                    'activityid' => new external_value(PARAM_INT, 'ID of the activity.'),
                    'activity' => new external_value(PARAM_TEXT, 'Name of the activity.'),
                    'count' => new external_value(PARAM_INT, 'Count of the activity.')
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_activity_info_parameters() {
        return new external_function_parameters(array(
            'category' => new external_value(
                PARAM_INT,
                'Category ID',
                VALUE_REQUIRED
            ),
            'activity' => new external_value(
                PARAM_INT,
                'Activity ID',
                VALUE_REQUIRED
            )
        ));
    }

    /**
     * Returns the current user's preferences.
     *
     * @param $category
     * @param $activity
     * @return array[string]
     */
    public static function get_activity_info($category, $activity) {
        global $DB;

        // Validate the username.
        $params = self::validate_parameters(self::get_activity_info_parameters(), array(
            'category' => $category,
            'activity' => $activity
        ));
        $category = $params['category'];
        $activity = $params['activity'];

        $module = $DB->get_record('modules', array(
            'id' => $activity
        ));

        $data = array();
        $obj = new \report_kent\reports\modulereport();
        $list = $obj->get_instances_for_category($category, $activity);
        foreach ($list as $item) {
            $row = array(
                $item->shortname,
                $item->mcount
            );

            if ($module->name == "forum") {
                $row['extra'] = 'Post Count';
                $row['extraval'] = $obj->forum_count($item->cid);
            }

            if ($module->name == "ouwiki") {
                $row['extra'] = 'Edit Count';
                $row['extraval'] = $obj->ouwiki_count($item->cid);
            }

            if ($module->name == "assignment") {
                $row['extra'] = 'Assignment Submissions';
                $row['extraval'] = $obj->assignment_count($item->cid);
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Returns description of get_activity_info() result value.
     *
     * @return external_multiple_structure
     */
    public static function get_activity_info_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'shortname' => new external_value(PARAM_TEXT, 'Name of the module'),
                    'count' => new external_value(PARAM_INT, 'Number of activities'),
                    'extra' => new external_value(PARAM_TEXT, 'Title of extra data'),
                    'extraval' => new external_value(PARAM_INT, 'Extra data value')
                )
            )
        );
    }
}
