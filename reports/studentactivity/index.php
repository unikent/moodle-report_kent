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

$category = optional_param('category', 0, PARAM_INT);

admin_externalpage_setup('reportstudentactivity', '', array('category' => $category), '', array('pagelayout' => 'report'));

$headers = array('Course');
foreach (\report_kent\reports\studentactivity::get_types() as $type) {
    $headers[] = get_string("satype_{$type}", 'report_kent');
}

$table = new \report_kent\report_table('reportkent_studentactivity');
$table->sortable(false);
$table->define_headers($headers);
$table->setup();

if (!$table->is_downloading()) {
    $catdropdown = $table->category_dropdown('studentactivity', $category);

    echo $OUTPUT->header();
    echo $OUTPUT->heading('Student Activity');

    echo $catdropdown;
}

// Write close here, this could take a while...
\core\session\manager::write_close();

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
