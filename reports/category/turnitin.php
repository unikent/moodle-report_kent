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

admin_externalpage_setup('courseturnitincountsreport', '', null, '', array(
    'pagelayout' => 'report'
));

// Create Table.
$table = new \report_kent\report_table('catbasedturnitin');
$table->sortable(false);
$table->define_headers(array(
    'Category',
    'Total',
    'Total with Turnitin',
    'Total with Grademark'
));
$table->setup();

if (!$table->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading("Category-Based Turnitin Report");
}

$categories = \coursecat::make_categories_list();

// Write close here, this could take a while...
\core\session\manager::write_close();

$report = new \report_kent\reports\course\core();
foreach ($report->get_categories() as $category) {
    $categorylink = \html_writer::tag('a', $categories[$category->id], array(
        'href' => new \moodle_url('/course/index.php', array(
            'categoryid' => $category->id
        ))
    ));

    $table->add_data(array(
        $categorylink,
        $category->count_courses(),
        $category->count_courses(null, 'turnitintooltwo'),
        \report_kent\reports\course\turnitin::count_grademark($category)
    ));
}

$table->finish_output();

echo $OUTPUT->footer();
