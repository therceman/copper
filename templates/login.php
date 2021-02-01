<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\FlashMessage\FlashMessage; ?>

<?= $view->render('header') ?>

<?php if ($view->flashMessage->hasError()) { ?>
    <div style="border: 1px solid #ccc; padding: 10px; margin:5px; border-radius: 5px;">
        <span>Error:</span>
        <code><?= $view->out($view->flashMessage->getError()) ?></code>
    </div>
<?php } ?>

<div class="content_wrapper" style="padding: 0 20px">
    <h3>Login</h3>

    <form method=POST action="<?= $view->url($view->auth->config->loginRoute) ?>">
        <input type=text name=login placeholder="Enter Login" size=30
               value="<?= $view->out($view->flashMessage->get('form_login')) ?>">
        <input type=password name=password placeholder="Enter password" size=30>
        <button type=submit>Login</button>
    </form>
</div>