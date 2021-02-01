<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController;

?>

<?= $view->render('header') ?>

<body class="markdown-body">
<h4>Authorization for <b>Control Panel</b></h4>

<?php if ($view->flashMessage->hasError()) : ?>
    <div style="color:red">Error: <?= $view->flashMessage->getError() ?></div>
<?php endif; ?>

<form method="post" action="<?= $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_AUTHORIZE]) ?>">
    <input type="password" name="password">
    <button type="submit">Login</button>
</form>
</body>
