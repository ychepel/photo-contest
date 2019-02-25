<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta property="og:type" content="article" />
    <meta property="og:title" content="Mary Kay - Марафон класів «Вітамінний клас»: результати БГ" />
    <meta property="og:image" content="template/img/header_croped_web.png" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
          integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/"
          crossorigin="anonymous">
    <link rel="stylesheet" href="template/css/style.css">
    <link rel="stylesheet" href="template/css/table.css">
    <title>Марафон класів «Вітамінний клас»- результати БГ</title>
</head>

<body>
<div class="myheader">
    <div class=" headerblock"></div>
    <img class="logoimg" src="template/img/logo.png" alt="MARY KAY LOGO">
</div>
<div class="headermainphoto">
    <img src="template/img/header_croped_web.png" alt="Логотип Виклику">
</div>
<div class="mainblock">
    <div>
        <img class="orangewave" src="template/img/wave.png" alt=""></div>

        <div class="row main-content">
            <div class="col">
                <div class="whiteblock">
                        <h5><?= $consultant->mailingName ?>, </h5>
                        <p> тут ви можете відстежувати участь Консультантів вашої Бізнес-Групи у марафоні</p>
                        <div class="row justify-content-center">
                            <div class="col">
                                <table id="list" class="resultsTable dataTable">
                                    <thead>
                                        <tr>
                                            <th>Конс.№</th>
                                            <th>ПІ</th>
                                            <th>Класи</th>
                                            <th>Смужки</th>
                                        </tr>
                                    </thead>
                                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                                    <?php
                                        $totalCount = 0;
                                        $totalPhotoCount = 0;
                                        foreach ($unitData as $key => $unitRecord) {
                                            echo '<tr class="odd">
                                                <td>'.$unitRecord->consultantNumber.'</td>
                                                <td>'.$unitRecord->consultantName.'</td>
                                                <td>'.$unitRecord->countPhoto.'</td>
                                                <td>'.($unitRecord->isPurchaser ? 'Так' : 'Ні').'</td>
                                            </tr>';
                                            $totalCount++;
                                            $totalPhotoCount += $unitRecord->countPhoto;
                                        }
                                    ?>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>Всього (<?= $totalCount?>)</th>
                                        <th><?= $totalPhotoCount?></th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class=".col-md-4 .offset-md-4 inputbox">
                            <div class="file_upload">
                                <div class="addimg">
                                    <a href="index.php<?= isset($_GET['debug']) ? '?debug='.$_GET['debug']: '' ?>">
                                        <p>На головну </p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
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
</body>

</html>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).ready(function(){
            $("#flip").click(function(){
                $("#panel").toggle("fast", function(){
                    window.scrollTo(500, 2000);
                });
            });
        });

        $('#list').dataTable( {
            "bPaginate": false,
            "bInfo": false,
            "bAutoWidth": false,
            "bJQueryUI": false,
            "columnDefs": [
                { "orderable": false, "targets": 0 }
            ],
            "aaSorting": [[ 1, "asc" ]],
            "language": {
                "emptyTable": "Наразі відсутня інформація про Консультантів, що беруть участь у Виклику",
                "zeroRecords": "Пошук не дав результатів"
            }
        });
        $("#list_filter").hide();
    });
</script>