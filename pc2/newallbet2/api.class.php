<?php


class BBIN_TZH
{
	private $url_register = "http://47.88.8.241:741/ALLBET/index/GameUserRegister";
	private $url_balance = "http://47.88.8.241:741/ALLBET/index/GetBalance";
	private $url_transferin = "http://47.88.8.241:741/ALLBET/index/TransferIn";
	private $url_transferout = "http://47.88.8.241:741/ALLBET/index/TransferOut";
	private $url_gamelogin = "http://47.88.8.241:741/ALLBET/index/GameLogin";

	private $url_getrecords_1 = "http://47.88.8.241:741/ALLBET/index/GetRecords1";
	private $url_getrecords_5 = "http://47.88.8.241:741/ALLBET/index/GetRecords5";
	//private $url_gamelogout = "http://api.room88.net/api/allbet/logout.ashx";
	//private $url_checktransfer = "http://api.room88.net/api/allbet/checktransfer.ashx";
	//private $url_changepassword = "http://api.room88.net/api/allbet/changePassword.ashx";
	//private $url_getrecords_1 = "http://api.room88.net/api/allbet/betlogpieceofhistories.ashx";//10分鐘/1次  yesterday 23:00-tomorrow 00:00
	//private $url_getrecords_2 = "http://api.room88.net/api/allbet/clientbetlogquery.ashx";//by username  above -15 days
	//private $url_getrecords_3 = "http://api.room88.net/api/allbet/betlogdailymodifiedhistories.ashx";//today all user records  1小时/1次
	//private $url_getrecords_4 = "http://api.room88.net/api/allbet/betlogpieceofhistoriesin30days.ashx";
	

    private $comId   = "yl66y";
	private $comKey  = "123456";

	private $apiKey  = "9b4de1e006739412";
	
    public $debug = 0;



    public function BBIN_TZH($comId,$comKey,$apiKey)
    {
        $this->comId    = $comId;
        $this->comKey   = $comKey;  
        $this->apiKey = $apiKey;
    }


    /*
     * 创建账号
     */
    public function GameUserRegister($username,$password)
    {
		//$code = $this->salt.MD5($this->apiKey.$this->comId.$username.$password.$this->salt);
        $array = array(
           'apiAccount' => $this->comId,
           'userName' => $username,
           'Password' => $password,
           'code' => $this->apiKey,
        );
		
        $receive = $this->post2($this->url_register,$array);
		//$receivejson = json_decode($receive,true);
		
		return $receive;
        if($receivejson['Success'] == "True")
        {
            return "1";
        }  else {
			return "0";
        }
    }

	public function GetBalance($username,$password)
    {
		header("Content-type: text/html; charset=utf-8");
		//$code = $this->salt.MD5($this->apiKey.$this->comId.$username.$password.$this->salt);
        $array = array(
           'apiAccount' => $this->comId,
           'userName' => $username,
           'Password' => $password,
           'code' => $this->apiKey,
        );
		
        $receive = $this->post2($this->url_balance,$array);
		//return $receive;
        $receivejson = json_decode($receive,true);
		
		//return $receive;
        if($receivejson['Success'] == "True")
        {
            return $receivejson['Data']['Balance'];
        }  else {
			if($receivejson['Message'])
				return $receivejson['Message'];
			else
				return "请求数据失败";
        }
    }

	public function GameLogin($username,$password,$language='zh-CN')
    {
		//$code = $this->salt.MD5($this->apiKey.$this->comId.$username.$password.$language.$this->salt);
        $array = array(
           'apiAccount' => $this->comId,
           'userName' => $username,
           'Password' => $password,
		   'language' => $language,
           'code' => $this->apiKey,
        );
		
        $receive = $this->post2($this->url_gamelogin,$array);
		//echo $receive;
        $receivejson = json_decode($receive,true);
		
		//return $receive;
        if($receivejson['Success'] == "True")
        {
            return $receivejson['Data']['GameUrl'];
        }  else {
			if($receivejson['Message'])
				return $receivejson['Message'];
			else
				return "请求数据失败";
        }
    }

