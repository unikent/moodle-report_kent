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

// Page parameters.
$showpast = optional_param('showpast', 0, PARAM_BOOL);

admin_externalpage_setup('reportdeadlines', '', array('showpast' => $showpast), '', array('pagelayout' => 'report'));

$table = new \report_kent\report_table('reportkent_filesize');
$table->sortable(false);
$table->define_headers(array(
    "Type",
    "Module Shortcode",
    "Activity Name",
    "Start Date",
    "Due Date",
    "Actions",
    "Students on course"
));
$table->setup();

if (!$table->is_downloading()) {
    $PAGE->requires->js_call_amd('report_kent/reports', 'init_manual_toggle', array('#showpastchk', '/report/kent/reports/deadlines/index.php?showpast='));

    echo $OUTPUT->header();
    echo $OUTPUT->heading('Deadlines Report');

    echo \html_writer::checkbox('showpast', true, $showpast, 'Show Past Deadlines?', array(
        'id' => 'showpastchk'
    ));
}

// Write close here, this could take a while...
\core\session\manager::write_close();

$deadlines = \report_kent\reports\deadlines\report::get_deadlines($showpast);
foreach ($deadlines as $data) {
    $table->add_data(array(
        s($data->type),
        s($data->course),
        s($data->name),
        date("Y-m-d H:i", $data->start),
        date("Y-m-d H:i", $data->end),
        s($data->activity),
        s($data->enrolled_students)
    ));
}

$table->finish_output();

echo $OUTPUT->footer();
