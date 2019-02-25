<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="template/css/admin.css">
    <title><?= sizeof($photos)?> - total approved photos</title>
</head>

<body>
<div class="wrapper">
    <?php
        foreach($photos as $photo) {?>
            <div class="container">
                <div class="photo-body">
                    <a href="<?= $photo->filePath?>" title="<?= $photo->fileName?>">
                        <img class="img-thumbnail" src="<?= $photo->thumbnailPath?>">
                    </a>
                </div>
            </div>
    <?php }?>
</div>
</body>
</html>
