<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\CP\CPController;

$migrate_url = $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_MIGRATE]);
$seed_url = $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_SEED]);
$gen_model_fields_url = $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_DB_GEN_MODEL_FIELDS]);
$entity_list = $view->dataBag->get('entity_list');
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
    <li>
        <form action="<?= $gen_model_fields_url ?>">
            <span>Generate Model Fields from Entity:</span>
            <select name="class_name">
                <?php foreach ($entity_list as $className) {
                    echo "<option value='$className'>$className</option>";
                } ?>
            </select>
            <button>GO</button>
        </form>
    </li>
</ul>

<form method="post" action="<?= $view->path(ROUTE_copper_cp_action, ['action' => CPController::ACTION_LOGOUT]) ?>">
    <button type="submit">Logout</button>
</form>
</body>