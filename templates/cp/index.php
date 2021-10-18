<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController;

$db_test_url = $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_TEST]);
$validator_test_url = $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_VALIDATOR_TEST]);
$core_test_url = $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_CORE_TEST]);
$migrate_url = $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_MIGRATE]);
$generator_url = $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GENERATOR]);
$seed_url = $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_SEED]);
$entity_list = $view->dataBag->get('entity_list');
?>

<?= $view->render('cp/header') ?>

<body class="markdown-body">
<h4>Welcome to Copper <b>Control Panel</b>!</h4>

<ul>
    <li>
        <a target="_blank" href="<?= $db_test_url ?>">DB Test</a><span></span>
    </li>
    <li>
        <a target="_blank" href="<?= $validator_test_url ?>">Validator Test</a><span></span>
    </li>
    <li>
        <a target="_blank" href="<?= $core_test_url ?>">Core Test</a><span></span>
    </li>
<!--    <li>-->
<!--        <a target="_blank" href="--><?//= $migrate_url ?><!--">DB Migrate</a>-->
<!--    </li>-->
<!--    <li>-->
<!--        <a target="_blank" href="--><?//= $seed_url ?><!--">DB Seed</a>-->
<!--    </li>-->
    <li>
        <a target="_blank" href="<?= $generator_url ?>">DB Generator</a>
    </li>
</ul>

<form method="post" action="<?= $view->url(ROUTE_copper_cp_action, ['action' => CPController::ACTION_LOGOUT]) ?>">
    <button type="submit">Logout</button>
</form>
</body>
