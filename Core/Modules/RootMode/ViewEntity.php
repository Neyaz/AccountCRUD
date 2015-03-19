<div class="entity">
    <h3><?= $name ?></h3>
    <ul>
    <? foreach ($items as $field => $config): ?>
        <li>
            <?= $config['id'] ? "<span class=\"key\">$field</span>" : $field ?>
            <span class="<?= $config['assoc'] ? 'assoc-type' : 'type' ?>">
                <?= $config['owning'] ? '<b>' . $config['type'] . '</b>' : $config['type'] ?>
            </span>
            <span class="link"><?= $config['target'] ?></span>
            <span class="extended">
                <?= $config['nullable'] ? '(nullable)' : '' ?>
                <?= $config['unique'] ? '(unique)' : '' ?>

                <? if ($config['info'] != NULL):
                    echo '<br/>';
                    $i = 0;
                    foreach ($config['info'] as $key => $val):
                ?>
                    <small><?= $key ?> = <?= $val ?></small><?= $i++ == 0 ? ', ' : '' ?>
                <? endforeach; endif; ?>
            </span>
        </li>
    <? endforeach; ?>
    </ul>
</div>