<p>Проверка</p>

<? foreach ($items as $it): ?>
    <p><?= $it ?></p>
<? endforeach; ?>

<p><?= Html::Link(':', 'Главный путь роутинга') ?></p>