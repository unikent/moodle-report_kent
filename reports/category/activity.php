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

$activity = optional_param('activity', null, PARAM_PLUGIN);

admin_externalpage_setup('coursemodulecountsreport', '', array('activity' => $activity), '', array(
    'pagelayout' => 'report'
));

// Display activity chooser if we have not yet chosen one.
if (!$activity) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading("Category-Based Activity Report");

    $form = new \report_kent\forms\activity_select();
    $form->display();

    echo $OUTPUT->footer();
    die;
}

$table = new \report_kent\report_table('reportkent_catact');
$table->sortable(false);
$table->define_headers(array(
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
));
$table->setup();

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading("Category-Based Activity Report");
}

// Write close here, this could take a while...
\core\session\manager::write_close();

$data = array();
$categories = \coursecat::make_categories_list();
$report = new \report_kent\reports\course\core();
foreach ($report->get_categories() as $category) {
    $catname = $categories[$category->id];
    if (!$table->is_downloading()) {
        $catname = \html_writer::tag('a', $catname, array(
            'href' => new \moodle_url('/course/index.php', array(
                'categoryid' => $category->id
            ))
        ));
    }

    $table->add_data(array(
        $catname,
        $category->count_courses(),
        $category->count_courses(null, $activity),
        $category->count_courses(\report_kent\reports\course\course::STATUS_UNUSED),
        $category->count_courses(\report_kent\reports\course\course::STATUS_UNUSED, $activity),
        $category->count_courses(\report_kent\reports\course\course::STATUS_ACTIVE),
        $category->count_courses(\report_kent\reports\course\course::STATUS_ACTIVE, $activity),
        $category->count_courses(\report_kent\reports\course\course::STATUS_RESTING),
        $category->count_courses(\report_kent\reports\course\course::STATUS_RESTING, $activity),
        $category->count_courses(\report_kent\reports\course\course::STATUS_EMPTY),
        $category->count_courses(\report_kent\reports\course\course::STATUS_EMPTY, $activity)
    ));
}

$table->finish_output();

echo $OUTPUT->footer();
