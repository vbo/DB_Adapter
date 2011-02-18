<h1>DB_Adapter usage example &rarr; guestbook (<?php echo get_class($DB); ?>)</h1>
<form method="post" action="">
    <p>
        <b>Автор</b><br />
        <input name="author" />
    </p>
    <p>
        <b>Текст*</b><br />
        <textarea name="text" cols="" rows=""></textarea>
    </p>
    <input type="submit" />
</form>
<br />
<?php foreach ($messages as $message):?>
    <p>
        <?php echo $message['text']?><br />
        <small><b><?php echo $message['author']?></b> <?php echo substr($message['created'], 0, 19)?></small>
    </p>
<?php endforeach;?>