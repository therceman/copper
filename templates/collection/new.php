<?php /** @var \Copper\Component\Templating\ViewHandler $view */

use Copper\Entity\AbstractEntity;

$resource = $view->dataBag->set('entity', new AbstractEntity());

?>

<?= $view->render('collection/edit') ?>