<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\HTML\HTML;
use Copper\Entity\AbstractEntity;
use Copper\Handler\ArrayHandler;

/** @var AbstractEntity[] $list */
$list = $view->dataBag->get('list');

/** @var Copper\Resource\AbstractResource $Resource */
$Resource = $view->dataBag->get('resource');

$model = $Resource::getModel();

$order_by = $view->query('order_by', 'id');
$order = $view->query('order', 'asc');
$offset = $view->query('offset', 0);
$limit = $view->query('limit', 20);

$show_removed = $view->query('show_removed');
$show_removed_checked = $view->queryBag->has('show_removed') ? 'checked' : '';

$undoId = $view->flashMessage->get('undo_id', 0);
$undoAction = $view->url($Resource::POST_UNDO_REMOVE, [$model::ID => $undoId]);

$field_names = ArrayHandler::removeKeys($model->getFieldNames(), $model::REMOVED_AT);
?>

<?= $view->render('header') ?>

<style>
    tr.removed {
        background: #bbb !important;
    }
</style>

<?php if ($view->flashMessage->hasError()) { ?>
    <div class="bg_error" style="border: 1px solid #ccc; padding: 10px; margin:5px; border-radius: 5px;">
        <span>Error:</span>
        <code class="bg_error"><?= $view->out($view->flashMessage->getError()) ?></code>
    </div>
<?php } ?>

<?php if ($view->flashMessage->hasSuccess()) { ?>
    <div class="bg_success" style="border: 1px solid #ccc; padding: 10px; margin:5px; border-radius: 5px;">
        <code class="bg_success"><?= $view->out($view->flashMessage->getSuccess()) ?></code>
        <?php if ($undoId !== 0) : ?>
            <form style="display: inline-block;margin-left:10px;float:right" method="post" action="<?= $undoAction ?>">
                <button>Undo</button>
            </form>
        <?php endif; ?>
    </div>
<?php } ?>

<div class="content_wrapper" style="padding: 0 20px">
    <h3><?= $Resource::getName() ?> List</h3>
    <div>
        <form style="float: left" action="<?= $view->url($Resource::GET_LIST) ?>" method="get">
            <label for="show_removed">Show Removed: </label>
            <input type="checkbox" id="show_removed" <?= $show_removed_checked ?> name="show_removed">
            <label for="offset">Offset: </label>
            <input type="number" id="offset" name="offset" value="<?= $offset ?>">
            <label for="limit">Limit: </label>
            <input type="number" id="limit" name="limit" value="<?= $limit ?>" autocomplete="off">
            <button>Filter</button>
        </form>
        <form style="float: right;margin-left: 40px;margin-bottom: 10px" action="<?= $view->url($Resource::GET_NEW) ?>"
              method="get">
            <button>Create New</button>
        </form>
    </div>
    <div style="clear: both"></div>
    <table class="collection">
        <tr>
            <?php foreach ($field_names as $fieldName): ?>
                <th id="<?= $fieldName ?>"><?= $fieldName ?></th>
            <?php endforeach; ?>
            <th class="empty"></th>
        </tr>
        <?php foreach ($list as $entry) : ?>
            <tr class="<?= $entry->isRemoved() ? 'removed' : '' ?>">
                <?php foreach ($field_names as $fieldName): ?>
                    <td><?= $entry->$fieldName ?></td>
                <?php endforeach; ?>
                <td style="text-align: center">
                    <?php
                    if ($entry->isRemoved() === false)
                        echo HTML::formGet($view->url($Resource::GET_EDIT, [$model::ID => $entry->id]))
                            ->addElement(HTML::button('Edit'));
                    else
                        echo HTML::form($view->url($Resource::POST_UNDO_REMOVE, [$model::ID => $entry->id]))
                            ->addElement(HTML::button('Restore'));
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    let order = '<?= $order ?>';
    let order_by = '<?= $order_by ?>'.toLowerCase();
    let order_url = '<?= $view->url($Resource::GET_LIST, [
        'order_by' => '__field__',
        'order' => '__order__',
        'offset' => $offset,
        'limit' => $limit,
        'show_removed' => $show_removed
    ]) ?>';

    $tableThList = document.querySelectorAll('table th');

    $tableThList.forEach(function ($th) {
        let id = $th.id;
        let current_order_by = false;

        if (id === order_by) {
            current_order_by = true;
            $th.classList.add('sort');
            $th.classList.add(order === 'asc' ? 'asc' : 'desc')
        }

        if ($th.id === '')
            return;

        $th.addEventListener('click', function (e) {
            let url = order_url.replace('__field__', $th.id);
            url = url.replace('__order__', (current_order_by && order === 'asc') ? 'desc' : 'asc');
            window.location.href = url;
        });
    })
</script>