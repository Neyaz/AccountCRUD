<html lang="en">
    <head>
        <title><?=$title?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <link rel="stylesheet" href="<?=Url::Make(':css') ?>?<?= \Core\Utils::GeneratePassword()?>" type="text/css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script type="text/javascript">
            $(function(){
                $('.confirm').click(function(){  var answer = confirm('Вы уверены?'); return answer });
            });
        </script>
    </head>
    <body>
        <div class="page-body">
            <div class="masthead">
                <ul class="nav nav-pills pull-right">
                    <li<?if($sector == 'index'):?> class="active"<?endif?>><?= Html::Link(':index', 'Главная') ?></li>
                    <li<?if($sector == 'database'):?> class="active"<?endif?>><?= Html::Link(':database', 'База данных') ?></li>
                    <li<?if($sector == 'php-info'):?> class="active"<?endif?>><?= Html::Link(':php-info', 'PHP Info') ?></li>
                    <li<?if($sector == 'continuous-integration'):?> class="active"<?endif?>><?= Html::Link(':continuous-integration', 'Непрерывная интеграция') ?></li>
                    <li><?= Html::Link(':exit', 'Покинуть') ?></li>
                </ul>
                <h2 class="muted">LiteWork <?= $version?></h2>
            </div>
            <div class="row-fluid">
                <div class="span12"><?=$content?></div>
            </div>
        </div>
    </body>
</html>
