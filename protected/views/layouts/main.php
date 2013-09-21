<?php /* @var $this Controller */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
    

    
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
<div id="headbody"></div>
<div class="container" id="page">

	<div id="header">
        
		<img src="<?php echo Yii::app()->request->baseUrl.'/css/img/llogo.png'?>" height="150" style="float: left;border-radius: 45px 0 0 0;">
		<img src="<?php echo Yii::app()->request->baseUrl.'/css/img/rlogo.png'?>" height="150" style="float: right;border-radius: 0 45px 0 0;">
		<div id="logo">
			<p><?php echo CHtml::encode($this->pageTitle); //CHtml::encode(Yii::app()->name); ?></p>
		</div>
		
	</div><!-- header -->

	<div id="mainmenu">
		<?php $this->widget('zii.widgets.CMenu',array(
			'items'=>array(
				array('label'=>'Отправка письма', 'url'=>array('/site/index')),
				array('label'=>'О скрипте', 'url'=>array('/site/page', 'view'=>'about')),
                array('label'=>'Демонстрация', 'url'=>array('/site/message')),
			),
		)); ?>
	</div><!-- mainmenu -->
    
    
   	<?php if(isset($this->breadcrumbs)):?>
		<?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
	<?php endif?>
    
	<?php echo $content; ?>

	<div class="clear"></div>

	<div id="footer">
		Copyright &copy; <?php echo date('Y'); ?> by FusionFire.<br/>
		All Rights Reserved.<br/>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>
