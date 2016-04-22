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
 * Report core.
 */
class core
{
    use \local_kent\traits\singleton;

    /**
     * Returns a list of categories.
     */
    public function get_categories() {
        global $DB;

        $categories = $DB->get_recordset('course_categories');
        foreach ($categories as $category) {
            yield new category($category);
        }
        $categories->close();
    }

    /**
     * Returns a category.
     */
    public function get_category($id) {
        global $DB;

        return new category($DB->get_record('course_categories', array(
            'id' => $id
        )));
    }
}
