<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController;

?>

<?= $view->render('header') ?>

<body class="markdown-body">
<h4>Authorization for <b>Control Panel</b></h4>

<?php if ($view->flashMessage->exists('error')) : ?>
    <div style="color:red">Error: <?= $view->flashMessage->get('error') ?></div>
<?php endif; ?>

<form method="post" action="<?= $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_AUTHORIZE]) ?>">
    <input type="password" name="password">
    <button type="submit">Login</button>
</form>
</body>
