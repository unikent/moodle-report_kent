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

require(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('coursemodulecountsreport', '', null, '', array(
    'pagelayout' => 'report'
));

$activity = required_param('activity', PARAM_PLUGIN);

$data = array();
$report = new \report_kent\reports\course\core();
foreach ($report->get_categories() as $category) {
    $data[] = (object)array(
        'categoryid' => $category->id,
        'name' => $category->name,
        'path' => $category->path,
        'total' => $category->count_courses(),
        'total_activity_count' => $category->count_courses(null, $activity),
        'ceased' => $category->count_courses(\report_kent\reports\course\course::STATUS_UNUSED),
        'ceased_activity_count' => $category->count_courses(\report_kent\reports\course\course::STATUS_UNUSED, $activity),
        'active' => $category->count_courses(\report_kent\reports\course\course::STATUS_ACTIVE),
        'active_activity_count' => $category->count_courses(\report_kent\reports\course\course::STATUS_ACTIVE, $activity),
        'resting' => $category->count_courses(\report_kent\reports\course\course::STATUS_RESTING),
        'resting_activity_count' => $category->count_courses(\report_kent\reports\course\course::STATUS_RESTING, $activity),
        'inactive' => $category->count_courses(\report_kent\reports\course\course::STATUS_EMPTY),
        'inactive_activity_count' => $category->count_courses(\report_kent\reports\course\course::STATUS_EMPTY, $activity)
    );
}

// Run CSV.
$headings = array(
    'Category',
    'Total Modules',
    'Total Modules with activity',
    'Unused Modules',
    'Unused Modules with activity',
    'Active Modules',
    'Active Modules with activity',
    'Resting Modules',
    'Resting Modules with activity',
    'Empty Modules',
    'Empty Modules with activity'
);

$countcolumns = array(
    'total' => '',
    'total_activity_count' => '',
    'ceased' => '',
    'ceased_activity_count' => \report_kent\reports\course\course::STATUS_UNUSED,
    'active' => '',
    'active_activity_count' => \report_kent\reports\course\course::STATUS_ACTIVE,
    'resting' => '',
    'resting_activity_count' => \report_kent\reports\course\course::STATUS_RESTING,
    'inactive' => '',
    'inactive_activity_count' => \report_kent\reports\course\course::STATUS_EMPTY
);

$table = new \html_table();
$table->head = $headings;
$table->attributes['class'] = 'admintable generaltable';
$table->data = array();
foreach ($data as $row) {
    $category = str_pad($row->name, substr_count($row->path, 1), '-');
    $category = \html_writer::tag('a', $category, array(
        'href' => new \moodle_url('/course/index.php', array(
            'categoryid' => $row->categoryid
        ))
    ));

    $columns = array(
        new html_table_cell($category)
    );

    foreach ($countcolumns as $column => $status) {
        $cell = new html_table_cell($row->$column);
        $cell->attributes['class'] = "datacell " . $column;
        if ($status) {
            $cell->attributes['column'] = $status;
        }
        $cell->attributes['catid'] = $row->categoryid;
        $columns[] = $cell;
    }

    $obj = new html_table_row($columns);
    $obj->attributes['class'] = 'datarow';
    $table->data[] = $obj;
}

echo $OUTPUT->header();
echo $OUTPUT->heading("Category-Based Activity Report");

echo \html_writer::table($table);
echo \html_writer::empty_tag('hr');

echo $OUTPUT->footer();
