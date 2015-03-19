<div class="row">
    <div class="col-md-4">
        <div>
            <?foreach($errors as $error):?>
                <p class="error text-error"><?=$error?></p>
            <?endforeach?>
        </div>
        <form method="post">
            <div class="form-group"><?= Html::TextBoxLabeled('login', $account->getLogin(), 'Логин: ', ['class' => 'form-control']) ?></div>
            <div class="form-group"><?= Html::TextBoxLabeled('name', $account->getName(), 'ФИО: ', ['class' => 'form-control']) ?></div>
            <div class="form-group"><?= Html::EmailBoxLabeled('email', $account->getEmail(), 'Email: ', ['class' => 'form-control']) ?></div>
            <div>
                <?= Html::ListBoxLabeled('gender', $genderList, $account->GetGender(), 3, 'Выберите из списка:') ?>
            </div>
            <div class="form-group"><?= Html::TextBoxLabeled('birthday', $account->getBirthday()?date_format($account->GetBirthday(), 'd-m-Y'):null, 'Дата рождения: ', ['class' => 'form-control']) ?></div>
            <div class="form-group"><?= Html::TextAreaLabeled('comment', $account->getComment(), 'Коментарий:',
                    ['cols' => 60, 'rows' => 4, 'class' => 'form-control']) ?>
            </div>
            <?= Html::SubmitButton('submit', 'Заслать!', ['class' => 'btn btn-default']) ?> |
            <?= Html::Link(':index', 'Назад') ?>
    </div>
</div>