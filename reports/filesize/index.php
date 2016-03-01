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

admin_externalpage_setup('reportfilesize', '', null, '', array('pagelayout' => 'report'));

$category = optional_param('category', 0, PARAM_INT);

$table = new \report_kent\report_table('reportkent_studentactivity');
$table->sortable(false);
$table->define_headers(array("Course", "File count", "Total file size"));
$table->setup();

if (!$table->is_downloading()) {
    $PAGE->requires->js_call_amd('report_kent/reports', 'init_menu_category', array('#menucategory', 'filesize', 'category'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading('Filesize Report');

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

$results = \report_kent\reports\filesize::get_result_set($category);
foreach ($results as $k => $item) {
    $course = \html_writer::tag('a', $item['shortname'], array(
        'href' => new \moodle_url('/course/view.php', array('id' => $item['cid'])),
        'target' => '_blank'
    ));

    $table->add_data(array($course, $item["count"], \report_kent\reports\filesize::pretty_filesize($item["size"])));
}

$table->finish_output();

echo $OUTPUT->footer();
