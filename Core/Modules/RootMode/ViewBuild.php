<h2>Непрерывная интеграция</h2>
<h3>Информация о сборке №<?= $build['number'];?> <small><?= \Html::Link(':continuous-integration', 'назад к проекту');?></small></h3>

<div class="row-fluid" style="margin-top: 40px;">
    <div class="span7">
        <?if(count($build['changeSet']['items'])>0):?>
            <h4>Отличия от предыдущей сборки</h4>
            <? foreach($build['changeSet']['items'] as $logItem): ?>
                <div class="well">Ревизия <b><?= $logItem['revision']?></b>. Автор изменений: <b><?= $logItem['user']?></b> от <b><?= $logItem['time']->format('d.m.Y в H:i:s')?></b>
                    <div class="muted">
                        <h5>Сообщение</h5>
                        <?= nl2br($logItem['msg']); ?>
                    </div>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <td width="40"></td>
                                <td>Файл</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?foreach($logItem['paths'] as $path):?>
                                <tr>
                                    <td style="text-align: center;">
                                        <?if($path['editType'] == 'edit'):?>
                                            <i class="icon-edit"></i>
                                        <?elseif($path['editType'] == 'add'):?>
                                            <i class="icon-plus"></i>
                                        <?else:?>
                                            <i class="icon-minus"></i>
                                        <?endif?>
                                    </td>
                                    <td><?= $path['file']?></td>
                                </tr>
                            <?endforeach?>
                        </tbody>
                    </table>
                </div>
            <? endforeach; ?>
        <?else:?>
            <div class="alert alert-info" style="padding: 20px;">
                Перед этой сборкой никто не делал коммиты
            </div>
        <?endif?>
    </div>
    <div class="span5">
        <h4>Основные сведения о сборке</h4>
        <p>Запустил сборку: <b><?= $build['user']?></b></p>
        <p>Результат:
            <b></b>
            <?if($build['result'] == 'SUCCESS'):?>
                <b class="label label-success">Успешно</b>
            <?elseif($build['building']):?>
                <b class="label label-info">В процессе</b>
            <?else:?>
                <b class="label label-important">Провал</b>
            <?endif?>
        </p>
    </div>
</div>

<?if(count($tests['suites']) > 0):?>
    <div class="row-fluid">
        <div class="span12">
            <h3>Отчет о тестировании (Провалено: <b><?= $tests['failCount'];?></b>, Пройдено: <b><?= $tests['passCount'];?></b>, Пропущено: <b><?= $tests['skipCount'];?></b>  )</h3>
            <? foreach($tests['suites'] as $suit):?>
                <?$i=1?>
                <h4><?= $suit['name']?></h4>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <td>№</td>
                            <td>Возраст</td>
                            <td>Время выполнения</td>
                            <td>Название</td>
                            <td>Стектрейс</td>
                            <td>Первый провал</td>
                            <td>Результат</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?foreach($suit['cases'] as $case):?>
                            <?if($case['status'] == 'PASSED'):?>
                                <tr class="success">
                            <?elseif($case['status'] == 'FIXED'):?>
                                <tr class="info">
                            <?else:?>
                                <tr class="error">
                            <?endif?>
                                <td><?= $i++?></td>
                                <td><?= $case['age']?></td>
                                <td><?= $case['duration']?></td>
                                <td><?= $case['name']?></td>
                                <td><?= nl2br($case['errorStackTrace'])?></td>
                                <td><?= $case['failedSince']?></td>
                                <td><?= $case['status']?></td>
                            </tr>
                        <?endforeach?>
                    </tbody>
                </table>
            <?endforeach?>
        </div>
    </div>
<?endif?>