<?php

class Letter
{
	const STOP_CRITICAL = -1; // message, plus full stop, critical error reached

	/*
		Массив заголовков
	@var array
	*/
	private $headers = array();
	
	/*
		Заголовки для отправки
	@var string
	*/
	private $headersend;
	
	/*
		Тело для отправки
	@var string
	*/
	private $bodysend;
	private $headbodysend;
	private $endbodysend;
	/*
		Кодировка по умолчанию
	@var string
	*/
	private $charset = "windows-1251";	
	private $encoding = "8bit";
	
	/*
		Запрос подтверждения о доставке
	@var int
	*/
	private $receipt;
	
	/*
		Формат письма
	@var string
	*/
	private $text_html='text/html';
	private $names_email = array(); 
	
	/*
		Приоритеты
	@var array
	*/
	private $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
	
	/*
		Путь к прикрепляемому файлу,имя файла,тип файла,как отображать файл пользователю
	@var string
	*/
	private $attach,$act_filename,$ftype,$displayfile,$uniq_id,$cid;
    private $fileatach = false;

	public function __construct ($charset ='')
	{
		$this->uniq_id= md5(date('r', time()));

		if( $charset != '' ) {
			$this->charset = strtolower($charset);
			if( $this->charset == 'us-ascii' )
				$this->encoding = '7bit';
		}
	}

	
	/*
		Создание простого письма без вложения
	@param boolean $property
	*/
	
	public function creatLetter ( $subject,$from,$body,$fromname='',$attach='',$actual_filename = '',$disposition ='inline', $prior = 3,$reply = '',$replyname = '')
	{
		try {
			if ($reply === ''){
				$reply = $from;
				$replyname = $fromname;
			}
			
			if ( $attach !== '')
				$this->Attach ($attach,trim($actual_filename),$disposition);
			
			$this->setSubject ($subject);
			$this->setFrom($from, $fromname);
			$this->setReplyTo($reply,$replyname);
            
			$this->setBody($body,$this->charset);
			$this->setPriority($prior);
			
			$this->BuildMail();
			return true;
		}
		catch (Exception $e) {
		  if ($e->getCode() == self::STOP_CRITICAL)
			return false;
		}
	}
	
	
	/*
		Создание заголовков для From, Reply
	@param boolean $property
	*/
	
	private function creatHeaders ( $head ){
		$this->headersend .= $head.': =?'.$this->charset.'?Q?'.str_replace('+','_',str_replace('%','=',urlencode(strtr( $this->names_email[$head], "\n" , "  " )))).'?= <'.$this->headers[$head].">\n";
	}
	
	/*
		Установка имён для полей 
	@param string $key
	@param string $email
	@param string $name
	*/
	private function setNameEmail( $key, $email, $name = ''  )
	{

		if( ! is_string($email) )
			throw new LetterException("E-mail должен быть строкой для поля $key", self::STOP_CRITICAL);
		if ($this->CheckEmail($email)) {
			$this->names_email[$key]= $name;
			$this->headers[$key] = $email;
		}
		else
			throw new LetterException("Не верно указан e-mail для поля $key", self::STOP_CRITICAL);
	}
	
	/*
		Установка темы письма
	@param boolean $subject
	*/
	
	public function setSubject( $subject )
	{
		$this->headers['Subject'] ='=?'.$this->charset.'?Q?'.str_replace('+','_',str_replace('%','=',urlencode(strtr( $subject, "\n" , "  " )))).'?=';
	}
	
        
	/*
		Установка поля от кого 'Вася;test@test.com'
	@param string $from
	@param string $name
	*/

	public function setFrom( $from, $name )
	{
		$this->setNameEmail ('From' ,$from, $name);
	}

	/*
		Установка поля ответа, обратный адресс 'Вася;test@test.com'
	@param string $reply
	@param string $name
	*/
	
	public function setReplyTo( $reply, $name  )
	{
		$this->setNameEmail ('Reply-To' ,$reply, $name);
	}


