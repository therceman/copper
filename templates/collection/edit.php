<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Entity\AbstractEntity;

/** @var AbstractEntity $entity */
$entity = $view->dataBag->get('entity');

/** @var Copper\Resource\AbstractCollectionResource $resource */
$resource = $view->dataBag->get('resource');

$model = $resource::getModel();

$action = $entity->exists() ? 'Update' : 'Create';

$action_url = $entity->exists()
    ? $view->url($resource::POST_UPDATE, [$model::ID => $entity->id])
    : $view->url($resource::POST_CREATE);


?>

<?= $view->render('header') ?>

<style>
    input:not([type=checkbox]){
        width: 500px;
    }

    select {
        width: 508px;
    }

    textarea {
        width: 500px;
        height: 150px;
    }

    form {
        margin-bottom: 10px;
    }
</style>

<?php if ($view->flashMessage->hasError()) { ?>
    <div style="border: 1px solid #ccc; padding: 10px; margin:5px; border-radius: 5px;">
        <span>Error:</span>
        <code><?= $view->out($view->flashMessage->getError()) ?></code>
    </div>
<?php } ?>

<div class="content_wrapper" style="padding: 0 20px">
    <h3><?= $entity->exists() ? 'Edit Entity #' . $entity->id : 'Create New Entity'; ?></h3>

    <?php if ($entity->exists()) : ?>
        <div style="float:right;margin-top:-40px;">
            <form style="float: right;margin-left: 10px;"
                  action="<?= $view->url($resource::POST_REMOVE, [$model::ID => $entity->id]) ?>" method="post">
                <button>Remove</button>
            </form>
        </div>
    <?php endif; ?>

    <form action="<?= $action_url ?>" method="post">
        <table class="entry">
            <?php foreach ($model->getFieldNames() as $fieldName) {
                $field = $model->getFieldByName($fieldName);

                $id = $fieldName;
                $name = $fieldName;
                $value = $entity->$fieldName ?? '';

                $disabled = '';
                if ($model->hasStateFields() && in_array($name, [$model::REMOVED_AT, $model::UPDATED_AT, $model::CREATED_AT]))
                    $disabled = 'disabled';

                if ($name === $model::ID)
                    continue;

                $input = "<input type='text' $disabled name='$name' value='$value'>";

                if ($field->typeIsBoolean()) {
                    $checked = (boolval($value) === true) ? 'checked' : '';
                    // trick to pass enabled=0 to server (when checkbox is unchecked)
                    $input = "<input type=hidden name='$name' value='0'>";
                    $input .= "<input type=checkbox $checked name='$name' value='1'>";
                }

                if ($field->typeIsText()) {
                    $input = "<textarea name='$name'>$value</textarea>";
                }

                if ($field->typeIsDecimal()) {
                    $min = $field->unsigned() ? 'min="0"' : '';
                    $input = "<input type='number' name='$name' $min value='$value' step='.01'>";
                }

                if ($field->typeIsEnum()) {
                    $options = $field->getLength();
                    $select = "<select name='$name'>";
                    foreach ($options as $option) {
                        $selected = ($value === $option) ? 'selected' : '';
                        $select .= "<option $selected value='$option'>$option</option>";
                    }
                    $input = $select . '</select>';
                }

                echo "
            <tr>
                <td><label>$name</label></td>
                <td>$input</td>
            </tr>";
            } ?>
        </table>
        <button style="float:right"><?= $action ?></button>
    </form>
    <form action="<?= $view->url($resource::GET_LIST) ?>" method="get">
        <button style="float:left">Cancel</button>
    </form>

</div>