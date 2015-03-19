<h3><?= $name ?></h3>

<ul>
<? foreach ($arr as $it): ?>
    <li><?= $it ?></li>
<? endforeach; ?>
    <li>
        <form>
            <input type="hidden" name="LiteWork" value="application: create-module" />
            <input type="hidden" name="app" value="<?= $name ?>" />
            <input type="text" name="name" value="" size="20" />
            <input type="submit" value="create" />
        </form>
    </li>
</ul>