	/*
		Установка тела письма и формат представления письма
	@param string $body
	@param string $text_html
	*/
	
	public function setBody( $body, $text_html = '' )
	{
		$this->body = $body;
		if( $text_html == 'plain' ) $this->text_html = 'text/plain';

	}


	/*
		Установка приоритета письма
	@param int $priority
	*/
	
	public function setPriority( $priority )
	{
		if( !intval( $priority ) || !isset( $this->priorities[$priority-1]))
			$this->headers['X-Priority'] = '3 (Normal)';
		else
			$this->headers['X-Priority'] = $this->priorities[$priority-1];
	}
	

	/*
		Прикрепленные файлы
	@param string $filename : путь к файлу, который надо отправить
	@param string $actual_filename : реальное имя файла.
	@param string $disposition : Как отображать прикрепленный файл ("inline") как часть письма или ("attachment") как прикрепленный файл
	*/

	public function Attach( $filename, $actual_filename = '', $disposition = 'inline' )
	{
		$filetype = $this->mime_types(substr(strrchr($filename, '.'), 1));
		
		$this->attach = $filename;
        
        if ($disposition == 'inline'){
            if ($actual_filename)
                $this->cid = basename($actual_filename);
            else
                $this->cid = basename($filename);
        }
        
        $this->act_filename = $actual_filename;
		$this->ftype = $filetype;
		$this->displayfile = $disposition;
	}

    /*
		Отправка письма
	*/
	
	public function Send($sendTo)
	{
		$res = @mail( $sendTo, $this->headers['Subject'], $this->bodysend,$this->headersend );
		return (($res)? true : false);
	}

	/*
		Отправка письма с перестроением 
	*/
	public function BuildSend($sendTo)
	{
		$this->BuildMail();
		$this->Send();
	}
	
	/*
		Изменение тела письма
	*/
	
	public function ChangeBody($chbody)
	{
		$this->bodysend = $this->headbodysend.$chbody."\n\n".$this->endbodysend;
	}
	
	public function AddHeadBody($addbody)
	{
		$this->bodysend = $this->headbodysend.$addbody.$this->body."\n\n".$this->endbodysend;
	}
	
	
    /*
		Загрузка header
	*/

	public function SetHeader($header)
	{
		$this->headersend = $header;
	}
    
   	public function SetBodyHeader($header)
	{
		$this->headbodysend = $header;
	}
    
     /*
		Загрузка footer
	*/

	public function SetBodyFooter($footer)
	{
		$this->endbodysend = $footer;
	}
    
    
    /*
		Получение cid,FileAtach
	*/
    public function getCidName (){
        return $this->cid;
    }
    	
    public function getFileAtach (){
        return $this->fileatach;
    }
    
    /*
		Вывод письма
	*/

	public function GetMail()
	{
		return "{$this->headersend}\n\n{$this->bodysend}";
	}
    
    
    /*
		Загрузка header
	*/
    
	public function GetHeader()
	{
		return $this->headersend;
	}
    
     /*
		Получение body
	*/

    public function GetBodyHeader()
	{
		return $this->headbodysend;
	}
    
    
	public function GetBodyFooter()
	{
		return $this->endbodysend;
	}
    
	/*
		Проверка email
	@return bolean
	*/

