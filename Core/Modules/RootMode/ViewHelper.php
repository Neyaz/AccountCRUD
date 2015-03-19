<h4><?= $name ?></h4>
<ul>
    <? foreach ($methods as $name => $it): ?>
        <li><?= $name ?>(<?= $it ?>)</li>
    <? endforeach; ?>

</ul>
