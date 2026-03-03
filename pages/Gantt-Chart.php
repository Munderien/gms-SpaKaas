<?php
// Libraries van de jpgraph site (github pls werk ff)
require_once (__DIR__ . '/../jpgraph/src/jpgraph.php');
require_once (__DIR__ . '/../jpgraph/src/jpgraph_gantt.php');
 
$db = new mysqli("localhost", "root", "", "dms-spakaas");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$sql = "SELECT lodgeid, starttijd, eindtijd, status 
        FROM afspraak 
        ORDER BY lodgeid, starttijd";

$result = $db->query($sql);

$graph = new GanttGraph();
$graph->SetShadow();
$graph->title->Set("Resort Lodge Planning");

$graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAME);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

$graph->SetDateRange(date("Y-01-01"), date("Y-12-31"));

$lodgeRows = [];
$currentRow = 0;

while($row = $result->fetch_assoc()) {

    $lodgeId = $row['lodgeid'];

    // Als de lodge nog geen row heeft, wordt er een row aangemaakt voor de lodge
    if(!isset($lodgeRows[$lodgeId])) {
        $lodgeRows[$lodgeId] = $currentRow;
        $currentRow++;
    }

    $rowIndex = $lodgeRows[$lodgeId];

    $lodgeName = "Lodge " . $lodgeId;

    $startDate = date("Y-m-d", strtotime($row['starttijd']));
    $endDate   = date("Y-m-d", strtotime($row['eindtijd']));

    $bar = new GanttBar($rowIndex, $lodgeName, $startDate, $endDate);

    // Op basis van status (van de afspraak), wordt de bar veranderd van kleur
    switch($row['status']) {
        case "Vrij":
            $bar->SetFillColor("green");
            break;
        case "Bezet":
            $bar->SetFillColor("red");
            break;
        case "Onderhoud":
            $bar->SetFillColor("yellow");
            break;
        case "Aan de schoonmaak":
            $bar->SetFillColor("violet");
            break;
        default:
            $bar->SetFillColor("gray");
    }

    $graph->Add($bar);
}

$graph->Stroke();

?>