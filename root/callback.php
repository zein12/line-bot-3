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

define("LINE_MESSAGING_API_CHANNEL_SECRET", 'a6b4b1a80d9f25eb0a719fc92cef7d86');
define("LINE_MESSAGING_API_CHANNEL_TOKEN", '3/cEBpOR0mjAMUtnHKrSrx3N6FnMVNPYfXBIwMO6HNGaljxuxTxZz2fGrmZYFwqfV3dvAWMa7FEGrmOONfbZ7or1wxYgpjbtFMS0Mkk+RftjvYSrUpThxAHGiivf2M662z2zM5P8BSKby0dJiBG3GQdB04t89/1O/w1cDnyilFU=');

require __DIR__."/../vendor/autoload.php";
require __DIR__."/func.php";

$bot = new \LINE\LINEBot(
    new \LINE\LINEBot\HTTPClient\CurlHTTPClient(LINE_MESSAGING_API_CHANNEL_TOKEN),
    ['channelSecret' => LINE_MESSAGING_API_CHANNEL_SECRET]
);

//エラー処理
if(!isset($_SERVER["HTTP_".\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE])){
    error_log("kesalahan adalah");
    responseBadRequest("Permintaan salah");
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
        if (preg_match('/^beams$/i', $text)) {
            $fashion_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text."Tampak? ?");
            $shop_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Apakah apa jenis pakaian yang Anda jual? ?");
            $muiti_builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
            $muiti_builder->add($fashion_text);
            $muiti_builder->add($shop_text);
            $bot->replyMessage($reply_token,$muiti_builder);
        }elseif (preg_match('/pakaian/',$text)) {
            $fashion_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text."Atau seperti");
            $shop_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Kirim foto dari toko! !");
            $muiti_builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
            $muiti_builder->add($fashion_text);
            $muiti_builder->add($shop_text);
            $bot->replyMessage($reply_token,$muiti_builder);
        }else{
            $response = $bot->replyText($reply_token, $text);
        }
