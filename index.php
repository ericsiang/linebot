<?php

include('class_pdo.php');
include('config.php');

$action=isset($_GET['action']) &&trim($_GET['action']) ? $_GET['action'] : '' ;

$post_url='https://'.$_SERVER['HTTP_HOST'].'/index.php';

function PostJsonCurl($ChannelAccessToken,$url,$postData){
        // 傳送json訊息
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $ChannelAccessToken
        ]);
        $Result = curl_exec($ch);
        //var_dump($Result);
        curl_close($ch);
}


function GetJsonCurl($ChannelAccessToken,$url){
    // 傳送json訊息
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $ChannelAccessToken
    ]);
    $Result = curl_exec($ch);
    var_dump($Result);
    curl_close($ch);
}

//連接資料庫
$pdo=new PdoConnect($db_config);

//讀取JSON資訊 
$HttpRequestBody = file_get_contents('php://input'); 


if(!empty($_SERVER['HTTP_X_LINE_SIGNATURE'])){
    $HeaderSignature = $_SERVER['HTTP_X_LINE_SIGNATURE']; 
    
    //驗證來源是否是LINE官方伺服器 
    $Hash = hash_hmac('sha256', $HttpRequestBody, $ChannelSecret, true); 
    $HashSignature = base64_encode($Hash); 
    if($HashSignature != $HeaderSignature) 
    { 
        die('hash error!'); 
    }
    

    $data=json_decode($HttpRequestBody,true);
    
    //新增紀錄
    $insert_data=[
        'replyToken'=>$data['events'][0]['replyToken'],
        'userId'=>$data['events'][0]['source']['userId'],
        'id'=>$data['events'][0]['message']['id'],
        'data'=>$HttpRequestBody,
        'timestamp'=>$data['events'][0]['timestamp'],
        'create_time'=>time(),
    ];
    //var_dump($insert_data);
    $id=$pdo->table('get_event_list')->insert($insert_data);


    //輸出 
    //file_put_contents('log.txt', $HttpRequestBody); 

    //對方傳的message類別
    $action_type=$data['events'][0]['message']['type'];
    //對方傳的文字
    $action_text=$data['events'][0]['message']['text'];
    $message_type=$data['events'][0]['message']['type'];

    //新增data log紀錄
    $pdo->table('`response_list`')->where('`action`="'.$action_text.'"')->field('*')->select();
    //echo $pdo->sql;
    $res=$pdo->query($pdo->sql);

    if(!empty($res[0])){
        $response=$res[0]['response'];
    }else{
        $response=$action_text;
    }

    if($message_type!='text'){
        $response='哼! 欺負我只能打字';
    }
    
    //逐一執行事件
    foreach($data['events'] as $Event)
    {
        //當bot收到任何訊息
        if($Event['type'] == 'message')
        {   
            
            if(strtolower($action_type)=='image' || $action_text=='圖片' || $action_text=='柴犬'){
                $image_url_arr=[
                    'https://www.bomb01.com/upload/news/original/584985332f9f30dc0358deb1d86c2a5d.jpg',
                    'https://img.ltn.com.tw/Upload/partner/page/2019/09/14/190914-4886-01-WvNZA.jpg',
                    'https://i1.kknews.cc/SIG=2ru26a9/ctp-vzntr/15301131549198023s8q5n0.jpg',
                    'https://cdn2-digiphoto.techbang.com/system/images/124905/medium/45dbba2f981c35f27610a6191036309d.jpg?1548392392',
                    'https://cdn2-digiphoto.techbang.com/system/images/124904/medium/1a7d862bb16aa53fecb82448d2181da6.jpg?1548392391',
                    'https://cdn1-digiphoto.techbang.com/system/images/124900/medium/a1740120003e749928689edd861f9789.jpg?1548392387',
                ];
                
                if($action_text=='柴犬'){
                    $rand=mt_rand(0,2);
                }else{
                    $rand=mt_rand(0,6);
                }
               

                //回傳圖片
                $Payload = [
                    'replyToken' => $Event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'image', // 訊息類型 (圖片)
                             //回覆圖片的URL
                            'originalContentUrl' => $image_url_arr[$rand],
                            //回覆預覽圖片的URL
                            'previewImageUrl' => $image_url_arr[$rand],
                        ]
                    ]
                ];
            }else if (strtolower($action_type) == "sticker" || $action_text == "貼圖" || $action_text == "貼紙") {
                //回傳貼圖
                /*
                    packageId=11538 => rand(51626494,51626533)
                    packageId=11537 => rand(52002734,52002773)
                    packageId=1 => rand(1,139)
                      
                */ 

                $Payload = [
                    'replyToken' => $Event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'sticker', // 訊息類型 (貼圖)
                            'packageId' => 11537, // 貼圖包 ID
                            'stickerId' => rand(52002734,52002773) // 貼圖 ID
                        ]
                    ]
                ];
                
            }elseif (strtolower($action_type) == "video" || $action_text == "視頻" || $action_text == "影片" || strtolower($action_text) == "video"){  
                //回傳影片
                $Payload = [
                    'replyToken' => $Event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'video', // 訊息類型 (影片)
                            'originalContentUrl' => 'https://sideproject-linechatbot.ericsiang.club/video/chang.mp4', // 回復影片
                            'previewImageUrl' => 'https://img.ltn.com.tw/Upload/partner/page/2019/09/14/190914-4886-01-WvNZA.jpg' // 回復的預覽圖片
                        ]
                    ]
                ];
            }else if(strtolower($action_text) == "location" || $action_text == "地址" || $action_text == "位置" || $action_text == "定位"){
                //回傳定位，經緯度一定要帶
                $Payload = [
                    'replyToken' => $Event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'location', // 訊息類型 (位置)
                            'title' => '台北小巨蛋', // 回復標題
                            'address' => '105台北市松山區南京東路四段2號', // 回復地址
                            'latitude' => 25.051297, // 地址緯度
                            'longitude' => 121.549878 // 地址經度
            
                        ]
                    ]
                ];
            }else{
                //回傳文字
                $Payload = [
                    'replyToken' => $Event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $response
                        ]
                    ]
                ];
            }

            //https://api.line.me/v2/bot/message/reply 回應API網址
            PostJsonCurl($ChannelAccessToken,'https://api.line.me/v2/bot/message/reply',$Payload);

        }
    }
}


