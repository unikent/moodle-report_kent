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

admin_externalpage_setup('reportactivitiesreport', '', null, '', array('pagelayout' => 'report'));

$records = $DB->get_records('modules', array('visible' => 1), '', 'id, name');
$headers = array('category' => 'Category');
foreach ($records as $module) {
    $headers[$module->name] = get_string('modulename', 'mod_' . $module->name);
}

$table = new \report_kent\report_table('reportkent_activities');
$table->sortable(false);
$table->define_headers(array_values($headers));
$table->define_columns(array_keys($headers));

$i = 0;
foreach ($records as $module) {
    $i++;
    $table->column_class($module->name, "moduleid_{$module->id}");
}
$table->setup();

if (!$table->is_downloading()) {
    $PAGE->requires->js_call_amd('report_kent/reports', 'init_ws', array(
        '.cell',
        'report_kent_get_courses_for_activity',
        'categoryid',
        'moduleid',
        array('Shortname', 'Fullname', 'Count')
    ));

    echo $OUTPUT->header();
    echo $OUTPUT->heading('Category-Based Activity Usage');
}

// Write close here, this could take a while...
\core\session\manager::write_close();

$categories = \coursecat::make_categories_list();
$report = new \report_kent\reports\course\core();
foreach ($report->get_categories() as $category) {
    $link = \html_writer::link(new \moodle_url('/report/kent/reports/activities/category.php', array(
        'category' => $category->id
    )), $categories[$category->id]);

    $row = array($link);
    foreach ($records as $module) {
        $row[] = $category->count_activity($module->name);
    }

    $table->add_data($row, "categoryid_{$category->id}");
}

$table->finish_output();

echo $OUTPUT->footer();
