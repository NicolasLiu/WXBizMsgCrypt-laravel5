<?php
namespace Nicolasliu\Wxbizmsgcrypt;

use Illuminate\Http\Request;

/**
 * 对企业微信发送给企业的消息加解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */


include_once "sha1.php";
include_once "xmlparse.php";
include_once "pkcs7Encoder.php";
include_once "errorCode.php";

/**
 * 1.第三方回复加密消息给企业微信；
 * 2.第三方收到企业微信发送的消息，验证消息的安全性，并对消息进行解密。
 */
class WXBizMsgCrypt
{
	private $m_sToken;
	private $m_sEncodingAesKey;
	private $m_sCorpid;

	private $sTimeStamp;
	private $sNonce;

	/**
	 * 构造函数
	 * @param $token string 企业微信后台，开发者设置的token
	 * @param $encodingAesKey string 企业微信后台，开发者设置的EncodingAESKey
	 * @param $Corpid string 企业的Corpid
	 */
	public function __construct($token, $encodingAesKey, $Corpid)
	{
		$this->m_sToken = $token;
		$this->m_sEncodingAesKey = $encodingAesKey;
		$this->m_sCorpid = $Corpid;
	}
	
    /*
	*验证URL
    *@param request Laravel请求
    *@return array [成功0，失败返回对应的错误码, echostr]
	*/
	public function VerifyURL(Request $request)
	{
        $sMsgSignature = $request->input('msg_signature');
        $this->sTimeStamp = $request->input('timestamp');
        $this->sNonce = $request->input('nonce');
        $sEchoStr = $request->input('echostr');

		if (strlen($this->m_sEncodingAesKey) != 43) {
			return [ErrorCode::$IllegalAesKey, ''];
		}

		$pc = new Prpcrypt($this->m_sEncodingAesKey);
		//verify msg_signature
		$sha1 = new SHA1;
		$array = $sha1->getSHA1($this->m_sToken, $this->sTimeStamp, $this->sNonce, $sEchoStr);
		$ret = $array[0];

		if ($ret != 0) {
			return [$ret, ''];
		}

		$signature = $array[1];
		if ($signature != $sMsgSignature) {
			return [ErrorCode::$ValidateSignatureError, ''];
		}

		$result = $pc->decrypt($sEchoStr, $this->m_sCorpid);
		if ($result[0] != 0) {
			return [$result[0], ''];
		}

		return [ErrorCode::$OK, $result[1]];
	}
	/**
	 * 将企业微信回复用户的消息加密打包.
	 * <ol>
	 *    <li>对要发送的消息进行AES-CBC加密</li>
	 *    <li>生成安全签名</li>
	 *    <li>将消息密文和安全签名打包成xml格式</li>
	 * </ol>
	 *
	 * @param $replyMsg string 企业微信待回复用户的消息，xml格式的字符串
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function EncryptMsg($sReplyMsg)
	{
		$pc = new Prpcrypt($this->m_sEncodingAesKey);

		//加密
		$array = $pc->encrypt($sReplyMsg, $this->m_sCorpid);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}

		if ($this->sTimeStamp == null) {
			$this->sTimeStamp = time();
		}
		$encrypt = $array[1];

		//生成安全签名
		$sha1 = new SHA1;
		$array = $sha1->getSHA1($this->m_sToken, $this->sTimeStamp, $this->sNonce, $encrypt);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}
		$signature = $array[1];

		//生成发送的xml
		$xmlparse = new XMLParse;
		$sEncryptMsg = $xmlparse->generate($encrypt, $signature, $this->sTimeStamp, $this->sNonce);
		echo $sEncryptMsg;
		return ErrorCode::$OK;
	}


	/**
	 * 检验消息的真实性，并且获取解密后的明文.
	 * <ol>
	 *    <li>利用收到的密文生成安全签名，进行签名验证</li>
	 *    <li>若验证通过，则提取xml中的加密消息</li>
	 *    <li>对消息进行解密</li>
	 * </ol>
	 *
	 * @param $request Laravel请求
	 *
	 * @return array [成功0，失败返回对应的错误码,解密后的原文]
	 */
	public function DecryptMsg(Request $request)
	{
        $sMsgSignature = $request->input('msg_signature');
        $this->sTimeStamp = $request->input('timestamp');
        $this->sNonce = $request->input('nonce');
        $sPostData = $request->getContent();

		if (strlen($this->m_sEncodingAesKey) != 43) {
			return [ErrorCode::$IllegalAesKey, ''];
		}

		$pc = new Prpcrypt($this->m_sEncodingAesKey);

		//提取密文
		$xmlparse = new XMLParse;
		$array = $xmlparse->extract($sPostData);
		$ret = $array[0];

		if ($ret != 0) {
			return [$ret, ''];
		}

		if ($this->sTimeStamp == null) {
			$this->sTimeStamp = time();
		}

		$encrypt = $array[1];
		$touser_name = $array[2];

		//验证安全签名
		$sha1 = new SHA1;
		$array = $sha1->getSHA1($this->m_sToken, $this->sTimeStamp, $this->sNonce, $encrypt);
		$ret = $array[0];

		if ($ret != 0) {
			return [$ret, ''];
		}

		$signature = $array[1];
		if ($signature != $sMsgSignature) {
			return [ErrorCode::$ValidateSignatureError, ''];
		}

		$result = $pc->decrypt($encrypt, $this->m_sCorpid);
		if ($result[0] != 0) {
			return [$result[0], ''];
		}
        $values = json_encode(simplexml_load_string($result[1], 'SimpleXMLElement', LIBXML_NOCDATA));

		return [ErrorCode::$OK, $values];
	}

}

