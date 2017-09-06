<?php

require('vendor/autoload.php');
use houdunwang\xml;

$w = new wechatCallbackapiTest();
//$w->valid();
$w->responseMsg();

function request_data($type,$ind,$dval=''){
    $type=strtolower($type);
    if(empty($ind) || !is_string($ind)){
        return $dval;
    }
    if($type=='get'){
        return (isset($_GET[$ind])?$_GET[$ind]:$dval);
    }
    elseif($type=='post'){
        return (isset($_POST[$ind])?$_POST[$ind]:$dval);
    }
    return $dval;
}

class wechatCallbackapiTest
{
    private $appid='';
    private $secret='';
    private $token = '';

    private $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
    private $imageTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
            <MediaId><![CDATA[%s]]></MediaId>
            </Image>
            </xml>";
    private $voiceTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[voice]]></MsgType>
            <Voice>
            <MediaId><![CDATA[%s]]></MediaId>
            </Voice>
            </xml>";
    private $msgTpl='';
    //验证流程开始
    private function checkSignature()
    {
        $signature = request_data('get','signature');
        $timestamp = request_data('get','timestamp');
        $nonce = request_data('get','nonce');
        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function valid()
    {
        $echoStr = request_data('get','echostr');
        if($this->checkSignature()){
          exit($echoStr);
        }
    }

  public function responseMsg()
  {
      /*
      HTTP_RAW_POST_DATA这种获取POST数据流的方式只能在低于5.6版本
      的PHP上运行，这里使用的PHP版本是7.0
      $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
      */
      
      $postStr = file_get_contents('php://input', 'r');
    	//extract post data
      if (!empty($postStr)){
          //$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
          $wxMsgXml = houdunwang\xml\Xml::toArray($postStr);
          $wxMsg=$wxMsgXml['xml'];
          $tm=time();
          //消息处理函数
          $ret=$this->preMsgHandle($wxMsg);
          $resultStr='';
          $msg_tpl = $this->msgTpl;
          $resultStr = sprintf($this->$msg_tpl,
              $wxMsg['FromUserName']['@cdata'],
              $wxMsg['ToUserName']['@cdata'],$tm,$ret);
          exit($resultStr);
      }else {
      	exit('');
      }
  }

  private function preMsgHandle($msg){
    $content='';
    $this->msgTpl = $msg['MsgType']['@cdata'] . 'Tpl';
    switch($msg['MsgType']['@cdata']){
        case 'text':
            $content=trim($msg['Content']['@cdata']);
            return $this->textMsgHandle($content);
            break;
        case 'image':
        case 'voice':
            return $msg['MediaId']['@cdata'];
            break;
        case 'video':
            break;
        case 'event':
            $event_log = $msg['Event']['@cdata']."\n";
            file_put_contents('wx_event.log', $event_log,FILE_APPEND);
            return $this->eventHandle($msg);
        default:
            return '';
    }
  }

  private function send_text_msg($from_user,$to_user,$time,$info){
    $resultStr = sprintf($this->textTpl,$from_user,$to_user,$time,$info);
    exit($resultStr);
  }

  private function eventHandle($msg){
    switch($msg['Event']['@cdata']){
      case 'subscribe':
          $this->send_text_msg(
              $msg['FromUserName']['@cdata'],
              $msg['ToUserName']['@cdata'],
              time(),
              '欢迎关注测试号');
        break;
      case 'unsubscribe':
        break;
      case 'LOCATION':
        break;
      case 'CLICK':
        break;
      case 'VIEW':
        file_put_contents('wx_event.log', 'VIEW:'.$msg['FromUserName']['@cdata']."\n");
        break;
    }
  }

  private function textMsgHandle($t){
    $ret='';
    $ins_list = explode(' ',$t);
    
    switch(strtolower($ins_list[0])){
        case 'hello':
            $ret='Hey!';
            break;
        case 'help':
        case '?':
        case '？':
            return $this->help();
        case 'info':
            return $this->myinfo();
            break;
        default:
            $ret=$t;
    }
    return $ret;
  }
    
  private function help(){
      return "输入指令可以获得信息\n".
          "help,HELP,Help,?\n获取帮助信息。\n\n".
          "hello,Hello,HELLO\nsay Hello to system\n\n".
          "info\n查看公众号运营者的信息\n".
          "";
  }
  
  private function myinfo(){
    return "王勇\n".
      "1146040444@qq.com\n".
      "13223439296\n".
      "The master programmer.\n".
      "Design and devlop some php web system in my spare time.\n".
      "Skill: PHP,Python,Linux...";
  }

}
