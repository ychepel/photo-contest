<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="template/css/admin.css">
    <title>Vitamin photo approving</title>
</head>

<body>
    <div class="wrapper">
        <img src="template/img/header_croped_web.png" class="logo">
        <span class="header-message">Unverified photos: <span id="quantity"><?= sizeof($photos) ?></span></span>
        <button id="refresh" class="btn btn-primary refresh">Refresh</button>
        <?php
            $previousConsultantNumber = null;
            foreach($photos as $photo) {
                if($photo->consultantNumber != $previousConsultantNumber) {
                    $previousConsultantNumber = $photo->consultantNumber;?>
                    <?= is_null($previousConsultantNumber) ? '' : '</div>'?>
                    <hr>
                    <div class="consultant">
                        <a href="admin.php?profile=<?= $photo->consultantNumber?>" target="_blank">
                            <?= $photo->consultantName?> (<?= $photo->consultantNumber?>)
                        </a>
                    </div>
                    <div class="photos">
                <?php }?>
                <div class="container" data-id="<?= $photo->id?>">
                    <div class="photo-body">
                        <a href="<?= $photo->filePath?>" title="<?= $photo->fileName?>" target="_blank">
                            <img class="img-thumbnail" src="<?= $photo->thumbnailPath?>">
                        </a>
                        <div class="photo-uploading-time">original date: <?= $photo->originalDate?></div>
                    </div>
                    <div class="buttons">
                        <button class="btn btn-danger object-btn" data-value="false">Disapprove</button>
                        <button class="btn btn-success object-btn" data-value="true">Approve</button>
                    </div>
                </div>
            <?php }?>
            <?= is_null($previousConsultantNumber) ? '' : '</div>'?>
        <hr>
    </div>
</body>

<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script>
    $(function () {
        function DecreaseQuantity() {
            var current = $('#quantity').text();
            $('#quantity').text(current - 1);
        }
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
                            object.remove();
                            DecreaseQuantity();
                        }
                        return;
                    }
                    alert("Error on page. Please contact Yuriy Chepel.");
                })
                .fail(function() {
                    alert("Error on page. Please contact Yuriy Chepel.");
                });
        });
    });
</script>

</html>