	private function CheckEmail($email)
	{
		if (function_exists('filter_list'))
		{
			return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false)? true : false ;
		}
		// FULL e-mail regexp valid http://www.ex-parrot.com/~pdw/Mail-RFC822-Address.html
		return preg_match ( '^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$' , $email) ;
	}


	/*
		Проверка массива адресов
	@param string array
	@return array
	*/

	public function CheckListEmail( $email_list )
	{
		if (is_array ($email_list)){
			$email_list = array_unique($email_list);
			foreach ($email_list as $key=>$value) {
				if(!$this->CheckEmail( $value ))
					unset ($email_list[$key]);
				
			}
			sort($email_list);
		}
		return $email_list;
	}

	
	private function mime_types($ext = '') {
		$mimes = array(
		  'bmp'   =>  'image/bmp',
		  'gif'   =>  'image/gif',
		  'jpeg'  =>  'image/jpeg',
		  'jpe'   =>  'image/jpeg',
		  'jpg'   =>  'image/jpeg',
		  'png'   =>  'image/png',
		  'tiff'  =>  'image/tiff',
		  'tif'   =>  'image/tiff'      
		);
		
		return (!isset($mimes[strtolower($ext)])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
	}
  
	/*
		Преобразование изображения в base64
	@param string filename
	@return string
	*/

	private function ImgBase64( $filename )
	{
		if( ! file_exists( $filename) ) {
			return '';
		}
		$linesz= filesize( $filename)+1;
		$fp= fopen( $filename, 'r' );
		
		if ($fp === false )
			throw new LetterException("Не возможно открыть файл $filename", self::STOP_CRITICAL);
			
		$atach = chunk_split(base64_encode(fread( $fp, $linesz)));
		fclose($fp);
		
		return $atach;
	}
	
	
	/*
		Построение письма
		Content-Type: 
		mixed — сообщение с вложением.
		alternative — несколько частей содержащих одну и ту же информацию: например текстовая и HTML версия одного и того же письма.
		related — HTML письмо с картинками, только в этом случае должны работать ссылки на Contend-id элементов
	*/
	
	private function BuildMail()
	{
		$this->headersend = '';
		
		$this->headers['X-Mailer'] = 'Newsletter';
		$this->headers['Mime-Version'] = '1.0';
		$this->headers['Content-Type'] = "{$this->text_html}; charset={$this->charset}";
		$this->headers['Content-Transfer-Encoding'] = $this->encoding;
		
		if($this->attach){
			if(!$this->BuildAttach())
				$this->bodysend = $this->body;
                $this->fileatach = true;
			}
		else
			$this->bodysend = $this->body;

		reset($this->headers);
		while( list( $head,$value ) = each( $this->headers ) ) {
			if( $head == 'From' && strlen($this->names_email['From']))
				$this->creatHeaders('From');
			else if( $head == 'Reply-To' && strlen($this->names_email['Reply-To']))
				$this->creatHeaders('Reply-To');
			else if( $head != 'Subject')
				$this->headersend .= "{$head}: {$value}\n";
		}
	}
	
	/*
		Сборка файлов для отправки
	*/
	
	private function BuildAttach()
	{
		$atch = $this->ImgBase64($this->attach);
		
		if ($atch !== ''){
			$this->headbodysend = "This is a multi-part message in MIME format.\n\n--{$this->uniq_id}\n";
			$this->headbodysend .= "Content-Type: {$this->text_html}; charset={$this->charset}\nContent-Transfer-Encoding: {$this->encoding}\n\n";
			
            if(isset($this->cid) && $this->cid !== '')
			{
				$this->headers['Content-Type'] = "multipart/related;\n boundary=\"{$this->uniq_id}\"";
				$linkID = "Content-ID: <{$this->cid}> \n";
				$contentdisp = "{$linkID}Content-Disposition: inline;";
			}
			else{
				$this->headers['Content-Type'] = "multipart/mixed;\n boundary=\"{$this->uniq_id}\"";
				$contentdisp = "Content-Disposition: {$this->displayfile};";
			}
            
		
			// Задание имени файла
			if(strlen($this->act_filename)) $basename=$this->act_filename;
			else $basename = basename($this->attach);


			$subheader= "Content-Type: {$this->ftype}; name=\"$basename\"\nContent-Transfer-Encoding: base64\n{$contentdisp} filename=\"$basename\"\n";
			$this->endbodysend .= "--{$this->uniq_id}\n{$subheader}\n{$atch}\n\n--{$this->uniq_id}--\n\n";
			
			$this->bodysend = $this->headbodysend.$this->body."\n\n".$this->endbodysend;
			return true;
		}
		return false;
	}
}

class LetterException extends Exception {

  public function errorMessage() {
    $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
    return $errorMsg;
  }
}
?>
