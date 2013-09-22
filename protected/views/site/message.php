<?php
/* @var $this SiteController */

$this->pageTitle = 'Демонстрация писем';
$this->breadcrumbs = array(
    'Демонстрация',
);
?>

<table width="100%" border="0" cellpadding="4">
    <tr align="center">
        <td style="font-size: 120%;">
            <?php echo CHtml::ajaxLink('Письмо с вложением', array('site/message', 'id' => '0'), array('update' => '#apartments-list')) ?>
        </td>
        <td style="font-size: 120%;">
            <?php echo CHtml::ajaxLink('Письмо с изображение внутри', array('site/message', 'id' => '1'), array('update' => '#apartments-list')) ?>
        </td>
        <td style="font-size: 120%;">
            <?php echo CHtml::ajaxLink('Простое письмо', array('site/message', 'id' => '2'), array('update' => '#apartments-list')) ?>
        </td>
    </tr>
</table>

<?php
        echo CHtml::textArea('apartments-list','',array('rows'=>20, 'cols'=>120,'id'=>'apartments-list','style' => 'resize:none;'));
?>