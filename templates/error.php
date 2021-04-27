<?php /** @var \Copper\Component\Templating\ViewHandler $view */

$method = $view->dataBag->get('$method');
$url = $view->dataBag->get('$url');
$type = $view->dataBag->get('$type');
$msg = $view->dataBag->get('$msg');
$file = $view->dataBag->get('$file');
$line = $view->dataBag->get('$line');
$code = $view->dataBag->get('$code');
$func = $view->dataBag->get('$func');
$args = $view->dataBag->get('$args');
$trace_string = $view->dataBag->get('$trace_string');
$ips = $view->dataBag->get('$ips');
$user_id = $view->dataBag->get('$user_id');
$referer = $view->dataBag->get('$referer');

print "<div style='text-align: center;'>";
print "<h2 style='color: rgb(190, 50, 50);'>Error Occurred</h2>";
print "<table style='width: 800px; display: inline-block;'>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Request</th><td>{$method} {$url}</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>{$type}</td></tr>";
print "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$msg}</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$file}</td></tr>";
print "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$line}</td></tr>";
print "<tr style='background-color:rgb(240,240,240);'><th>Code</th><td>{$code}</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th>Function</th><td>{$func}</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th>Args</th><td>{$args}</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Ips</th><td>$ips</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>User ID</th><td>$user_id</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Referer</th><td>$referer</td></tr>";
print "</table></div>";