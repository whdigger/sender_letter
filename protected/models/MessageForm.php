<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 */
class MessageForm extends CActiveRecord
{
	public $email;
	public $sub;
	public $bodyf;
	public $verifyCode;
    public $attach;
    public $attached;

	public function tableName()
	{
		return 'message';
	}
    
	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			// name, email, subject and body are required
			array('email, sub, bodyf', 'required'),
            array('attached', 'boolean'),
            array('attach', 'file','types'=>'bmp,jpeg,png,gif,pjpeg,jpg', 
                                  //'Mimetypes'=>'image/bmp,image/jpeg,image/png,image/gif,image/pjpeg,image/jpg',
                                  'maxSize' => '3145728',
                                  'allowEmpty'=>true,
                                  'wrongType'=>'Изображение должно быть в графическом формате',
                                  'tooLarge'=> 'Размер изображения не должен превышать {limit}'),
            
			// email has to be a valid email address
			array('email', 'email'),
			// verifyCode needs to be entered correctly
			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
            array('subject, header, bodyheader, body, bodyfooter', 'safe'),
		);
	}
    
    /**
	 * Declares the validation rules.
	*/
	public function CreateMessage($filepath='')
	{
        try{
            $disposition = 'inline';
            if ($this->attached){
               $disposition = 'attachment';
            }
            
            $this->body = $this->bodyf;
            $lt = new Letter ('UTF8');
            
            if ($filepath === '')
                $lt->creatLetter($this->sub,$this->email,$this->bodyf);
            else
              	$lt->creatLetter($this->sub,$this->email,$this->bodyf,'',$filepath,$this->attach,$disposition);
            
            
            if ($disposition == 'inline' && $lt->getFileAtach() )
                $this->body .= '</br><img style="float: left; margin: 0 20px 20px 0;" src="cid:'.$lt->getCidName().'" />';
            
            $this->subject = $this->sub;
            
            $this->header = $lt->GetHeader();
            $this->bodyheader = $lt->GetBodyHeader();
            $this->bodyfooter = $lt->GetBodyFooter();
            $this->save(false);
            return $this->id;
        }
        catch (LetterException $e) {
        	return '';
        }
	}
    

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Message the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'email'=>'От кого',
            'sub'=>'Заголовок',
            'bodyf'=>'Текст письма',
            'filename'=>'Выбор файла',
            'verifyCode'=>'Код проверки',
            'attached'=>'Как вложение',
		);
	}
}