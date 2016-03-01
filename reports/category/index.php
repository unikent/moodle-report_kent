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

admin_externalpage_setup('coursecatcountsreport', '', null, '', array(
    'pagelayout' => 'report'
));

$PAGE->set_url(new \moodle_url('/report/coursecatcounts/beta.php'));
$PAGE->set_context(\context_system::instance());

$category = optional_param('category', false, PARAM_INT);
$format = optional_param('format', 'screen', PARAM_ALPHA);
if ($format != 'csv') {
    $format = 'screen';
}

// Create Table.
$table = new \report_kent\report_table('coursecatcountsreport');
$table->sortable(false);
$table->define_headers(array(
    'Category',
    'Total',
    'Unused',
    'Active',
    'Resting',
    'Empty',
    'Guest Enabled',
    'Guest Passworded'
));
$table->setup();

if (!$table->is_downloading()) {
    $PAGE->requires->js_init_call('M.report_categories.init', array(), false, array(
        'name' => 'report_coursecatcounts',
        'fullpath' => '/report/coursecatcounts/scripts/categories.js'
    ));

    echo $OUTPUT->header();
    echo $OUTPUT->heading("Category-Based Course Report");
}

$report = new \report_kent\reports\course\core();
foreach ($report->get_categories() as $category) {
    $link = \html_writer::link(new \moodle_url('/report/coursecatcounts/beta.php', array(
        'category' => $category->id
    )), $category->name);

    $table->add_data(array(
        $link,
        $category->count_courses(),
        $category->count_courses(\report_coursecatcounts\course::STATUS_UNUSED),
        $category->count_courses(\report_coursecatcounts\course::STATUS_ACTIVE),
        $category->count_courses(\report_coursecatcounts\course::STATUS_RESTING),
        $category->count_courses(\report_coursecatcounts\course::STATUS_EMPTY),
        $category->count_guest(),
        $category->count_guest_passwords()
    ), $category->id);
}

$table->finish_output();

// Output to screen.
if (!$table->is_downloading()) {
    // Show a back link for category view.
    if ($category) {
        echo \html_writer::tag('a', 'Back', array(
            'href' => new \moodle_url('/report/coursecatcounts/beta.php')
        ));
    } else {
        echo\html_writer::empty_tag('hr');
    }
}

echo $OUTPUT->footer();
