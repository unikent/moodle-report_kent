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

admin_externalpage_setup('reportfilesize', '', array('category' => $category), '', array('pagelayout' => 'report'));

$table = new \report_kent\report_table('reportkent_filesize');
$table->sortable(false);
$table->define_headers(array("Course", "File count", "Total file size"));
$table->setup();

if (!$table->is_downloading()) {
    $catdropdown = $table->category_dropdown('filesize', $category);

    echo $OUTPUT->header();
    echo $OUTPUT->heading('Filesize Report');

    echo $catdropdown;
}

$results = \report_kent\reports\filesize::get_result_set($category);
foreach ($results as $k => $item) {
    $course = \html_writer::tag('a', $item['shortname'], array(
        'href' => new \moodle_url('/course/view.php', array('id' => $item['cid'])),
        'target' => '_blank'
    ));

    $table->add_data(array(
        $table->is_downloading() ? $item['shortname'] : $course,
        $item["count"],
        \report_kent\reports\filesize::pretty_filesize($item["size"])
    ));
}

$table->finish_output();

echo $OUTPUT->footer();
