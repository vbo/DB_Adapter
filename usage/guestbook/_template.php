<h1>DB_Adapter usage example &rarr; guestbook (<?=get_class($DB)?>)</h1>
<form method="post">
    <p>
        <b>Автор</b><br />
        <input name="author" />
    </p>
    <p>
        <b>Текст*</b><br />
        <textarea name="text"></textarea>
    </p>
    <input type="submit" />
</form>
<br />
<?php foreach ($messages as $message):?>
    <p>
        <?=$message['text']?><br />
        <small><b><?=$message['author']?></b> <?=substr($message['created'], 0, 19)?></small>
    </p>
<?php endforeach;?>