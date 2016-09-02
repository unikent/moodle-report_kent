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

admin_externalpage_setup('readinglistcoursereport', '', null, '', array(
    'pagelayout' => 'report'
));

$action = optional_param('action', '', PARAM_ALPHA);
if ($action == 'refresh') {
    $courseid = required_param('courseid', PARAM_INT);
    $course = $DB->get_record('course', array('id' => $courseid));
    report_kent\reports\readinglists::rebuild_course($course);
    \core\notification::success('Refreshed course');
}

// Create Table.
$table = new \report_kent\report_table('kentreadinglistreport');
$table->sortable(false);
$table->define_headers(array(
    'Course',
    'Current year',
    'Last year',
    'Action'
));
$table->setup();

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading("Reading lists Report");
}

$tick = \html_writer::tag('i', '', array('class' => 'fa fa-check'));
$cross = \html_writer::tag('i', '', array('class' => 'fa fa-times'));

$coursedata = report_kent\reports\readinglists::get_data();
foreach ($coursedata as $id => $course) {
    $course = (object)$course;

    $courseurl = new \moodle_url('/course/view.php', array(
        'id' => $id
    ));
    $coursecell = \html_writer::link($courseurl, "{$course->shortname}: {$course->fullname}");

    $actionurl = new \moodle_url('/report/kent/reports/courses/readinglists.php', array(
        'courseid' => $id,
        'action' => 'refresh'
    ));
    $action = $OUTPUT->action_link($actionurl, 'Refresh');

    $table->add_data(array($coursecell, $course->currentlist ? $tick : $cross, $course->pastlist ? $tick : $cross, $action));
}

$table->finish_output();

echo $OUTPUT->footer();
