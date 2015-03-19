<html>
    <head>
        <title>LiteWork Local Mode</title>
        <style>
            body { color: #333; background: #FEFFF4; }
            a { color: #351b97; }
            li { list-style-type: square; }
            .err { color: red }
        </style>
    </head>
<body>
<h1>Меню</h1>
<ul>
<?
foreach ($links as $val)
{
    $name = $val;
    if ($name == '')
        $name = 'index';
    else
        $val = "=$val";
    echo "<li><a href='/?LiteWork$val'>$name</a></li>";
}
?>
</ul>

<h2>Приложения</h2>