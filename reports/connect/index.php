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

admin_externalpage_setup('reportconnectcourse', '', null, '', array('pagelayout' => 'report'));

// Allow restriction by category.
$category = optional_param('category', 0, PARAM_INT);
$select = array(
    0 => "All"
);
$categories = $DB->get_records_sql('SELECT cc.id, cc.name
    FROM {connect_course} c
    INNER JOIN {course_categories} cc ON cc.id=c.category
    GROUP BY c.category
    ORDER BY cc.name DESC
');
foreach ($categories as $obj) {
    $select[$obj->id] = $obj->name;
}

$table = new \report_kent\report_table('reportkent_connectcourse');
$table->define_headers(array(
    'Module Code',
    'Module Title',
    'Module Length',
    'Campus',
    'Students',
    'Staff'
));
$table->setup();

if (!$table->is_downloading()) {
    $PAGE->requires->js_call_amd('report_kent/reports', 'init_menu_category', array('connect'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading("Connect Course Report");

    echo html_writer::select($select, 'category', $category);
}

$studentrole = $DB->get_field('connect_role', 'id', array('name' => 'sds_student'));

$sql = <<<SQL
SELECT c.id, c.mid, c.module_code, c.module_title, c.module_length, ca.name as campus,
       SUM(case when ce.roleid=:studentid then 1 else 0 end) as students,
       SUM(case when ce.roleid<>:studentid2 then 1 else 0 end) as staff
    FROM {connect_enrolments} ce
    INNER JOIN {connect_course} c on c.id=ce.courseid
    INNER JOIN {connect_campus} ca on ca.id=c.campusid
    INNER JOIN {course_categories} cc ON cc.id=c.category
SQL;

$params = array(
    'studentid' => $studentrole,
    'studentid2' => $studentrole
);

if ($category !== 0) {
    $sql .= " WHERE cc.id = :category";
    $params['category'] = $category;
}

$sql .= ' GROUP BY ce.courseid';

$sortsql = \report_kent\report_table::get_sort_for_table('reportkent_connectcourse');
if (!empty($sortsql)) {
    $sql .= " ORDER BY $sortsql";
}

$rs = $DB->get_recordset_sql($sql, $params);
foreach ($rs as $data) {
    $row = array();

    if (!empty($data->mid) && !$table->is_downloading()) {
        $url = new \moodle_url('/course/view.php', array(
            'id' => $data->mid
        ));

        $row[] = \html_writer::tag('a', $data->module_code, array(
            'href' => $url
        ));
    } else {
        $row[] = s($data->module_code);
    }

    $row[] = s($data->module_title);
    $row[] = s($data->module_length);
    $row[] = s($data->campus);

    $row[] = s($data->students);
    $row[] = s($data->staff);

    $table->add_data($row);
}

$rs->close();

$table->finish_output();

echo $OUTPUT->footer();
