<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Component\HTML\HTML;
use Copper\Entity\AbstractEntity;
use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;

$list = AbstractEntity::fromViewAsList($view, 'list');

/** @var Copper\Resource\AbstractResource $Resource */
$Resource = $view->dataBag->get('resource');

$model = $Resource::getModel();

$head_title = $Resource::getName() . ' List';

$order_by = $view->queryBag->get('order_by', 'id');
$order = $view->queryBag->get('order', 'asc');
$offset = $view->queryBag->get('offset', 0);
$limit = $view->queryBag->get('limit', 255);

$show_archived = $view->queryBag->get('show_archived');
$show_archived_checked = $view->queryBag->has('show_archived') ? 'checked' : '';

$undoId = $view->flashMessage->get('undo_id', 0);

// ----------------------- Routes -----------------------

$urlGetNew = $view->url($Resource::route($Resource::GET_NEW));

$urlGetList = function ($params = []) use ($view, $Resource) {
    return $view->url($Resource::route($Resource::GET_LIST), $params);
};

$urlGetEdit = function ($id) use ($view, $Resource, $model) {
    return $view->url($Resource::route($Resource::GET_EDIT), [$model::ID => $id]);
};

$urlPostUndoArchive = function ($id) use ($view, $Resource, $model) {
    return $view->url($Resource::route($Resource::POST_UNDO_ARCHIVE), [$model::ID => $id]);
};

// ------------------- Field Names ------------------------

$field_names = [];

foreach (ArrayHandler::deleteValue($model->getFieldNames(), $model::ARCHIVED_AT) as $field) {
    $field_names[$field] = StringHandler::underscoreToCamelCase($field, true);
};

// custom $field_names

?>

<?= $view->render('header', ['head_title' => $head_title]) ?>

<style>
    tr.archived {
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
            <form style="display: inline-block;margin-left:10px;float:right" method="post"
                  action="<?= $urlPostUndoArchive($undoId) ?>">
                <button>Undo</button>
            </form>
        <?php endif; ?>
    </div>
<?php } ?>

<div class="content_wrapper" style="padding: 0 20px">
    <h3><?= $head_title ?></h3>
    <div>
        <form style="float: left" action="<?= $urlGetList() ?>" method="get">
            <label for="show_archived">Show Archived: </label>
            <input type="checkbox" id="show_archived" <?= $show_archived_checked ?> name="show_archived">
            <label for="offset">Offset: </label>
            <input type="number" id="offset" name="offset" value="<?= $offset ?>">
            <label for="limit">Limit: </label>
            <input type="number" id="limit" name="limit" value="<?= $limit ?>" autocomplete="off">
            <button>Filter</button>
        </form>
        <form style="float: right;margin-left: 40px;margin-bottom: 10px" action="<?= $urlGetNew ?>"
              method="get">
            <button>Create New</button>
        </form>
    </div>
    <div style="clear: both"></div>
    <table class="collection">
        <tr>
            <?php foreach ($field_names as $fieldName => $fieldText) {
                $th = HTML::th($fieldText)->id($fieldName);

                echo $th;
            } ?>
            <th class="empty"></th>
        </tr>
        <?php foreach ($list as $entry) {
            $tr = HTML::tr()->id('entry_' . $entry->id)->toggleClass('archived', $entry->isArchived());

            foreach ($field_names as $fieldName => $fieldText) {
                $value = $entry->$fieldName;

                $td = HTML::td($value);

                /*
                if ($fieldName === 'image')
                    $td = HTML::td()->addElement(HTML::img($value)->setAttr('width', '45px'));
                */

                $tr->addElement($td->addClass('col_' . $fieldName));
            }

            $td = HTML::td()->addStyle('text-align', 'center');

            if ($entry->isArchived() === false)
                $form = HTML::formGet($urlGetEdit($entry->id))->addElement(HTML::button('Edit'));
            else
                $form = HTML::form($urlPostUndoArchive($entry->id))->addElement(HTML::button('Restore'));

            $td->addElement($form);

            $tr->addElement($td);

            echo $tr;
        } ?>
    </table>
</div>

<script>
    let order = '<?= $order ?>';
    let order_by = '<?= $order_by ?>'.toLowerCase();
    let order_url = '<?= $urlGetList([
        'order_by' => '__field__',
        'order' => '__order__',
        'offset' => $offset,
        'limit' => $limit,
        'show_archived' => $show_archived
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