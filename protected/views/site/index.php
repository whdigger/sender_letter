<?php
/* @var $this SiteController */
/* @var $model ContactForm */
/* @var $form CActiveForm */

$this->pageTitle='Рассылка';
$this->breadcrumbs=array(
	'Рассылка',
);
?>
<?php if(Yii::app()->user->hasFlash('error')): ?>

<div class='flash-error'>
	<?php echo Yii::app()->user->getFlash('error'); ?>
</div>

<?php elseif(Yii::app()->user->hasFlash('info')): ?>
<?php	
        Yii::app()->clientScript->registerPackage('sender');
        // подключить jQuery в проекте
        Yii::app()->getClientScript()->registerCoreScript('jquery');
        Yii::app()->getClientScript()->registerCoreScript('jquery.ui');
        Yii::app()->clientScript->registerCss('uiloader','
        .ui-progressbar-value {
          transition: width 0.5s;
          -webkit-transition: width 0.5s;
          display:block !important;
        }
        
        #progresstext{
            text-align: center;
        }
        ');

?>

<?php
Yii::app()->clientScript->registerScript('progress_auto_update',"

t = setTimeout( function () { $('#progressbar').progressbar({ value: 0 });
                                $('#progresspercent').html('<b>'+0.0+'%</b>');
                                $('#progress').fadeIn(1000);
                                 }, 1000);

t = setTimeout( function () { updateStatus('".Yii::app()->urlManager->createUrl('process/view')."'); }, 3000);
t = setTimeout( function () { $('#buttondel').animate({height: 'show'}); }, 5000);

",CClientScript::POS_READY);
?>

<div id='content_resp' class='flash-success'>
	<?php echo Yii::app()->user->getFlash('info'); ?>
</div>

<div style='margin: 0 25%;position: relative;display: none;' id='progress'>
	<div id="progresspercent"></div>
	<div id="progressbar"></div>
    <div id="progresstext"></div>
</div>


<div id='buttondel' style='margin: 0 40%;display: none;' class="row buttons">
<?php
        echo CHtml::ajaxSubmitButton(
                                'Удалить рассылку', CController::createUrl('process/view'),  
                                array('type' => 'POST',
                                    'data'=>array('delete'=>TRUE ),
                                    'success'=>'function(data){
                                        if (jQuery.parseJSON(data).success){ 
                                            $("#buttondel").animate({height: "hide"});
                                        }
                                    }')
);
    ?>
</div>


<?php else: ?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'message-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	'htmlOptions'=>array('enctype'=>'multipart/form-data'),
    //'enableAjaxValidation' => true,
)); ?>

	<p>Для отправки рассылки пожалуйста, заполните следующую форму.</p>
	<p class="note centerform">Поля помеченные <span class="required">*</span> обязательны к заполнению.</p>
	

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
	
		<?php echo $form->labelEx($model,'email',array('class' => 'leftlabel')); ?>
		<?php echo $form->textField($model,'email',array('placeholder' => 'Введите e-mail')); ?>
		<?php echo $form->error($model,'email',array('style' => 'margin-left: 155px;')); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'sub',array('class' => 'leftlabel')); ?>
		<?php echo $form->textField($model,'sub',array('placeholder' => 'Введите тему')); ?>
		<?php echo $form->error($model,'sub',array('style' => 'margin-left: 155px;')); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'bodyf',array('class' => 'leftlabel')); ?>
		<?php echo $form->textArea($model,'bodyf',array('placeholder' => 'Введите письмо')); ?>
		<?php echo $form->error($model,'bodyf',array('style' => 'margin-left: 155px;')); ?>
	</div>


    <div class="row">
		<?php echo $form->labelEx($model,'filename',array('class' => 'leftlabel')); ?>
        <input type='text' id='filename' readonly='readonly'>
        <div class='file_input_div'>
                <?php echo $form->fileField($model,'attach',array(
                'class' => 'file_input_hidden',
                'onchange' => 'javascript:document.getElementById("filename").value = this.value.replace("/C:\\fakepath\\/i", "");'
                )); ?>
        </div>
	</div>
 
    <div class="row">
       <?php echo $form->labelEx($model,'attached',array('class' => 'leftlabel')); ?>
       <?php echo $form->checkBox($model,'attached'); ?>
    </div>
    

	<?php if(CCaptcha::checkRequirements()): ?>
	<div class="row">
		<?php echo $form->labelEx($model,'verifyCode',array('class' => 'leftlabel')); ?>
		<div>
		<?php //$this->widget('CCaptcha',array('buttonLabel' => '<br>[новый код]')); ?>
				
		<?php $this->widget('CCaptcha', array('clickableImage'=>true, 'showRefreshButton'=>true, 'buttonLabel' => CHtml::image(Yii::app()->baseUrl
                                        . '/css/img/icon_refresh.png'),'imageOptions'=>array(
                                        'style'=>'/*display:block;*/border:none;',
                                         /*'height'=>'40px',*/
                                         'alt'=>'Картинка с кодом валидации',
                                         'title'=>'Чтобы обновить картинку, нажмите по ней'))); ?>
        <br />
		
		</div>
		
		<div class="alignformright hint">Пожалуйста введите символы с картинки.<br />Буквы вводятся без учёта регистра</div>
		
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'',array('class' => 'leftlabel'));
			  echo $form->textField($model,'verifyCode');
			  echo $form->error($model,'verifyCode',array('style' => 'margin-left: 155px;'));
		?>
	</div>
	
	<?php endif; ?>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Submit',array ('class' => 'alignformright','value' => 'Отправить')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php endif; ?>