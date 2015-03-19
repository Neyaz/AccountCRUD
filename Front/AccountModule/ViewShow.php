<div class="row">
    <div>
        <label>Логин:</label>
        <?=$account->GetLogin()?>
    </div>
    <div>
        <label>ФИО:</label>
        <?=$account->GetName()?>
    </div>
    <div>
        <label>Email:</label>
        <?=$account->GetEmail()?>
    </div>
    <div>
        <label>Пол:</label>
        <?=$account->GetGender()?>
    </div>
    <div>
        <label>Дата рождения:</label>
        <?
        if($account->GetBirthday()){
            echo date_format($account->GetBirthday(), 'd-m-Y');
        }
        ?>
    </div>
    <div>
        <label>Комментарий:</label>
        <?=$account->GetComment()?>
    </div>
</div>

<?=Html::Link(':index', 'Назад')?>