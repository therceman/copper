<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Symfony\Component\HttpFoundation\Response;

$method = $view->dataBag->get('$method');
$url = $view->dataBag->get('$url');
$status = $view->dataBag->get('$status', Response::HTTP_INTERNAL_SERVER_ERROR);
$protocol_ver = $view->dataBag->get('$protocol_ver', 'HTTP/1.1');
$type = $view->dataBag->get('$type');
$msg = $view->dataBag->get('$msg');
$file = $view->dataBag->get('$file', null);
$line = $view->dataBag->get('$line', null);
$code = $view->dataBag->get('$code', null);
$func = $view->dataBag->get('$func', null);
$args = $view->dataBag->get('$args', null);
$trace_string = $view->dataBag->get('$trace_string', null);
$ips = $view->dataBag->get('$ips', null);
$session_id = $view->dataBag->get('$session_id', null);
$referer = $view->dataBag->get('$referer', null);

$request = "\"{$method} {$url} {$protocol_ver}\" {$status}";

print "<div style='text-align: center;'>";
print "<h2 style='color: rgb(190, 50, 50);'>Error Occurred</h2>";
print "<table style='width: 1000px; display: inline-block;'>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Request</th><td>{$request}</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>{$type}</td></tr>";
print "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td style='width: 920px'>{$msg}</td></tr>";

if ($file !== null)
    print "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$file}</td></tr>";
if ($line !== null)
    print "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$line}</td></tr>";
if ($code !== null)
    print "<tr style='background-color:rgb(240,240,240);'><th>Code</th><td>{$code}</td></tr>";
if ($func !== null)
    print "<tr style='background-color:rgb(230,230,230);'><th>Function</th><td>{$func}</td></tr>";
if ($args !== null)
    print "<tr style='background-color:rgb(230,230,230);'><th>Args</th><td>{$args}</td></tr>";

print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Ips</th><td>$ips</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Session ID</th><td>$session_id</td></tr>";
print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Referer</th><td>$referer</td></tr>";
print "</table></div>";