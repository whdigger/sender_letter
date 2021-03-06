<?php
/* @var $this SiteController */

$this->pageTitle = 'О скрипте';
$this->breadcrumbs = array(
    'О скрипте',
);
?>

<h1>Рассыльщик писем</h1>

<p>
    Исполнитель: back-end + front-end</br>
    Название: рассыльщик писем.</br>
    Область: server-side + client-side.</br>
</p>

<p>Задача: сделать интерфейс и процесс рассылки 500000 писем (на разные адреса) за одну операцию.</p>

<u>Интерфейс:</u>
<ol type="1">
    <li>Поле "Заголовок письма".</li>
    <li>Поле "Текст письма".</li>
    <li>Поле выбора файла.</li>
    <li>Кнопка "Отправить".</li>
</ol>

<u>Процесс написания письма:</u>
<ol type="1">
    <li>Можно использовать HTML.</li>
    <li>Файл можно выбрать только один.</li>
    <li>При сабмите форму переходим к процедуре отправки писем.</li>
</ol>

<u>Процесс отправки письма:</u>
<ol type="1">
    <li>Письмо только в HTML-формате.</li>
    <li>Со стороны пользователя выглядит так: зашёл на страницу, вбил данные, загрузил файл, нажал "Отправить". Подождал, получил уведомление об успешной рассылке.</li>
    <li>Файл приаттачивается к письму.</li>
    <li>В тексте каждого письма в начале стоит фраза "Здравствуй, имя_юзера", где имя_юзера уникальное для каждого письма (можно генерировать случайно, можно даже цифры подставлять из функции rand, но в каждом письме имя уникально).</li>
    <li>Прилагаемый файл - картинка. Картинка должна быть вставлена в тело письма и иметь стили <i>"float: left; margin: 0 20px 20px 0;".</i>
        Картинка должна отображаться при открытии письма. Не должно быть ситуаций типа таких, когда почтовый клиент предлагает "показать изображения".</li>
</ol>

<u>Обратить внимание на подводные камни:</u>
<ol type="1">
    <li>Использовать обходные пути. при отправке 500000 писем т.к процедура долгая.</li>
</ol>

<u>Технология:</u>
<ol type="1">
    <li>Проект организован на архитектуре MVC с использованием YII.</li>
    <li>Использовать SMTP-заглушку, по типу sendmail. То есть нужно получить много *.eml-файлов.</li>
</ol>


<?php
    echo CHtml::link('Демонстрация:',array('/site/message')); 
?>

<ol type="1">
    <li>Либо выложить в интернет и дать ссылку на просмотр.</li>
    <li>Прислать код и 
    <?php
        echo CHtml::link('инструкцию по развертыванию.',array('/site/page', 'view'=>'deployment')); 
    ?>
    </li>
    <li>Прислать один eml-файл.</li>
</ol>

<u>Бонус</u>
<ol type="1">
    <li>Прилагаемый файл – картинка.</li>
    <li>Картинка должна быть вставлена в тело письма и иметь стили <i>"float: left; margin: 0 20px 20px 0;"</i>.</li>
    <li>Картинка должна отображаться при открытии письма.</li>
</ol>
