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

$role = optional_param('role', 0, PARAM_INT);

admin_externalpage_setup('reportkentplayer');

$table = new \report_kent\report_table('reportkent_kentplayer');
$table->sortable(false);
$table->define_headers(array('Username', 'First name', 'Last Name', 'Role'));
$table->setup();

if (!$table->is_downloading()) {
    $PAGE->requires->js_call_amd('report_kent/reports', 'init_menu_category', array('#menurole', 'kentplayer', 'role'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading('Kent Player Report');

    // Allow restriction by role.
    echo html_writer::select(array(
        0 => "All",
        \block_panopto\eula::VERSION_ACADEMIC => "Academic",
        \block_panopto\eula::VERSION_NON_ACADEMIC => "Non-Academic"
    ), 'role', $role);
}

$wheresql = '';
$params = array();

if ($role == \block_panopto\eula::VERSION_ACADEMIC || $role == \block_panopto\eula::VERSION_NON_ACADEMIC) {
    $wheresql = '= :version';
    $params['version'] = $role;
} else {
    list($wheresql, $params) = $DB->get_in_or_equal(array(
        \block_panopto\eula::VERSION_ACADEMIC,
        \block_panopto\eula::VERSION_NON_ACADEMIC
    ), SQL_PARAMS_NAMED, 'version');
}

$sql = <<<SQL
    SELECT u.id, u.username, u.firstname, u.lastname, eula.version
    FROM {block_panopto_eula} eula
    INNER JOIN {user} u ON u.id = eula.userid
    WHERE eula.version $wheresql
    GROUP BY u.id
SQL;

$rs = $DB->get_recordset_sql($sql, $params);
foreach ($rs as $datum) {
    $user = \html_writer::tag('a', $datum->username, array(
        'href' => new \moodle_url('/user/view.php', array('id' => $datum->id)),
        'target' => '_blank'
    ));

    $table->add_data(array(
        $table->is_downloading() ? $datum->username : $user,
        $datum->firstname,
        $datum->lastname,
        $datum->version == \block_panopto\eula::VERSION_ACADEMIC ? 'Academic' : 'Non-Academic'
    ));
}

$rs->close();
$table->finish_output();

echo $OUTPUT->footer();
