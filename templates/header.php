<?php global $view;

use Copper\Component\HTML\HTML;

$title = $view->dataBag->get('title', $view->appConfig->title);
$description = $view->dataBag->get('description', $view->appConfig->description);
$author = $view->dataBag->get('author', $view->appConfig->author);

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $view->out($title) ?></title>
    <?= HTML::meta('description', $description) . PHP_EOL ?>
    <?= HTML::meta('author', $author) . PHP_EOL ?>
    <style>
        body {
            background-color: #fff;
            color: #24292e;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Helvetica, Arial, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol;
            font-size: 16px;
            word-wrap: break-word;
        }

        code {
            font-family: SFMono-Regular, Consolas, Liberation Mono, Menlo, Courier, monospace;
            background-color: rgba(27, 31, 35, .05);
            border-radius: 3px;
            font-size: 85%;
            margin: 0;
            padding: .2em .4em
        }

        form {
            margin: 0;
        }
    </style>
</head>