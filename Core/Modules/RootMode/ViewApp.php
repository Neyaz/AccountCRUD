<h4><?= $name ?></h4>
<ul>
    <? foreach ($modules as $it): ?>
        <li><?= $it ?></li>
    <? endforeach; ?>

</ul>
<?= Html::Form(':create-project-item', null, array('class'=>'form-inline')) ?>
    <input type="hidden" name="type" value="module" />
    <input type="hidden" name="app" value="<?= $name ?>" />
    <input type="text" name="name" value="" class="input-medium"/>
    <input type="submit" value="create" class="btn btn-primary" />
<?= Html::EndForm() ?>