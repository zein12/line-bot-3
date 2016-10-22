<?php
/**
 * Created by PhpStorm.
 * User: kenzo
 * Date: 2016/10/23
 * Time: 0:34
 */

use LINE\LINEBot\Event\BeaconDetectionEvent;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\JoinEvent;
use LINE\LINEBot\Event\LeaveEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\VideoMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Event\UnfollowEvent;

define("LINE_MESSAGING_API_CHANNEL_SECRET", '7ce26fb54a53c4d258e855b7f49d0c37');
define("LINE_MESSAGING_API_CHANNEL_TOKEN", 'WGI2sHZrfhY94b4HurfkPmIINGpRw47EqBz3iJHOXtnloBAnZPlo6X293XM1RIUYuGMZJFWzCTXQkUvtDs7WKh7CbmbICOcOmp4m+ets5UMbOHPDerVwO+dlTagr7EaWQRTxuzM4z/dj1+z6jSwfWgdB04t89/1O/w1cDnyilFU=ISSUE');

require __DIR__."/../vendor/autoload.php";

$bot = new \LINE\LINEBot(
    new \LINE\LINEBot\HTTPClient\CurlHTTPClient(LINE_MESSAGING_API_CHANNEL_TOKEN),
    ['channelSecret' => LINE_MESSAGING_API_CHANNEL_SECRET]
);

//エラー処理
if(!isset($_SERVER["HTTP_".\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE])){
    error_log("エラーです");
    responseBadRequest("誤ったリクエストです");
}

function responseBadRequest($reason){
    http_response_code(400);
    echo 'Bad request'.$reason;
    exit;
}

$signature = $_SERVER["HTTP_".\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
$body = file_get_contents("php://input");


//イベントごとに場合分け
$events = $bot->parseEventRequest($body, $signature);

foreach ($events as $event) {
    if ($event instanceof TextMessage) {
        $reply_token = $event->getReplyToken();
        $text = $event->getText();
        $bot->replyText($reply_token, $text);
    }elseif ($event instanceof StickerMessage){
        $reply_token = $event->getReplyToken();
        $sticker_id = $event->getStickerId();
        $package_id = $event->getPackageId();

        $sticker_builder = new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder($package_id,$sticker_id);

        $bot->replyMessage($reply_token,$sticker_builder);


    }elseif ($event instanceof VideoMessage){
        $reply_token = $event->getReplyToken();
        $message_id = $event->getMessageId();

        $response = $bot->getMessageContent($message_id);
        if ($response->isSucceeded()) {
            $videourl = __DIR__.'/../video/sample.mp4';
            $videosource = fopen($videourl,'a');
            fwrite($videosource, $response->getRawBody());
            fclose($videosource);
        } else {
            error_log($response->getHTTPStatus() . ' ' . $response->getBody());
        }


        $contenturl = "https://line-bot0202.herokuapp.com/video/pen.mp4";
        $imageurl = "https://cdn-images-1.medium.com/max/800/1*BUWSUWN8817VsQvuUNeBpA.jpeg";

        $video_builder = new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder($contenturl,$imageurl);
        $bot ->replyMessage($reply_token,$video_builder);

    }elseif($event instanceof LocationMessage){
        $reply_token = $event->getReplyToken();
        $title =  "my location";
        $address =  "〒150-0002 東京都渋谷区渋谷２丁目２１−１";
        $latitude = 35.65910807942215;
        $longitude = 139.70372892916203;
        $location_builder = new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title,$address,$latitude,$longitude);

        $bot->replyMessage($reply_token,$location_builder);

    }elseif($event instanceof AudioMessage){


    }elseif($event instanceof ImageMessage){

    }elseif ($event instanceof \LINE\LINEBot\Event\FollowEvent) {

        $profile_data = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
        error_log("8P BOT FOLLOWED: {$event->getUserId()}: {$profile_data['displayName']}");
        $reply_token = $event->getReplyToken();
        $bot->replyText($reply_token, "友達してくれてありがとう！！".$profile_data['displayName']);
    }


}

echo "OK";