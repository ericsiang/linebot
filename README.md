# Line PHP聊天機器人
A simple for line bot

Line Developer後台操作網址 : <https://developers.line.biz/en/>  
Message API文件 : <https://developers.line.biz/en/reference/messaging-api/>  


## 1.這裡記得修改成你Line開發者帳號內的資訊，及DB連線設定  
## set your Line developer account info and DB Connection  
```php
//Line Developers Channel Secret and Channel Access Token
$ChannelSecret = '你的Channel secret'; 
$ChannelAccessToken = '你的Channel access token'; 
 
//DB連線設定
$db_config=[
    'dbHost'=>'127.0.0.1',
    'dbPort'=>'3306',
    'dbName'=>'',
    'dbUser'=>'',
    'dbPass'=>'',
    'charset'=>'utf-8',
];
```  
