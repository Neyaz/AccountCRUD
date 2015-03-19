<div class="row-fluid">
    <div class="span6 alert alert-info" style="text-align: center;" >
        Текущий режим работы: <br/><b style="font-size: 25px; line-height: 30px;"><?= $modesText[\Core\Config::Instance()->GetStr('mode')]; ?></b>
    </div>
    <?foreach($modes as $mode):?>
        <a class="span3 alert alert-success" style="text-align: center;" href="<?= \Url::Make(":switch-mode/".$mode)?>" >
            Устанвить новый режим работы: <br/><b style="font-size: 25px; line-height: 30px;"><?= $modesText[$mode] ?></b>
        </a>
    <?endforeach?>
</div>

<h2>Приложения</h2>
<div class="row-fluid">
    <?$i=0;?>
    <? foreach ($applications as $app): ?>
    <?if(($i++%4)==0):?>
        </div><div class="row-fluid">
    <?endif?>
        <div class="span3 well">
            <?= $app ?>
        </div>
    <? endforeach; ?>
    <?if(($i++%4)==0):?>
        </div><div class="row-fluid">
    <?endif?>
    <div class="span3">
        <h4>Создать приложение</h4>
        <?= Html::Form(':create-project-item', null, array('class'=>'form-inline')) ?>
            <input type="hidden" name="type" value="app" />
            <input type="text" name="name" value="" class="input-medium"/>
            <input type="submit" value="create" class="btn btn-primary" />
        <?= Html::EndForm() ?>
    </div>
</div>

<h2>Хелперы</h2>
<div class="row-fluid">
    <?$i=0;?>
    <? foreach ($helpers as $helper): ?>
    <?if(($i++%4)==0):?>
        </div><div class="row-fluid">
    <?endif?>
        <div class="span3 well">
            <?= $helper ?>
        </div>
    <? endforeach; ?>
    <?if(($i++%4)==0):?>
        </div><div class="row-fluid">
    <?endif?>
    <div class="span3 well">
        <h4>Создать Хелпер</h4>
        <?= Html::Form(':create-project-item', null, array('class'=>'form-inline')) ?>
            <input type="hidden" name="type" value="helper" />
            <input type="text" name="name" value="" class="input-medium"/>
            <input type="submit" value="create" class="btn btn-primary" />
        <?= Html::EndForm() ?>
    </div>
</div>