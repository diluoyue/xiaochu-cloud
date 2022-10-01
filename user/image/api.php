<?php

use extend\ImgThumbnail;

include '../../includes/fun.global.php';
$id = isset($_QET['ids']) ? (int)$_QET['ids'] : die;
$url = isset($_QET['url']) ? trim($_QET['url']) : die;
$url = base64_decode($url);
$images = isset($_QET['images']) ? trim($_QET['images']) : die;
if ($id < 1 || $id > 13) die;
header('Content-type: image/jpeg');
header("Content-Disposition: attachment; filename=$images");
$backgrounds = array('1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg', '9.jpg', '10.jpg', '11.jpg', '12.jpg', '13.jpg');
$imagesx = array(630, 640, 348, 348, 650, 630, 648, 130, 605, 635, 268, 170, 260);
$imagesy = array(150, 153, 120, 120, 90, 165, 230, 110, 196, 180, 84, 280, 167);
$imagesize = array(200, 180, 240, 230, 140, 165, 190, 280, 180, 140, 180, 85, 145);

$path_1 = './img/' . $backgrounds[$id - 1];
$image_1 = imagecreatefromjpeg($path_1);
$Image = QR_Code(1, $url, 10, 1, ROOT . 'includes/extend/log/ShopImage/' . md5($url) . '.png');
new ImgThumbnail($Image, $imagesize[$id - 1], $Image);
$image_2 = imagecreatefrompng($Image);
imagecopymerge($image_1, $image_2, $imagesx[$id - 1], $imagesy[$id - 1], 0, 0, imagesx($image_2), imagesy($image_2), 100);
imagepng($image_1);