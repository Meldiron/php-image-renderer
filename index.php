<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Prague');

// 3004 = 30.4
$dt = new DateTime('tomorrow');

$d = $_REQUEST['d'] ?? '';
if(!empty($d)) {
    $month = substr($d, 0, 2);
    $day = substr($d, 2, 2);
    $dt = new DateTime($dt->format('o') . '-' . $month . '-' . $day);
}

$date = $dt->format('dm');

$weekDay = $dt->format('N');
$weekDayVerbose = match ($weekDay) {
    '1' => 'pondělí',
    '2' => 'úterý',
    '3' => 'středa',
    '4' => 'čtvrtek',
    '5' => 'pátek',
    '6' => 'sobota',
    '7' => 'neděle',
    default => ''
};

$dayVerbose = $dt->format('d.m.');
$yearVerbose = $dt->format('o');

$getNameDay = function() use ($date) {
    $curl = curl_init('https://svatky.adresa.info/json?date=' . $date . '&lang=cs');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    $json = json_decode($response, true);
    return $json[0]['name'] ?? '';
};

$name = $getNameDay();

$image = new Imagick($dayVerbose === '26.07.' ? 'bg-celebrate.png' : 'bg.png');

if(!empty($name)) {
    $draw = new ImagickDraw();
    $draw->setFont('fonts/Inter/Inter-SemiBold.ttf');
    $draw->setFontSize(68);
    $draw->setFontWeight(600);
    $draw->setFillColor('#FFB9CC');
    $draw->setTextAlignment(Imagick::ALIGN_CENTER);
    $text = "SVÁTEK MÁ " . mb_strtoupper($name);
    
    $image->annotateImage($draw, $image->getImageWidth() /2, 1110, 0, $text);
}

if(!empty($weekDayVerbose)) {
    $draw = new ImagickDraw();
    $draw->setFont('fonts/Inter/Inter-Bold.ttf');
    $draw->setFontSize(64);
    $draw->setFontWeight(700);
    $draw->setTextKerning(5);
    $draw->setFillColor('#C5C5C5');
    $draw->setTextAlignment(Imagick::ALIGN_LEFT);
    $text = mb_strtoupper($weekDayVerbose);
    
    $image->annotateImage($draw, 96, 250, 0, $text);
}

// Day, Month
$draw = new ImagickDraw();
$draw->setFont('fonts/Poppins/Poppins-Bold.ttf');
$draw->setFontSize(56);
$draw->setFontWeight(700);
$draw->setTextKerning(10);
$draw->setFillColor('#FFFFFF');
$draw->setTextAlignment(Imagick::ALIGN_LEFT);
$text = $dayVerbose;
$image->annotateImage($draw, 204, 160, 0, $text);

// Year 
$metrics = $image->queryFontMetrics($draw, $text);
$draw = new ImagickDraw();
$draw->setFont('fonts/Poppins/Poppins-Medium.ttf');
$draw->setFontSize(56);
$draw->setFontWeight(400);
$draw->setTextKerning(10);
$draw->setFillColor('#FFFFFF');
$draw->setTextAlignment(Imagick::ALIGN_LEFT);
$text = $yearVerbose;
$image->annotateImage($draw, 204 + $metrics['textWidth'] + 14, 160, 0, $text);


header('Content-Type: image/jpeg');

echo $image;

$image->destroy();