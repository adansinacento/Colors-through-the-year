<?php
/*
* Bot that tweets a different color everyday (@everydaycolor)
* AdanSinAcento 2018
* When in doubt -> HMU @Adansinacento
* this code uses Codebird.
*/
function getColor($item, $maxitem) { // Recibes a day and the number of days and returns the appropiate color as a hex String
    $phase = 0;
    $center = 128;
    $width = 127;
    $frequency = 3.1415926535*2/$maxitem;
    $red   = sin($frequency*$item+2+$phase) * $width + $center;
    $green = sin($frequency*$item+0+$phase) * $width + $center;
    $blue  = sin($frequency*$item+4+$phase) * $width + $center;
    return RGB2Color($red,$green,$blue);
}
function hexColorAllocate($im,$hex){
    $hex = ltrim($hex,'#');
    $a = hexdec(substr($hex,0,2));
    $b = hexdec(substr($hex,2,2));
    $c = hexdec(substr($hex,4,2));
    return imagecolorallocate($im, $a, $b, $c);
}
function RGB2Color($r,$g,$b) {
    $r = dechex($r);
    $g = dechex($g);
    $b = dechex($b);
    return '#'.PadZeroLeft($r).PadZeroLeft($g).PadZeroLeft($b);
}
function PadZeroLeft($str){
    return str_pad($str, 2, "0", STR_PAD_LEFT);
}
function cal_days_in_year(){
    $year = date('y');
    $days=0;
    for($month=1;$month<=12;$month++){
        $days = $days + cal_days_in_month(CAL_GREGORIAN,$month,$year);
    }
    return $days;
}
require_once('codebird.php'); //CodeBird file inclusion
\Codebird\Codebird::setConsumerKey("KEY", "SECRET KEY");
$cb = \Codebird\Codebird::getInstance();
$cb->setToken("TOKEN", "SECRET TOKEN"); //CodeBird Initialization
$filename = 'date.png';
$max = cal_days_in_year(); //number of days in current year
$day = date('z'); //current day (0 to 365)
$colorHex = getColor($day, $max); //get color
//create the image
$image = imagecreatetruecolor(600, 600);
//set background to $color
$color = hexColorAllocate($image, $colorHex);
imagefill($image, 0, 0, $color);
imagepng($image, $filename);
imagedestroy($image);
$media_ids = [];
$reply = $cb->media_upload([
    'media' => $filename
]);
// fetch the media ids, so we can use them in the tweets
$media_ids[] = $reply->media_id_string;
$media_ids = implode(',', $media_ids); // Converts the resulting Array into a single String
$params = [ // params for the tweet
    'status'                => date('d/m')." ".str_replace('#', '0x', $colorHex),
    'media_ids'             => $media_ids, // string with the media files
];
$reply = $cb->statuses_update($params); // Sends the tweet
unlink($filename); //delete file
?>
