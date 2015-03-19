<table class="table">
    <thead>
    <th>Id</th>
    <th>Логин</th>
    <th>ФИО</th>
    <th>Email</th>
    <th>Пол</th>
    <th>Дата рождения</th>
    <th></th>
    <th></th>
    </thead>
    <tbody>
        <? foreach($accounts as $account): ?>
            <tr>
                <td><?=$account->GetId()?></td>
                <td><?=Html::Link('show/'.$account->GetId(), $account->GetLogin())?></td>
                <td><?=$account->GetName()?></td>
                <td><?=$account->GetEmail()?></td>
                <td><?=$account->GetGender()?></td>
                <td><?
                    if($account->GetBirthday()){
                        echo date_format($account->GetBirthday(), 'd-m-Y');
                    }
                ?></td>
                <td><?=Html::Link(":edit/".$account->GetId(), "Edit", ['class' => "btn btn-info"])?></td>
                <td><?=Html::Link(":delete/".$account->GetId(), "Delete", ['class' => "btn btn-info"])?></td>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
<div>
    <?=Html::Link(":create", "New account", ['class' => "btn btn-info"])?>
</div>