//推播(全部加入此LINE的人都會收到)
if($action=="broadcast"){
    if(!empty($_GET['text'])){
        $text=$_GET['text'];
    }else{
        $text='大家好!';
    }

    $Payload = [
        'messages' => [
            [
                'type' => 'text',
                'text' => $text
            ]
        ]
    ];
    
    PostJsonCurl($ChannelAccessToken,'https://api.line.me/v2/bot/message/broadcast',$Payload);
}

//單一user推播
if($action=='single_push'){
    if(!empty($_GET['text'])){
        $text=$_GET['text'];
    }else{
        $text='大家好!';
    }

    if(!empty($_GET['user_id'])){
        $user_id=$_GET['user_id'];
    }else{
        $user_id='U7ef1b40ca2dbe10ff5996864b4ad9b00';
    }

    $Payload = [
        'to'=>$user_id,
        'messages' => [
            [
                'type' => 'text',
                'text' => $text
            ]
        ]
    ];


    PostJsonCurl($ChannelAccessToken,'https://api.line.me/v2/bot/message/push',$Payload);
}


//GetJsonCurl($ChannelAccessToken,'https://api.line.me/v2/bot/profile/U7ef1b40ca2dbe10ff5996864b4ad9b00');

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="<?php echo  $post_url; ?>" method="get">
        <input type="radio" name="action" id="single_push" value="single_push">單發
        <input type="radio" name="action" id="broadcast" value="broadcast">群發
        <br>
        <input type="text" name="text" id="text" >
        <input type="submit"  value="send">
    </form>
</body>

</html>