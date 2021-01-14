<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController; ?>

<?= $view->render('header') ?>

<body class="markdown-body">
<h4>Welcome to Copper <b>Control Panel</b>!</h4>
<form method="post" action="<?=$view->path(ROUTE_post_copper_cp, ['action' => CPController::ACTION_LOGOUT])?>">
    <button type="submit">Logout</button>
</form>
</body>
