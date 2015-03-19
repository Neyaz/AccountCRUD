<div class="row">
    <div class="col-md-4">
        <div>
            <?foreach($errors as $error):?>
                <p class="error text-error"><?=$error?></p>
            <?endforeach?>
        </div>
        <form method="post">
            <div class="form-group"><?= Html::TextBoxLabeled('login', '', 'Логин: ', ['class' => 'form-control']) ?></div>
            <div class="form-group"><?= Html::TextBoxLabeled('name', '', 'ФИО: ', ['class' => 'form-control']) ?></div>
            <div class="form-group"><?= Html::EmailBoxLabeled('email', '', 'Email: ', ['class' => 'form-control']) ?></div>
            <div>
                <?= Html::ListBoxLabeled('gender', $genderList, null, 3, 'Выберите из списка:') ?>
            </div>
            <div class="form-group"><?= Html::TextBoxLabeled('birthday', '', 'Дата рождения: ', ['class' => 'form-control']) ?></div>
            <div class="form-group"><?= Html::TextAreaLabeled('comment', "", 'Коментарий:',
                    ['cols' => 60, 'rows' => 4, 'class' => 'form-control']) ?>
            </div>
            <?= Html::SubmitButton('submit', 'Заслать!', ['class' => 'btn btn-default']) ?> |
            <?= Html::Link(':index', 'Назад') ?>
    </div>
</div>