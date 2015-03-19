<? if (count($errors) != 0): ?>
    <h2 class="error">Список ошибок</h2>
    <? foreach ($errors as $class => $list): ?>
        <h3 class="error"><?= $class ?></h3>
        <ul>
        <? foreach ($list as $err): ?>
            <li class="error"><?= $err ?></li>
        <? endforeach; ?>
        </ul>
    <? endforeach; ?>
<? endif; ?>

<h2>Действия</h2>
<p>
    <?= Html::Link(':entities-autogenerate', 'Автогенерация', array('class' => 'btn btn-success btn-large')) ?>
    <?= Html::Link(':update-database', 'Обновить структуру базы', array('class' => 'btn btn-primary btn-large')) ?>
    <?= Html::Link(':load-data', 'Загрузить данные', array('class' => 'btn btn-warning confirm btn-large')) ?>
    <?= Html::Link(':entities-generator', 'Генератор сущностей', array('class' => 'btn btn-danger btn-large')) ?>
</p>
<? if (count($entities) == 0): ?>
    <br/>
    <h2>Первый запуск</h2>
    <p><?= Html::Link(':create-mapping', 'Создать классы на основе базы данных', array('class' => 'button  confirm')) ?></p>
<? endif; ?>
<br/>

<h2>Структура</h2>
<? $i = 0; ?>
<table border="0" class="database table table-bordered table-hover">
    <tbody>
    <? foreach ($entities as $entity): ?>
        <?= $i++ % $split == 0 ? '<tr valign="top">' : '' ?>
        <td class="span3" style="width:auto"><?= $entity ?></td>
        <?= $i % $split == 0 ? '</tr>' : '' ?>
    <? endforeach; ?>
    <?
        $dmp = $i % $split != 0;
        for (; $i % $split != 0; $i++)
            echo '<td class="span3" style="width:auto">&nbsp;</td>';
        if ($dmp) echo '</tr>';
    ?>
    </tbody>
</table>