<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Entity\AbstractEntity;
use Copper\Component\DB\DBModel;

/** @var AbstractEntity[] $list */
$list = $view->dataBag->get('list');

/** @var DBModel $model */
$model = $view->dataBag->get('model');

?>

<?= $view->render('header') ?>

<?php if ($view->flashMessage->existsError()) { ?>
    <div style="border: 1px solid #ccc; padding: 10px; margin:5px; border-radius: 5px;">
        <span>Error:</span>
        <code><?= $view->out($view->flashMessage->getError()) ?></code>
    </div>
<?php } ?>

<div class="content_wrapper" style="padding: 0 20px">
    <h3><?= ucfirst($model->tableName) ?> List</h3>

    <table class="styled">
        <tr>
            <?php foreach ($model->getFieldNames() as $fieldName): ?>
                <th><?= $fieldName ?></th>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($list as $entry) : ?>
            <tr>
                <?php foreach ($model->getFieldNames() as $fieldName): ?>
                    <td><?= $entry->$fieldName ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>

</div>