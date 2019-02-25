<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta property="og:type" content="article" />
    <meta property="og:title" content="Mary Kay - Марафон класів «Вітамінний клас»" />
    <meta property="og:image" content="template/img/header_croped_web.png" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
          integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/"
          crossorigin="anonymous">
    <link rel="stylesheet" href="template/css/style.css">

    <title>Марафон класів «Вітамінний клас»</title>
</head>

<body>
<div class="myheader">
    <div class=" headerblock">
        <img class="logoimg" src="template/img/logo.png" alt="MARY KAY LOGO">
    </div>
    <div class="headermainphoto">
        <img src="template/img/header_croped_web.png" alt="Логотип Виклику">
    </div>
</div>
<div class="mainblock">
    <div>
        <img class="orangewave" src="template/img/wave.png" alt=""></div>
    <div class="container ">
        <div class="row ">
            <div class="col">
                <div class="whiteblock">
                    <div class="container">
                        <h2>Вітаємо, <?= $consultant->mailingName?></h2>
                        <p>Завантажте 10+ фото з різних класів з краси</p>
                        <p>Завантажених фото: <span id="photoCounter"><?= sizeof($images)?></span></p>

                        <div class="buttons row justify-content-center">
                            <div class="func-button">
                                <div class=".col-md-4 .offset-md-4 inputbox">
                                    <div class="file_upload">
                                        <div class="addimg">
                                            <div onclick="document.getElementById('fileMulti').click(); return false;"/>
                                                <p id="addButton">Додати фото</p>
                                            </div>
                                            <input type="file" id="fileMulti" class="fileMulti" name="fileMulti[]"
                                                   multiple style="visibility: hidden;"/>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="<?= $consultant->isDirector() ? '' : ' hidden' ?> func-button">
                                <div class=".col-md-4 .offset-md-4 inputbox">
                                    <div class="file_upload">
                                        <div class="addimg">
                                            <a href="index.php?p=unit<?= isset($_GET['debug']) ? '&debug='.$_GET['debug']: '' ?>">
                                                <p>Бізнес-Група</p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="loading" class="hidden"><img src="template/img/loader.gif"></div>
                        <ul id="messages"></ul>

                        <div class="row justify-content-center imgblock">
                            <div class="col" id="outputMulti">
                                <?php
                                    foreach ($images as $image) {
                                        echo '<div class="mycontainer preview" data-toggle="modal" data-target="#myModal">
                                            <img class="img-thumbnail image" src="'.$image->thumbnailPath.'" title="'.$image->fileName.'"
                                                data-id="'.$image->id.'" data-status="'.$image->getStatus().'" data-delete-allowed="'.($image->isDeleteAllowed()?'true':'false').'"/>
                                            <span style="color: '.$image->getWatermarkColor().';"><i class="'.$image->getWatermarkCssClass().'"></i></span>
                                        </div>';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- The Modal -->
                    <div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                         aria-hidden="true" backdrop="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="modal-mycontainer">
                                        <img class="modal-image img-thumbnail" id="modalThumbnail" src="" alt="">
                                    </div>
                                    <div class="col d-flex justify-content-center statusdiv">
                                        <span id="modalWatermarkSpan"><i id="modalWatermark"></i></span>
                                        <h3 class="d-flex justify-content-center" id="modalDescription"></h3>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-warning modalbutton" id="modalDeleteButton">ВИДАЛИТИ</button>
                                    <button type="button" class="btn btn-warning modalbutton" data-dismiss="modal" id="modalExitButton">
                                        ЗАКРИТИ
                                    </button>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- The Modal -->
                </div>
            </div>
            <hr>
            <div class="container legal " id="flip" onclick="scrollWin()">
                <p>
                    *Завантажуючи фото Консультант дає ТОВ «Мері Кей (Україна)
                    Лтд.» (далі - Компанія) згоду на
                    його використання шляхом, включаючи, але не обмежуючись, <span class="container legal " id="panel">
                            переробки, адаптації фото,
                            розміщення фото в
                            друкованих, Інтернет-виданнях компанії, сторінках компанії у соціальних мережах,
                            презентаціях
                            та
                            використання в інших рекламно-інформаційних матеріалах, на території України
                            протягом
                            всього строку охорони прав інтелектуальної власності на таке фото, а також
                            підтверджує та
                            гарантує, що він
                            отримав необхідні згоди та дозволи від усіх третіх осіб, в тому числі зображених на
                            фото, на
                            його передачу
                            компанії для використання способами зазначеними вище, та у випадку виникнення
                            будь-яких
                            претензій чи
                            позовів третіх осіб самостійно та за свій рахунок зобов’язується їх врегулювати.</span></p>
            </div>
        </div>
    </div>
</body>
</html>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    $(function () {
        $('#myModal').on('shown.bs.modal', function () {
            $('#myInput').focus()
        });

        $(document).ready(function(){
            $("#flip").click(function(){
                $("#panel").toggle("fast", function(){
                    window.scrollTo(500, 2000);
                });
            });
        });

        function showInBlockTop(parentIdName, blockContent) {
            var parentId = '#' + parentIdName;
            var parentBlock = $(parentId);
            return parentBlock.prepend(blockContent);
        }

        function showStandardError() {
            blockContent = '<li>Помилка під час виконання. Спробуйте, будь ласка, пізніше або зверніться за допомогою в Компанію.</li>';
            parentId = 'messages';
            showInBlockTop(parentId, blockContent);
        }

        function photoCounterUp() {
            var counterElement = $('#photoCounter');
            counterElement.text(parseInt(counterElement.text()) + 1);
        }

        function photoCounterDown() {
            var counterElement = $('#photoCounter');
            counterElement.text(parseInt(counterElement.text()) - 1);
        }

        function getPhotoStatusSettings(status, type) {
            var values = {
                waiting: {css: 'fas fa-hourglass-half fa-2x', color: 'orange', description: 'Фото очікує на перевірку'},
                approved: {css: 'fas fa-smile fa-2x', color: 'green', description: 'Фото прийняте'},
                unapproved: {
                    css: 'fas fa-meh fa-2x',
                    color: 'red',
                    description: 'Фото не відповідає критеріям класу з краси. Завантажте інше фото.'
                }
            };
            return values[status][type];
        }

        function processUpload(e) {
            $('#addButton').css('background-color', '#f6861f');
            var files = e.target.files;
            var data = {};
            $('#messages').html('');

            for (var i = 0, file; file = files[i]; i++) {
                var reader = new FileReader();
                reader.onload = (function (theFile) {
                    $("#loading").show();
                    var fileName = escape(theFile.name);
                    var shortFileName = fileName.length > 20 ? fileName.substring(0, 20) + '...' : fileName;

                    if(theFile.size > 10485760) {
                        blockContent = '<li>Розмір файла [' + shortFileName + '] перевищує встановлене обмеження 10MB.</li>';
                        parentId = 'messages';
                        showInBlockTop(parentId, blockContent);
                        $("#loading").hide();
                        return;
                    }

                    if (!theFile.type.match('image.*')) {
                        blockContent = '<li>Файл [' + shortFileName + '] не є зображенням.</li>';
                        parentId = 'messages';
                        showInBlockTop(parentId, blockContent);
                        $("#loading").hide();
                        return;
                    }

                    return function (e) {
                        var postData = {
                            consultantNumber: '<?= $consultantNumber?>',
                            token: '<?= $sessionToken?>',
                            fileName: fileName,
                            file: e.target.result,
                        };
                        $.post('upload.php', postData)
                            .done(function(data) {
                                $("#loading").hide();
                                if(!$.isEmptyObject(data)) {
                                    var response = JSON.parse(data);
                                    switch (response.status) {
                                        case 'success':
                                            blockContent = '<div class="mycontainer preview" data-toggle="modal" data-target="#myModal">' +
                                                '<img class="img-thumbnail image"  src="' + response.filePath + '" title="' + fileName + '" '+
                                                'data-id="' + response.fileId + '" data-status="waiting" data-delete-allowed="true"/>' +
                                                '<span style="color: orange;"><i class="markitem-clock ' + getPhotoStatusSettings('waiting', 'css')+ '"></i></span></div>';
                                            parentId = 'outputMulti';
                                            photoCounterUp();
                                            break;
                                        case 'exists-in-base':
                                            blockContent = '<li>Фото [' + shortFileName + '] вже було надіслане до Компанії раніше іншим Консультантом.</li>';
                                            parentId = 'messages';
                                            break;
                                        case 'exists-in-profile':
                                            blockContent = '<li>Ви вже надсилали фото [' + shortFileName + '] раніше.</li>';
                                            parentId = 'messages';
                                            break;
                                        default:
                                            showStandardError();
                                            return;
                                    }
                                    showInBlockTop(parentId, blockContent);
                                    $('.preview').on('click', function(e) {
                                        fillModalWindow(e);
                                    });
                                    return;
                                }
                                showStandardError();
                            })
                            .fail(function() {
                                $("#loading").hide();
                                showStandardError();
                            });
                    };
                })(file);

                reader.readAsDataURL(file);
            }
        }

        $('#fileMulti').on('change', function(e) {
            processUpload(e);
        });

        function fillModalWindow(e) {
            var targetImage = e.target;
            var photoLink = targetImage.getAttribute('src');
            var photoStatus = targetImage.getAttribute('data-status');
            var photoId = targetImage.getAttribute('data-id');
            var deleteAllowed = targetImage.getAttribute('data-delete-allowed') === 'false';
            $('#modalThumbnail').attr('src', photoLink);
            $('#modalWatermarkSpan').attr('style', 'color: ' + getPhotoStatusSettings(photoStatus, 'color'));
            $('#modalWatermark').attr('class', getPhotoStatusSettings(photoStatus, 'css'));
            $('#modalDescription').text(getPhotoStatusSettings(photoStatus, 'description'));
            $('#modalDeleteButton').prop('disabled', deleteAllowed);
            $('#modalDeleteButton').attr('data-id', photoId);
        }
        $('.preview').on('click', function(e) {
            fillModalWindow(e);
        });

        $('#modalDeleteButton').on('click', function(e) {
            var imageId = e.target.getAttribute('data-id');
            var postData = {consultantNumber: '<?= $consultantNumber?>', token: '<?= $sessionToken?>', fileId: imageId}
            $.post('delete.php', postData)
                .done(function(data) {
                    if(!$.isEmptyObject(data)) {
                        var result = JSON.parse(data);
                        if(result.status == 'success') {
                            photoCounterDown();
                            $('.img-thumbnail[data-id=' + imageId + ']').parent().remove();
                            $('#fileMulti').val('');
                            $('#modalExitButton').click();
                        }
                        return;
                    }
                    showStandardError();
                })
                .fail(function() {
                    showStandardError();
                });
        });
    })
</script>