<h2>Непрерывная интеграция</h2>
<div class="alert alert-info">Данный раздел предназначен для управления сборками проектов написанных на фреймфорке Litework. После каждого коммита советуем заглядывать суда и запускать процедуру тестиование приложения</div>
<div class="row-fluid" style="margin-top: 40px;">
    <div class="span6 well">
        <h3><?= $info['name']?></h3>
        <?if($info['description']):?>
            <div class="descruption">
                <?= $info['description']; ?>
            </div>
            <?endif;?>
        <div>
            <?if($info['buildable']):?>
                <?= \Html::Link(':start-build', "Cобрать сейчас", array('class'=>'btn btn-success btn-large', 'style'=>'width: 100%; padding-left: 0; padding-right: 0;'))?>
            <?endif?>
        </div>
    </div>

    <div class="span6">
        <div class="row-fluid">
            <div class="span6">
                <h4>Статистика</h4>
                <p>Последнаяя сборка: <b><?= \Html::Link(":build/".$info['lastBuild']['number'], $info['lastBuild']['number'])?></b></p>
                <p>Последняя провальная сборка: <b><?= \Html::Link(":build/".$info['lastFailedBuild']['number'], $info['lastFailedBuild']['number'])?></b></p>
                <p>Последняя успешная сборка: <b><?= \Html::Link(":build/".$info['lastSuccessfulBuild']['number'], $info['lastSuccessfulBuild']['number'])?></b></p>
                <p>Номер будущей сборки: <b><?= $info['nextBuildNumber'] ?></b></p>
            </div>
            <div class="span6 alert <?= $info['bootstrap-color'];?>" style="padding: 20px;">
                <?= $info['statusText']; ?>
            </div>
        </div>
    </div>

</div>
<?if(count($builds) > 0):?>
    <div class="row-fluid">
        <div class="span12">
            <h3>Сборки</h3>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <td>№</td>
                        <td>Описание сборки</td>
                        <td>Кто вызвал сборку?</td>
                        <td>Дата начала сборки</td>
                        <td>Результат сборки</td>
                        <td>Подробнее</td>
                    </tr>
                </thead>
                <tbody>
                    <?foreach($builds as $build):?>
                        <?if($build['result'] == 'SUCCESS'):?>
                            <tr class="success">
                        <?elseif($build['result'] == 'ABORTED'):?>
                            <tr class="info">
                        <?elseif($build['building']):?>
                            <tr class="info">
                        <?else:?>
                            <tr class="error">
                        <?endif?>
                            <td><?= $build['number']?></td>
                            <td><?= $build['description']?></td>
                            <td><?= $build['user']?></td>
                            <td><?= $build['time']->format('d.m.Y в H:i:s')?></td>
                            <td><?= $build['result']?></td>
                            <td><?= \Html::Link(":build/".$build['number'], 'Подробнее')?></td>
                        </tr>
                    <?endforeach?>
                </tbody>
            </table>
            <ul class="pager">
                <li class="previous <?if($page == 1):?>disabled<?endif?>">
                    <?= \Html::Link(':continuous-integration/'.($page - 1), '&larr; Новее')?>
                </li>
                <li class="<?if($page == 1):?>disabled<?endif?>">
                    <?= \Html::Link(':continuous-integration/1', '&uarr; на первую')?>
                </li>
                <li class="next <?if($page == $totalPages):?>disabled<?endif?>">
                    <?= \Html::Link(':continuous-integration/'.($page + 1), 'Старее &rarr;')?>
                </li>
            </ul>
        </div>
    </div>
<?endif?>