<?php /** @var \Copper\Component\Templating\ViewHandler $view */ ?>

<?= $view->render('header') ?>

<body class="markdown-body">
<h4>Welcome to Copper <b>PHP Framework</b>!</h4>
<div><u>Notice</u>: You are using preconfigured route <code><?= $view->out($view->route_name) ?></code>
    with default template <code><?= $view->out($view->route_name) ?>.php</code></div>
<div>Please create <code>config/routes.php</code> file and configure your application routes</div>
<div>See configuration information on <a href="https://github.com/rceman/copper#configuration-getting-started">GitHub</a> page</div>
</body>
