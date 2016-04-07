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

admin_externalpage_setup('reportturnitingrademark', '', null, '', array('pagelayout' => 'report'));

$table = new \report_kent\report_table('reportkent_turnitingrademark');
$table->sortable(false);
$table->define_headers(array(
    'Module Shortcode',
    'Assignment',
    'Students on course',
    'Students with submissions',
    'Students with grades'
));
$table->setup();

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading('Turnitin Grademark Report');
}

// Write close here, this could take a while...
\core\session\manager::write_close();

$assignments = \report_kent\reports\turnitin\grademark::get_assignments();
foreach ($assignments as $data) {
    $table->add_data(array(
        s($data->course_shortname),
        s($data->assignment_name),
        s($data->students_on_course),
        s($data->students_with_submissions),
        s($data->students_with_grades)
    ));
}

$table->finish_output();

echo $OUTPUT->footer();
