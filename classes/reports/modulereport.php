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
 * Reporting class
 */
class modulereport
{
    /**
     * Grab a list of module counts by categories.
     *
     * @global $DB
     * @return array array (array("category",  "modules" => array("module" => "count", ...)), ...)
     */
    public function get_modules_by_category() {
        global $DB;

        list($wheres, $params) = $this->get_exclusions_sql();
        if (!empty($wheres)) {
            $wheres = 'WHERE ' . $wheres;
        } else {
            $wheres = '';
        }

        $sql = <<<SQL
            SELECT cm.id, c.id cid, cm.module, COUNT(cm.instance) mcount, cc.path catpath
                FROM {course_modules} cm
            JOIN {course} c
                ON cm.course = c.id
            JOIN {course_categories} cc
                ON c.category = cc.id
            $wheres
            GROUP BY cm.module, cc.id, c.id
SQL;
        $records = $DB->get_records_sql($sql, $params);

        // Stores an array of mappings for category ID -> category name.
        $categories = $this->get_categories();

        // Stores an array of mappings for module ID -> module name.
        $modules = $this->get_modules();

        // Placeholder set of 0 counts.
        $modulecounts = array_map(function($a) {
            return 0;
        }, $modules);

        // Go through every category, setup the data array for it.
        $data = array();
        foreach ($categories as $catid => $catname) {
            $data[$catid] = array(
                "category" => $catname,
                "modules" => $modulecounts
            );
        }

        // Update all the counts.
        foreach ($records as $record) {
            // Grab a list of categories to update.
            $path = $record->catpath;
            $paths = explode('/', $path);
            $categories = array_filter($paths, "strlen");

            foreach ($categories as $catid) {
                // This totals the number of courses using the module, rather than the total
                // number of instances (+= mcount)
                // CR996.
                $data[$catid]["modules"][$record->module]++;
            }
        }

        return $data;
    }

    /**
     * Returns a list of Module instances within a category
     *
     * @global $DB
     */
    public function get_instances_for_category($catid, $moduleid) {
        global $DB;

        list($wheres, $params) = $this->get_exclusions_sql();
        $params["cpath1"] = "%/" . $catid . "/%";
        $params["cpath2"] = "%/" . $catid;
        $params["mid"] = $moduleid;

        $wheresql = '(cc.path LIKE :cpath1 OR cc.path LIKE :cpath2) AND cm.module = :mid ';
        if (!empty($wheres)) {
            $wheresql .= "AND $wheres";
        }

        $sql = <<<SQL
            SELECT cm.id, c.id as cid, cm.module, cm.instance, c.shortname, COUNT(cm.module) mcount
                FROM {course_modules} cm
            JOIN {course} c
                ON cm.course = c.id
            JOIN {course_categories} cc
                ON c.category = cc.id
            WHERE $wheresql
            GROUP BY cm.module, c.id
SQL;

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns a list of category ids and category names.
     */
    public function get_categories() {
        global $DB;

        $records = $DB->get_records("course_categories", null, '', 'id, name');

        $data = array();
        foreach ($records as $record) {
            $data[$record->id] = $record->name;
        }

        return $data;
    }

    /**
     * Returns a list of modules.
     */
    public function get_modules() {
        global $DB;

        $records = $DB->get_records("modules", null, '', 'id, name');

        $data = array();
        foreach ($records as $record) {
            $data[$record->id] = $record->name;
        }

        return $data;
    }

    /**
     * Returns a list of modules we should exclude
     */
    private function get_exclusions_list() {
        global $DB;

        // Grab the ID of the forum module.
        $forum = $DB->get_field('modules', 'id', array(
            'name' => 'forum'
        ));

        // Grab the ID of the aspire lists module.
        $lists = $DB->get_field('modules', 'id', array(
            'name' => 'aspirelists'
        ));

        return array(
            $forum => $this->filter_list("forum", "News forum"),
            $lists => $this->filter_list("aspirelists", "Reading list")
        );
    }

    /**
     * Returns exclusion SQL and params.
     */
    private function get_exclusions_sql() {
        global $DB;

        // Grab a list of IDs to filter out.
        $exclusions = $this->get_exclusions_list();

        $params = array();
        $wheres = array();
        foreach ($exclusions as $module => $ids) {
            if (empty($ids)) {
                continue;
            }

            list($isql, $iparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'param', false);
            $wheres[] = 'cm.instance ' . $isql;
            $params = array_merge($params, $iparams);
        }
        $wheres = implode(' AND ', $wheres);

        return array($wheres, $params);
    }

    /**
     * Filter out default modules, this grabs a list of modules that need to be excluded.
     *
     * @return boolean True if this is a default forum, else false.
     */
    private function filter_list($tablename, $moduletitle) {
        global $DB;

        // Grab a list of IDs we can exclude.
        $records = $DB->get_records_sql("SELECT id FROM {".$tablename."} WHERE name = :title", array(
            'title' => $moduletitle
        ));

        // Map to an array.
        $ids = array();
        foreach ($records as $record) {
            $ids[] = $record->id;
        }

        return $ids;
    }
}
