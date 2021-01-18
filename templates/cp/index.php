<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController;

$migrate_url = $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_MIGRATE]);
$seed_url = $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_SEED]);
?>

<?= $view->render('header') ?>

<body class="markdown-body">
<h4>Welcome to Copper <b>Control Panel</b>!</h4>

<ul>
    <li>
        <a target="_blank" href="<?= $migrate_url ?>">Migrate</a>
    </li>
    <li>
        <a target="_blank" href="<?= $seed_url ?>">Seed</a>
    </li>
</ul>

<form method="post" action="<?= $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_LOGOUT]) ?>">
    <button type="submit">Logout</button>
</form>
</body>
