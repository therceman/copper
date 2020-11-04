<?php /** @var \Copper\Component\Templating\ViewHandler $view */ ?>

<?= $view->render('header') ?>

<body class="markdown-body">
<h4>You are not allowed to see this page!</h4>
<a href="<?=$view->path(ROUTE_index)?>">Home</a>
</body>
