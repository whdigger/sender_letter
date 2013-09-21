<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF, //цвет фона капчи
				//'testLimit'=>2, //сколько раз капча не меняется
				'transparent'=>false,
				//'foreColor'=>0xE16020, //цвет символов
			),
			
			
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

    private $items = array('attach.eml','inline.eml','html.eml');  
  
    public function actionMessage() {
        $id = $_GET['id'];
        if ($id >= 0 && $id < 3)
            $item = $_SERVER['DOCUMENT_ROOT'].'/message/'.$this->items[$id];
        else
            $item = '';
            
        if(Yii::app()->request->isAjaxRequest) {
            $this->renderPartial('_message', array(  
                'item' => $item,  
            ));
            Yii::app()->end();
        }
        $this->render('message');
    }
  
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$model=new MessageForm;

        // Проверяем cookies и если они есть редиректим на страницу загрузки
        $cookie = Yii::app()->request->cookies['status']->value;
        if ($cookie) 
            Yii::app()->user->setFlash('info','Дождитесь окончание рассылки');
        else{
    		if(isset($_POST['MessageForm']))
    		{
                // Загружаем полученные данные и файл
    			$model->attributes=$_POST['MessageForm'];
                $model->attach=CUploadedFile::getInstance($model,'attach');
                
                // Проверяем данные и файл
                if($model->validate())            
    			{
    				$cid = '';
                    if($model->attach){
                        $path = $_SERVER['DOCUMENT_ROOT'].'/upload/'.time().'.'.$model->attach->getExtensionName();
                        $model->attach->saveAs($path,true);
                        $cid = $model->CreateMessage($path);
                    }
                    else
                        $cid = $model->CreateMessage();
                    
                    if ($cid !== ''){
                        $better_token = md5(uniqid(rand(),true));
                        
                        // Загружаем информацию для пользователя
                        $modelprocess = new Process;
                        $modelprocess->attributes=array('id_message'=>$cid,'percent'=>0,'currentel'=>0,'maxel'=>500,'cookies'=>$better_token);
                        $modelprocess->save(false);
                        
                        $cookie = new CHttpCookie('status', $better_token);
                        $cookie->expire = time()+60*60*24*2; 
                        Yii::app()->request->cookies['status'] = $cookie;
                        
                                                
                        //Для тестов
                        //$modelprocess->SendMailDB();
                        
                        $modelprocess->execProcess(true);                    
                        Yii::app()->user->setFlash('info','Сообщение создано и готово к рассылке');
                    }
                    else
                        Yii::app()->user->setFlash('error','Произошла ошибка при создании сообщения обратитесь к разработчику');
                    
                }
    		}
        }
        $this->render('index',array('model'=>$model));
	}
     
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
}