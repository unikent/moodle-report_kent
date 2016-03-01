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
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('reportstudentactivity', '', null, '', array('pagelayout' => 'report'));

$category = optional_param('category', 0, PARAM_INT);

$headers = array('Course');
foreach (\report_kent\reports\studentactivity::get_types() as $type) {
    $headers[] = get_string("satype_{$type}", 'report_kent');
}

$table = new \report_kent\report_table('reportkent_studentactivity');
$table->sortable(false);
$table->define_headers($headers);
$table->setup();

if (!$table->is_downloading()) {
    $PAGE->requires->js_call_amd('report_kent/reports', 'init_menu_category', array('#menucategory', 'studentactivity', 'category'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading('Student Activity');

    // Allow restriction by category.
    $select = array(
        0 => "All"
    );
    $categories = $DB->get_records('course_categories', null, 'name', 'id,name');
    foreach ($categories as $obj) {
        $select[$obj->id] = $obj->name;
    }
    echo html_writer::select($select, 'category', $category);
}

$core = new \report_kent\reports\studentactivity();
if ($category !== 0) {
    $core->set_category($category);
}

// Display the data.
$courses = $core->get_courses();
foreach ($courses as $course) {
    $data = array(\html_writer::tag('a', $course->shortname, array(
        'href' => new \moodle_url('/course/view.php', array('id' => $course->id)),
        'target' => '_blank'
    )));

    foreach (\report_kent\reports\studentactivity::get_types() as $type) {
        $var = "{$type}_count";
        $data[] = $course->$var;
    }

    $table->add_data($data);
}

$table->finish_output();

echo $OUTPUT->footer();
