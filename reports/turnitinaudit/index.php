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

admin_externalpage_setup('reportturnitinaudit', '', null, '', array('pagelayout' => 'report'));

$table = new \report_kent\report_table('reportkent_turnitinaudit');
$table->define_headers(array(
    'Category',
    'Module',
    'Assignment',
    'Parts',
    'Anonymous marking',
    'Allow late submission',
    'Report generation speed',
    'Store student papers',
    'Student originality report',
    'Access restricted'
));
$table->setup();

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading('Turnitin Audit Report');
}

// Write close here, this could take a while...
\core\session\manager::write_close();

$orderby = \report_kent\report_table::get_sort_for_table('reportkent_turnitinaudit');
$result = \report_kent\reports\turnitin\audit::get_assignments($orderby);
foreach ($result as $data) {
    $table->add_data(array(
        s($data->category),
        s($data->module),
        s($data->assignment),
        s($data->parts),
        s($data->anonymous_marking),
        s($data->allow_late_submission),
        s($data->report_generation_speed),
        s($data->store_student_papers),
        s($data->student_originality_report),
        s($data->access_restricted)
    ));
}

$table->finish_output();

echo $OUTPUT->footer();
