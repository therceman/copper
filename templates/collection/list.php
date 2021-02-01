<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Entity\AbstractEntity;

/** @var AbstractEntity[] $list */
$list = $view->dataBag->get('list');

/** @var Copper\Resource\AbstractCollectionResource $resource */
$resource = $view->dataBag->get('resource');

$model = $resource::getModel();

$order_by = $view->query('order_by', 'id');
$order = $view->query('order', 'asc');
$offset = $view->query('offset', 0);
$limit = $view->query('limit', 20);

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
    <div style="float:right;margin-top:-40px;">
        <form style="float: left" action="<?= $view->path($resource::GET_LIST) ?>" method="get">
            <label for="offset">Offset: </label>
            <input type="number" id="offset" name="offset" value="<?= $offset ?>">
            <label for="limit">Limit: </label>
            <input type="number" id="limit" name="limit" value="<?= $limit ?>" autocomplete="off">
            <button>Filter</button>
        </form>
        <form style="float: right;margin-left: 10px;" action="<?= $view->path($resource::GET_NEW) ?>" method="get">
            <button>Create New</button>
        </form>
    </div>
    <table class="collection">
        <tr>
            <?php foreach ($model->getFieldNames() as $fieldName): ?>
                <th id="<?= $fieldName ?>"><?= $fieldName ?></th>
            <?php endforeach; ?>
            <th class="empty"></th>
        </tr>
        <?php foreach ($list as $entry) : ?>
            <tr>
                <?php foreach ($model->getFieldNames() as $fieldName): ?>
                    <td><?= $entry->$fieldName ?></td>
                <?php endforeach; ?>
                <td style="text-align: center">
                    <form action="<?= $view->path($resource::GET_EDIT, [$model::ID => $entry->id]) ?>">
                        <button>Edit</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    let order = '<?= $order ?>';
    let order_by = '<?= $order_by ?>'.toLowerCase();
    let order_url = '<?= $view->path($resource::GET_LIST, ['order_by' => '__field__', 'order' => '__order__']) ?>';

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