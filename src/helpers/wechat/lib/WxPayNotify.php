<?php

/**
 * 
 * 回调基础类
 * @author widyhu
 *
 */
namespace zander84\helpers\helpers\wechat\lib;

use zander84\modernadmin\helpers\wechat\lib\paydata\WxPayNotifyReply;
use zander84\modernadmin\helpers\wechat\lib\paydata\WxPayOrderQuery;

class WxPayNotify extends WxPayNotifyReply
{
	private $config = null;
	public $callback ;


    //查询订单
    public function Queryorder($transaction_id, $config)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);

        $result = WxPayApi::orderQuery($config, $input);

        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }


	/**
	 * 
	 * 回调入口
	 * @param bool $needSign  是否需要签名返回
	 */
	final public function Handle($callback, $config, $needSign = true)
	{
	    $this->callback = $callback;
		$this->config = $config;
		$msg = "OK";
		//当返回false的时候，表示notify中调用NotifyCallBack回调失败获取签名校验失败，此时直接回复失败
		$result = WxPayApi::notify($config, array($this, 'NotifyCallBack'), $msg);
		if($result == false){
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
			$this->ReplyNotify(false);
			return;
		} else {
			//该分支在成功回调到NotifyCallBack方法，处理完成之后流程

            $this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
		}
		$this->ReplyNotify($needSign);
	}
	
	/**
	 * 
	 * 回调方法入口，子类可重写该方法
	 	//TODO 1、进行参数校验
		//TODO 2、进行签名验证
		//TODO 3、处理业务逻辑
	 * 注意：
	 * 1、微信回调超时时间为2s，建议用户使用异步处理流程，确认成功之后立刻回复微信服务器
	 * 2、微信服务器在调用失败或者接到回包为非确认包的时候，会发起重试，需确保你的回调是可以重入
	 * @param WxPayNotifyResults $objData 回调解释出的参数
	 * @param WxPayConfigInterface $config
	 * @param string $msg 如果回调处理失败，可以将错误信息输出到该方法
	 * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
	 */
	public function NotifyProcess($objData, $config, &$msg)
	{

        $data = $objData->GetValues();
        //TODO 1、进行参数校验
        if(!array_key_exists("return_code", $data)
            ||(array_key_exists("return_code", $data) && $data['return_code'] != "SUCCESS")) {
            //TODO失败,不是支付成功的通知
            //如果有需要可以做失败时候的一些清理处理，并且做一些监控
            $msg = "异常异常";
            return false;
        }
        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }

        //TODO 2、进行签名验证
        try {
            $checkResult = $objData->CheckSign($config);
            if($checkResult == false){
                //签名错误
                $msg = "签名错误";
                return false;
            }
        } catch(Exception $e) {
            $msg = "签名错误";
            return false;
        }

        //TODO 3、查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"], $config)){
            $msg = "订单查询失败";
            return false;
        }

		//TODO 4、业务处理
        $c = $this->callback;
        return $c($data, $msg);
		//return false;
	}

	/**
	*
	* 业务可以继承该方法，打印XML方便定位.
	* @param string $xmlData 返回的xml参数
	*
	**/
	public function LogAfterProcess($xmlData)
	{
		return;
	}
	
	/**
	 * 
	 * notify回调方法，该方法中需要赋值需要输出的参数,不可重写
	 * @param array $data
	 * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
	 */
	final public function NotifyCallBack($data)
	{
		$msg = "OK";
		$result = $this->NotifyProcess($data, $this->config, $msg);
		
		if($result == true){
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
		} else {
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
		}
		return $result;
	}
	
	/**
	 * 
	 * 回复通知
	 * @param bool $needSign 是否需要签名输出
	 */
	final private function ReplyNotify($needSign = true)
	{
		//如果需要签名
		if($needSign == true && 
			$this->GetReturn_code() == "SUCCESS")
		{
			$this->SetSign($this->config);
		}

		$xml = $this->ToXml();
		$this->LogAfterProcess($xml);
		WxpayApi::replyNotify($xml);
	}
}
