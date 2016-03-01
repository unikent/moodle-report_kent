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
 * @copyright  2016 Jake Blatchford
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('reportdeadlines', '', null, '', array('pagelayout' => 'report'));

$deadlines = \report_kent\reports\deadlines\report::get_deadlines(false);

// Grab 200 submissions.
$graphdata = array_slice($deadlines, 0, 200);

$timesummary = array();
foreach ($graphdata as $data) {
    // Round date to hour.
    $time = $data->end - ($data->end % 3600);

    if (!isset($timesummary[$time])) {
        $timesummary[$time] = array(
            "activity" => $data->activity,
            "enrolled_students" => $data->enrolled_students
        );
    } else {
        $timesummary[$time]["activity"] += $data->activity;
        $timesummary[$time]["enrolled_students"] += $data->enrolled_students;
    }
}

$graphchartdata = array();
foreach ($timesummary as $time => $g) {
    $timestring = "Date(" . date("Y", $time) . "," . (date("m", $time) - 1) . "," . date("d,G,i", $time) . ")";
    // Because graph is stacked subtract activity from enrolled_students.
    $graphchartdata[] = array($timestring, $g["activity"], $g["enrolled_students"] - $g["activity"]);
}

// Add columns onto array.
$columns = array(
    array("type" => "datetime", "label" => "Date"),
    array("type" => "number", "label" => "Actions"),
    array("type" => "number", "label" => "Students without actions")
);
array_unshift($graphchartdata, $columns);
$graphjson = json_encode($graphchartdata);

echo $OUTPUT->header();
echo $OUTPUT->heading("Deadlines graph");

echo "<script>deadline_report_data = {$graphjson}</script>";
echo <<<CHARTJS
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load('current', {'packages': ['corechart']});
  google.charts.setOnLoadCallback(drawChart);
  function drawChart() {
    var data = google.visualization.arrayToDataTable(deadline_report_data);

    var options = {
      title: 'Deadline Report',
      width: '900px',
      legend: {position: 'bottom'},
      hAxis: {title: 'Date', format: 'EEE d HH:mm'},
      isStacked: 'true'
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  }
</script>
CHARTJS;
echo '<div id="chart_div" style="width: 900px; height: 500px;"></div>';

echo $OUTPUT->footer();