//        データベース接続するとき
//        if($response->isSucceeded()){
//            //テキスト送付が成功したら
//            $name = "岡野健三";
//            $img_url = "http://sample.co.jp";
//
//            //DBに挿入
//            $pdo = db_con();
//
//            $stmt = $pdo->prepare('INSERT INTO user (name,img_url) VALUES (:name,:img_url)');
//
//            $stmt->bindValue(":name",$name,PDO::PARAM_STR);
//            $stmt->bindValue(":img_url",$img_url,PDO::PARAM_STR);
//
//            $stmt->execute();
//        }


    }elseif ($event instanceof StickerMessage){
        $reply_token = $event->getReplyToken();
//        $sticker_id = $event->getStickerId();
//        $package_id = $event->getPackageId();
//
//        $sticker_builder = new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder($package_id,$sticker_id);

        $fashion_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Terima kasih banyak! ! Lihat Anda Salam! !");
        $muiti_builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        //確認ボタン
        // yes とは no はpostbackに格納されるデータ
        $yes_btn = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("ya","yes");
        $no_btn = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("tidak","no");
        $confirm = new LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder("Apakah Anda menulis artikel?",[$yes_btn,$no_btn]);
        $confirm_msg = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("Artikel hari ini",$confirm);
        $muiti_builder->add($fashion_text);
        $muiti_builder->add($confirm_msg);



        $bot->replyMessage($reply_token,$muiti_builder);



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
            error_log($response->getHTTPStatus() . ' ' . $response->getRawBody());
        }


        $contenturl = "https://line-bot0202.herokuapp.com/video/pen.mp4";
        $imageurl = "https://cdn-images-1.medium.com/max/800/1*BUWSUWN8817VsQvuUNeBpA.jpeg";

        $video_builder = new \LINE\LINEBot\MessageBuilder\VideoMessageBuilder($contenturl,$imageurl);
        $bot ->replyMessage($reply_token,$video_builder);

    }elseif($event instanceof LocationMessage){
        $reply_token = $event->getReplyToken();
        $title =  "my location";
        $address =  "〒150-0002 Tokyo, Shibuya-ku, Shibuya 2-chome, 21-1";
        $latitude = 35.65910807942215;
        $longitude = 139.70372892916203;
        $location_builder = new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title,$address,$latitude,$longitude);

        $bot->replyMessage($reply_token,$location_builder);

    }elseif($event instanceof AudioMessage){


    }elseif($event instanceof ImageMessage){
        $image_id = $event->getMessageId();
        $response = $bot->getMessageContent($image_id);

        if ($response->isSucceeded()) {
            $videourl = __DIR__.'/../img/sample.jpeg';
            $videosource = fopen($videourl,'a');
            fwrite($videosource, $response->getRawBody());
            fclose($videosource);
        } else {
            error_log($response->getHTTPStatus() . ' ' . $response->getRawBody());
        }




        $reply_token = $event->getReplyToken();
        $fashion_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Foto dikirim Terima kasih !!");
        $shop_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Akhirnya, itu merupakan saat perasaan di cap! !");
        $muiti_builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $muiti_builder->add($fashion_text);
        $muiti_builder->add($shop_text);
        $bot->replyMessage($reply_token,$muiti_builder);

//        $columns = [];
//        $items = [0,1,2];
//        foreach ($items as $item) {
//            $uriaction_builder = new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder("ここを押してね","https://cdn-images-1.medium.com/max/800/1*BUWSUWN8817VsQvuUNeBpA.jpeg");
//            $message_builder = new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder("ここを押してね","1を選ぶ");
//            $postback_builder = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("ここを押してね","3を選ぶ");
//
//
//            //カルーセルのカラムを作成する
//            $colunm = new LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder(
//                "今書いて欲しい記事",
//                "ここにあるものから選んでね！",
//                "https://cdn-images-1.medium.com/max/800/1*BUWSUWN8817VsQvuUNeBpA.jpeg",
//                [$uriaction_builder,$message_builder,$postback_builder]);
//
//            $columns[] =  $colunm;
//        }
//
//        $carouselbuilder = new LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columns);
//        $templatemessagebuilder = new LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("代わりのテキスト",$carouselbuilder);
//
//        //確認ボタン
//        // yes とは no はpostbackに格納されるデータ
//        $yes_btn = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("はい","yes");
//        $no_btn = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("いいえ","no");
//        $confirm = new LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder("今日は記事を書きますか？",[$yes_btn,$no_btn]);
//        $confirm_msg = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("今日の記事",$confirm);
//
//        $muiti_builder = new LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
//        $muiti_builder->add($templatemessagebuilder);
//        $muiti_builder->add($confirm_msg);
//        $bot->replyMessage($reply_token,$muiti_builder);
    }elseif ($event instanceof FollowEvent) {

        $profile_data = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
        error_log("8P BOT FOLLOWED: {$event->getUserId()}: {$profile_data['displayName']}");
        $reply_token = $event->getReplyToken();

        $text_builder1 = new LINE\LINEBot\MessageBuilder\TextMessageBuilder("Terima kasih untuk menambah teman! !".$profile_data['pictureUrl']);
        $text_builder2 = new LINE\LINEBot\MessageBuilder\TextMessageBuilder("Peppidayo ~ ~. Aku ingin kau mengatakan bersama-sama sehari-hari dan acara untuk semua orang! !");
        $text_builder3  = new LINE\LINEBot\MessageBuilder\TextMessageBuilder("Sekarang kita ingin dirangkum di sini");

        $image_builder = new LINE\LINEBot\MessageBuilder\ImageMessageBuilder("https://line-bot0202.herokuapp.com/img/peppi.jpeg","https://line-bot0202.herokuapp.com/img/peppi.jpeg");

        $columns = [];
        $items = [
            [
                "title" => "渋谷のオススメグルメ",
                "subtitle" => "渋谷で流行っているお店を教えて欲しいな",
                "img_url" => "https://d3ftecjsng6jy5.cloudfront.net/images/topic/1478/ce21c78040adc23e8594f9e854309f853bbc1d3f_56750a04314cf_p.jpeg"
            ],
            [
                "title" => "渋谷のオススメファッション",
                "subtitle" => "流行を先取り！！冬物コーデにオススメのお店を教えて欲しいな！",
                "img_url" => "https://cdn.top.tsite.jp/static/top/sys/contents_image/media_image/030/908/595/30908595_0.jpeg"
            ],
            [
                "title" => "渋谷のデートスポット",
                "subtitle" => "渋谷でデートするならこれ！！ってお店を教えて欲しいな",
                "img_url" => "https://fanblogs.jp/riko0723/file/image/image-a8d47.jpeg"
            ]
        ];

        foreach ($items as $item) {
            $message_builder = new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder("Lihat rincian","detail");
            $postback_builder = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("Menulis ringkasan ini","fashion");


            //カルーセルのカラムを作成する
            $colunm = new LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder(
                $item["title"],
                $item["subtitle"],
                $item["img_url"],
                [$message_builder,$postback_builder]);

            $columns[] =  $colunm;
        }

        $carouselbuilder = new LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columns);
        $templatemessagebuilder = new LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("Bukan teks",$carouselbuilder);

        $muiti_builder = new LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        $muiti_builder->add($text_builder1);
        $muiti_builder->add($text_builder2);
        $muiti_builder->add($image_builder);
        $muiti_builder->add($text_builder3);
        $muiti_builder->add($templatemessagebuilder);
        $bot->replyMessage($reply_token,$muiti_builder);

    }elseif ($event instanceof PostbackEvent){
        $query = $event->getPostbackData();
        if($query){
            parse_str($query,$data);
            if(isset($data["yes"])){
                $reply_token = $event->getReplyToken();

                $columns = [];
                $items = [
                    [
                        "title" => "渋谷のオススメグルメ",
                        "subtitle" => "渋谷で流行っているお店を教えて欲しいな",
                        "img_url" => "https://d3ftecjsng6jy5.cloudfront.net/images/topic/1478/ce21c78040adc23e8594f9e854309f853bbc1d3f_56750a04314cf_p.jpeg"
                    ],
                    [
                        "title" => "渋谷のオススメファッション",
                        "subtitle" => "流行を先取り！！冬物コーデにオススメのお店を教えて欲しいな！",
                        "img_url" => "https://cdn.top.tsite.jp/static/top/sys/contents_image/media_image/030/908/595/30908595_0.jpeg"
                    ],
                    [
                        "title" => "渋谷のデートスポット",
                        "subtitle" => "渋谷でデートするならこれ！！ってお店を教えて欲しいな",
                        "img_url" => "https://fanblogs.jp/riko0723/file/image/image-a8d47.jpeg"
                    ]
                ];

                foreach ($items as $item) {
                    $message_builder = new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder("詳細を見る","detail");
                    $postback_builder = new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("このまとめを書く","fashion");


                    //カルーセルのカラムを作成する
                    $colunm = new LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder(
                        $item["title"],
                        $item["subtitle"],
                        $item["img_url"],
                        [$message_builder,$postback_builder]);

                    $columns[] =  $colunm;
                }

                $carouselbuilder = new LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columns);
                $templatemessagebuilder = new LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("代わりのテキスト",$carouselbuilder);

                $muiti_builder = new LINE\LINEBot\MessageBuilder\MultiMessageBuilder();

                $text_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("はいを選択したよ");
                $muiti_builder->add($templatemessagebuilder);
                $muiti_builder->add($text_builder);

                $bot->replyMessage($reply_token,$muiti_builder);

            }elseif (isset($data["fashion"])){
                $reply_token = $event->getReplyToken();
                $fashion_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("ファッションだね！！今渋谷で流行しているファッションを教えて欲しいな");
                $shop_text = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("お店の名前は？？");
                $muiti_builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
                $muiti_builder->add($fashion_text);
                $muiti_builder->add($shop_text);
                $bot->replyMessage($reply_token,$muiti_builder);
            }
        }
    }


}

echo "OK";
