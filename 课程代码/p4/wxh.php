<?php

require('./wxcrypt/wxBizMsgCrypt.php');

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
    private $encodingAesKey='';
    private $token = 'mytest';

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
    //加密过程用到的变量
    private $timestamp='';
    private $nonce='';
    private $msg_sign='';
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
  //end

  //加密模式下对获取的数据进行解密
  private function getDecryptMsg($postdata){
    $pc = new WXBizMsgCrypt($this->token,$this->encodingAesKey,$this->appid);
    $this->msg_sign = request_data('get','msg_signature','');
    $this->timestamp = request_data('get','timestamp','');
    $this->nonce=request_data('get','nonce','');
    $msg='';
    $errCode = $pc->decryptMsg(
                  $this->msg_sign,
                  $this->timestamp,
                  $this->nonce,
                  $postdata,$msg);
    if ($errCode == 0) {
      return $msg;
    } else {
      return false;
    }
  }

  //加密模式下，对要发送的数据进行加密
  private function mkEncryptMsg($ret_data){
    $pc = new WXBizMsgCrypt($this->token,$this->encodingAesKey,$this->appid);
    $encryptMsg = '';
    $errCode = $pc->encryptMsg(
                  $ret_data,
                  $this->timestamp,
                  $this->nonce,
                  $encryptMsg);
    if ($errCode == 0) {
      return $encryptMsg;
    } else {
      return false;
    }
  }

  //格式化要回复的数据
  private function formatRetData($encryptMsg,$toUser){
    $xml_tree = new DOMDocument();
    $xml_tree->loadXML($encryptMsg);
    $array_e = $xml_tree->getElementsByTagName('Encrypt');
    $array_s = $xml_tree->getElementsByTagName('MsgSignature');
    $encrypt = $array_e->item(0)->nodeValue;
    $msg_sign = $array_s->item(0)->nodeValue;

    $format = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
            <MsgSignature><![CDATA[%s]]></MsgSignature>
            <TimeStamp><![CDATA[%s]]></TimeStamp>
            <Nonce><![CDATA[%s]]></Nonce>
            <Encrypt><![CDATA[%s]]></Encrypt></xml>";
    $from_xml = sprintf($format,$toUser,$msg_sign,$this->timestamp,$this->nonce,$encrypt);
    return $from_xml;
  }

  public function responseMsg()
  {
      /*
      HTTP_RAW_POST_DATA这种获取POST数据流的方式只能在低于5.6版本
      的PHP上运行，这里使用的PHP版本是7.0
      $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
      */
      $postStr = file_get_contents('php://input', 'r');
      $from_user = request_data('get','openid','');
    	//extract post data
      if (!empty($postStr)){
        //记录加密数据
        file_put_contents('wxtest.log', $postStr,FILE_APPEND);
        //进行消息解密
        $post_msg = $this->getDecryptMsg($postStr);
        if(false===$post_msg){
          exit('');
        }
        $postObj = simplexml_load_string($post_msg, 'SimpleXMLElement', LIBXML_NOCDATA);
        //把PHP对象的变量转换成关联数组
        $wxmsg = get_object_vars($postObj);
        $tm=time();
        //消息处理函数
        $ret=$this->preMsgHandle($wxmsg);

        $resultStr='';
        $mtpl = $this->msgTpl;
        $resultStr = sprintf($this->$mtpl,$wxmsg['FromUserName'],$wxmsg['ToUserName'],$tm,$ret);
        $resultStr = $this->mkEncryptMsg($resultStr);
        $format_retdata = $this->formatRetData($resultStr,$from_user);
        exit($format_retdata);
      }else {
      	exit('post is empty');
      }
  }

  private function preMsgHandle($msg){
    $content='';
    $this->msgTpl = $msg['MsgType'] . 'Tpl';
    switch($msg['MsgType']){
        case 'text':
            $content=trim($msg['Content']);
            //return $content;
            return $this->textMsgHandle($content);
            break;
        case 'image':
        case 'voice':
            return $msg['MediaId'];
            break;
        case 'video':
            break;
        default:
          return '';
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
      "编码狂人\n兼职一些公司的系统开发工作\n".
      "PHP开发、Python开发···";
  }

}
