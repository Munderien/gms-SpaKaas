<?php
// Libraries van de jpgraph site (github pls werk ff)
require_once (__DIR__ . '/../jpgraph/src/jpgraph.php');
require_once (__DIR__ . '/../jpgraph/src/jpgraph_gantt.php');
 
$db = new mysqli("localhost", "root", "", "dms-spakaas");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$sql = "
    SELECT
        a.lodgeid,
        a.starttijd,
        a.eindtijd,
        'afspraak' AS afspraak_type
    FROM afspraak a

    UNION ALL

    SELECT
        s.lodgeid,
        s.datum AS starttijd,
        DATE_ADD(s.datum, INTERVAL CEIL(lt.capaciteit * 7.5) MINUTE) AS eindtijd,
        'schoonmaak' AS afspraak_type
    FROM schoonmaak s
    INNER JOIN lodge l ON l.lodgeid = s.lodgeid
    LEFT JOIN lodgetype lt ON lt.lodgetypeid = l.typeid

    ORDER BY lodgeid, starttijd
";

$result = $db->query($sql);

$graph = new GanttGraph(3000, 150, 100, 100, true);
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

    if(!isset($lodgeRows[$lodgeId])) {
        $lodgeRows[$lodgeId] = $currentRow;
        $currentRow++;
    }

    $rowIndex = $lodgeRows[$lodgeId];

    $lodgeName = "Lodge " . $lodgeId;

    $startDate = date("Y-m-d", strtotime($row['starttijd']));
    $endDate   = date("Y-m-d", strtotime($row['eindtijd']));

    $bar = new GanttBar($rowIndex, $lodgeName, $startDate, $endDate);

    // Op basis van type afspraak wordt de bar kleur bepaald 
    // BELANGRIJK: onderhoud werkt niet omdat er geen datum is gekoppeld bij de onderhoud table, zit erin voor scaling 
    switch($row['afspraak_type']) {
        case "afspraak":
            $bar->SetFillColor("green");
            break;
        case "onderhoud":
            $bar->SetFillColor("red");
            break;
        case "schoonmaak":
            $bar->SetFillColor("yellow");
            break;
        default:
            $bar->SetFillColor("gray");
    }

    $graph->Add($bar);
}

$graph->Stroke();

?>