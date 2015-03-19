<h2>Обновить структуру базы на основе сущностей</h2>
<p>Адрес сервера: <b><?= $em->GetConnection()->getHost() ?></b>. Имя базы данных <b><?= $em->GetConnection()->getDatabase()?></b></p>
<div class="alert alert-info">
    Будьте осторожны при использовании данного инструмента. Если вы видите DROP запрос, то все данные, которые содержатся в удаляемом объекте будут уничтожены безвозвратно
</div>
<?if($queries):?>
<h3>Запросы на изменение структуры</h3>
<pre>
<?= $queries; ?>
</pre>
<?endif?>
<?= \Html::Form("", null, array('class'=>'form-inline'));?>
    <?if($queries):?>
        <?= \Html::SubmitButton('execute &rarr;', null, array('class' => 'btn btn-success btn-large'))?>
    <?endif?>
    <?= \Html::Link(':database', 'or cancel');?>
<?= \Html::EndForm();?>

