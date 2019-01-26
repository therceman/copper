<?php /** @var \Copper\Component\Templating\ViewHandler $view */ ?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $view->out($view->data('head_title', 'Copper PHP Framework')) ?></title>
    <meta name="description" content="<?= $view->out($view->data('head_meta_description', 'Copper is a PHP Framework based on Symfony')) ?>">
    <meta name="author" content="<?= $view->out($view->data('head_meta_author', 'rceman')) ?>">
    <style>
        body {
            background-color: #fff;
            color: #24292e;
            font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Helvetica, Arial, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol;
            font-size: 16px;
            line-height: 1.5;
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
    </style>
</head>
