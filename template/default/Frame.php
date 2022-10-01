<?php
/**
 * Author：晴玖天
 * Creation：2020/8/10 14:46
 * Filename：Frame.php
 * 框架模式
 */
$bgclass = background::image() == false ? 'background:#EEE;' : background::image();
if (empty($_QET['Url'])) header("Location:" . ROOT_DIR_S . "/");
$Curl = xiaochu_de($_QET['Url']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $conf['sitename'] . ($conf['title'] == '' ? '' : ' - ' . $conf['title']) ?></title>
    <meta name="keywords" content="<?= $conf['keywords'] ?>">
    <meta name="description" content="<?= $conf['description'] ?>">
</head>
<style>
    body {
    <?=$bgclass?>;
        padding: 0;
        margin: 0;
    }

    .IFrame {
        width: 375px;
        height: 750px;
        max-height: 90vh;
        margin: auto;
        margin-top: 4vh;
        background-color: #FFF;
        border: none;
        overflow: hidden;
        box-shadow: 6px 6px 16px 2px #464646;
    }

</style>
<body style="text-align: center">
<iframe class="IFrame" src="<?= (empty($Curl) ? '/' : $Curl) ?>"></iframe>
</body>
</html>
