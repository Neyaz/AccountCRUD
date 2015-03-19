<h1>Entities Generator</h1>

<? if (count($newEntities) > 0): ?>
    <h2>From Database</h2>
    Таблицы БД, которые не имеют маппинга:
    <ul>
    <? foreach ($newEntities as $it): ?>
        <!-- Неплохо бы еще давать выбрать желаемое имя -->
        <li><?= $it->name ?> [<?= Html::Link(':entities-generator/add-from-db/'.$it->name, 'создать') ?>]</li>
    <? endforeach; ?>
    </ul>
<? endif; ?>

<? if (count($newFieldsList) > 0): ?>
    <h2>New Fields</h2>
    Поля базы с отсутствующим маппингом:
    <? foreach ($newFieldsList as $name => $entity): ?>
        <h3><?= $name ?></h3>
        <ul>
        <? foreach ($entity as $field): ?>
            <li><?= $field['fieldName'] . ': ' . $field['type'] ?></li>
        <? endforeach; ?>
        </ul>
        <?= Html::Link(":entities-generator/create-fields/$name/", 'сгенерировать') ?>
    <? endforeach; ?>
<? endif; ?>

<p>Если тут пусто - значит делать нечего</p>