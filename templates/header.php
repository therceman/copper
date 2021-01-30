<?php /** @var \Copper\Component\Templating\ViewHandler $view */ ?>

    <!doctype html>
    <html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $view->out($view->data('head_title', 'Copper PHP Framework')) ?></title>
    <meta name="description"
          content="<?= $view->out($view->data('head_meta_description', 'Copper is a PHP Framework based on Symfony')) ?>">
    <meta name="author" content="<?= $view->out($view->data('head_meta_author', 'rceman')) ?>">
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
    <style>
        .content_wrapper {
            width: 1280px;
            margin: 0 auto;
        }

        table.collection {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        table.collection td, table.collection th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table.collection tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table.collection tr:not(:first-child):hover {
            background-color: #ddd;
        }

        table.collection th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            padding-right: 12px;
            background-color: #4CAF50;
            color: white;
        }

        table.collection th:not(.empty) {
            cursor: pointer;
        }

        table.collection th.sort.asc:after {
            content: "⇣";
            position: absolute;
        }

        table.collection th.sort.desc:after {
            content: "⇡";
            position: absolute;
        }
    </style>
</head>

<?php
if ($view->auth->check())
    echo 'Welcome, ' . $view->auth->user()->login;
?>