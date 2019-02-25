<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="template/css/admin.css">
    <title>Vitamin photo - <?= $consultantNumber?></title>
</head>

<body>
    <div class="wrapper">
        <img src="template/img/header_croped_web.png" class="logo">
        <span class="header-message"><?= $consultant->name?> (<?= $consultantNumber?>)</span>
        <button id="refresh" class="btn btn-primary refresh">Refresh</button>
        <?php
            foreach ($photoGroups as $groupName=>$photos) {?>
                <hr>
                <div class="consultant"><?= $groupName?>:</div>
                <div class="photos">
                    <?php
                        foreach($photos as $photo) {?>
                            <div class="container" data-id="<?= $photo->id?>">
                                <div class="photo-body">
                                    <a href="<?= $photo->filePath?>" title="<?= $photo->fileName?>" target="_blank">
                                        <img class="img-thumbnail" src="<?= $photo->thumbnailPath?>">
                                    </a>
                                    <div class="photo-uploading-time">original date: <?= $photo->originalDate?></div>
                                    <div class="photo-uploading-time">uploading date: <?= $photo->uploadingDateTime?></div>
                                </div>
                                <?php if($isSuperAdmin): ?>
                                    <div class="buttons">
                                        <button class="btn btn-danger object-btn" data-value="false">Disapprove</button>
                                        <button class="btn btn-success object-btn" data-value="true">Approve</button>
                                    </div>
                                <?php endif;?>
                            </div>
                    <?php }?>
                </div>
            <?php }?>
        <hr>
    </div>
</body>

<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script>
    $(function () {
        $('#refresh').click(function() {
            location.reload();
        });
        $('.object-btn').on('click', function () {
            var object = $(this).parent().parent();
            var resultValue = $(this).attr('data-value');
            var idValue = object.attr('data-id');
            $.post('approve.php', {id: idValue, approved: resultValue})
                .done(function(data) {
                    if(!$.isEmptyObject(data)) {
                        var result = JSON.parse(data);
                        if(result.status == 'success') {
                            location.reload();
                        }
                        return;
                    }
                    alert("Error on page. Please contact administrator.");
                })
                .fail(function() {
                    alert("Error on page. Please contact administrator.");
                });
        });
    });
</script>

</html>