	public function GameLogout($username)
    {
		//$code = $this->salt.MD5($this->apiKey.$this->comId.$username.$this->salt);
        $array = array(
           'apiAccount' => $this->comId,
           'userName' => $username,
           'code' => $this->apiKey,
        );
		
        $receive = $this->post2($this->url_gamelogout,$array);
		//echo $receive;
        $receivejson = json_decode($receive,true);
		
		//return $receive;
        if($receivejson['Success'] == "True")
        {
            return $receivejson['Data']['GameUrl'];
        }  else {
			if($receivejson['Message'])
				return $receivejson['Message'];
			else
				return "请求数据失败";
        }
    }

	public function TransferIn($username,$transSN,$amount)//$transSN为10-19位唯一,需要构造
		{
		//$code = $this->salt.MD5($this->apiKey.$this->comId.$username.$transSN.$amount.$this->salt);
        $array = array(
           'apiAccount' => $this->comId,
           'userName' => $username,
           'transSN' => $transSN,
		   'amount' => $amount,
           'code' => $this->apiKey,
        );
		//return json_encode($array);
        $receive = $this->post2($this->url_transferin,$array);
		//echo $receive;
        $receivejson = json_decode($receive,true);
		
		//return $receive;
        if($receivejson['Success'] == "True")
        {
            return True;
        }  else {
			if($receivejson['Message'])
				return $receivejson['Message'];
			else
				return "请求数据失败";
        }
	}


	public function TransferOut($username,$transSN,$amount)//$transSN为10-19位唯一,需要构造
		{
		//$code = $this->salt.MD5($this->apiKey.$this->comId.$username.$transSN.$amount.$this->salt);
        $array = array(
           'apiAccount' => $this->comId,
           'userName' => $username,
           'transSN' => $transSN,
		   'amount' => $amount,
           'code' => $this->apiKey,
        );
		
        $receive = $this->post2($this->url_transferout,$array);
		//echo $receive;
        $receivejson = json_decode($receive,true);
		
		//return $receive;
        if($receivejson['Success'] == "True")
        {
            return true;
        }  else {
			if($receivejson['Message'])
				return $receivejson['Message'];
			else
				return "请求数据失败";
        }
	}

	public function GetRecords1($startDate,$endDate)
		{
		$code = $this->salt.MD5($this->apiKey.$this->comId.$startDate.$endDate.$this->salt);
        $array = array(
           'apiAccount' => $this->comId,
           'startDate' => $startDate,
		   'endDate' => $endDate,
           'code' => $code,
        );
		
		//return $this->apiKey.$this->comId.$username.$startDate.$endDate.$pageIndex.$pageSize.$this->salt;
		
        $receive = $this->post2($this->url_getrecords_1,$array);
		return $receive;
        $receivejson = json_decode($receive,true);
		
		//return $receive;
        if($receivejson['Success'] == "True")
        {
            return $receivejson['Data']['Records'];
        }  else {
			if($receivejson['Message'])
				return $receivejson['Message'];
			else
				return "请求数据失败";
        }
	}

	public function GetRecords5($startDate,$endDate)
		{
		$code = $this->salt.MD5($this->apiKey.$this->comId.$startDate.$endDate.$this->salt);
        $array = array(
           'apiAccount' => $this->comId,
           'startDate' => $startDate,
		   'endDate' => $endDate,
           'code' => $code,
        );
		
		//return $this->apiKey.$this->comId.$username.$startDate.$endDate.$pageIndex.$pageSize.$this->salt;
		
        $receive = $this->post2($this->url_getrecords_5,$array);
		return $receive;
        $receivejson = json_decode($receive,true);
		
		//return $receive;
        if($receivejson['Success'] == "True")
        {
            return $receivejson['Data']['Records'];
        }  else {
			if($receivejson['Message'])
				return $receivejson['Message'];
			else
				return "请求数据失败";
        }
	}
	public function getall($report_url,$lasttime){
		
        $receive = $this->post2($report_url,$xml);
		return $receive;
	}

	public static function post2($url, $data){//file_get_content
 
         
 
        $postdata = http_build_query(
 
            $data
 
        );
 
         
 
        $opts = array('http' =>
 
                      array(
 
                          'method'  => 'POST',
 
                          'header'  => 'Content-type: application/x-www-form-urlencoded',
 
                          'content' => $postdata
 
                      )
 
        );
 
         
 
        $context = stream_context_create($opts);
 
 
        $result = file_get_contents($url, false, $context);
 
        return $result;
 
 
    }


}


?>