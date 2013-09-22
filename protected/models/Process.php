<?php

/**
 * This is the model class for table "process".
 *
 * The followings are the available columns in table 'process':
 * @property integer $id
 * @property string $cookies
 * @property integer $id_message
 * @property double $percent
 * @property integer $currentel
 * @property integer $maxel
 * @property integer $timeexec
 * @property integer $remtime
 * @property string $sectoken
 */
class Process extends CActiveRecord {

    public $test;

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'process';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('cookies', 'required'),
            array('id_message, currentel, maxel, timeexec', 'numerical', 'integerOnly' => true),
            array('percent', 'numerical'),
            //array('percent', 'numerical', 'integerOnly'=>false,'numberPattern'=>'/^s*[-+]?[0-9]*.?[0-9]+([eE][-+]?[0-9]+)?s*$/','message'=>'erroreeeeee'),
            array('cookies, sectoken', 'length', 'max' => 128),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, cookies, id_message, percent, currentel, maxel, timeexec, remtime, sectoken', 'safe'),
        );
    }

    /*
      Отправка POST запроса через socket
      @param string $url
      @param array $postreq
      @param array $cookie
      @return boolean
     */

    private function sendPostSocket($url, $postreq, $cookie = '') {
        $post_params = array();
        foreach ($postreq as $k => $v)
            $post_params[] = "$k=" . urlencode($v);
        $post_params = implode('&', $post_params);

        $parts = parse_url($url);
        if (!$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80))
            return false;

        if (empty($parts['path']))
            $parts['path'] = '/';

        $out = array();
        $out[] = "POST $parts[path] HTTP/1.1";
        $out[] = "Host: $parts[host]";

        if (isset($cookie)) {
            if (is_array($cookie)) {
                reset($cookie);
                $k = key($cookie);
                $out[] = "Cookie: $k=$cookie[$k]";
            }
        }
        $out[] = "Content-Type: application/x-www-form-urlencoded";
        $out[] = "Content-Length: " . mb_strlen($post_params, 'utf-8');
        $out[] = "User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.127 Safari/533.4";
        $out[] = "Connection: Close";
        $out[] = "\r\n";
        $out = implode("\r\n", $out) . $post_params;

        fwrite($fp, $out);
        sleep(1);
        fclose($fp);
        return true;
    }

    /*
      Генератор случайных имён
      @return string
     */

    private function randomNameGen() {
        $username = '';

        $char = "0123456789abcdefghijklmnopqrstuvwxyz";
        $ulen = mt_rand(5, 10);
        for ($i = 1; $i <= $ulen; $i++) {
            $username .= substr($char, mt_rand(0, strlen($char)), 1);
        }
        return $username;
    }

    /*
      Генератор случайных email
      @param string $username
      @return string
     */

    private function randomEmailGen($username = '') {
        // array of possible top-level domains
        $tlds = array('com', 'net', 'gov', 'org', 'edu', 'biz', 'info');

        // string of possible characters
        $char = "0123456789abcdefghijklmnopqrstuvwxyz";

        // choose random lengths for the username ($ulen) and the domain ($dlen)

        $dlen = mt_rand(7, 17);

        $email = '';

        if ($username)
            $email = $username;
        else
            $email = $this->randomNameGen();

        // wouldn't work so well without this
        $email .= '@';

        for ($i = 1; $i <= $dlen; $i++) {
            $email .= substr($char, mt_rand(0, strlen($char)), 1);
        }

        $email .= '.';

        // finally, pick a random top-level domain and stick it on the end
        $email .= $tlds[mt_rand(0, (sizeof($tlds) - 1))];

        return $email;
    }

    /*
      Обновление БД информация о процессе
      @param string current текущий элемент
      @param string time текущее время выполнения скрипта
      @return string возвращает токен
     */

    private function updateProcess($current, $time) {
        $alltime = $this->timeexec + $time;
        $rtime = ($alltime / $current) * ( $this->maxel - $current);

        $token = md5(uniqid(rand(), true));
        $percent = ($current / $this->maxel) * 100;
        if ($this->updateByPk($this->id, array('currentel' => $current, 'percent' => $percent, 'sectoken' => $token, 'timeexec' => $alltime, 'remtime' => $rtime)))
            return $token;
        else
            return $this->sectoken;
    }

    /*
      Отправка одного письма, для тестирования
     */

    public function SendMailDB() {
        $modelmessage = MessageForm::model()->findByPk($this->id_message);
        if ($modelmessage === null)
            exit();

        $lt = new Letter('UTF8');

        // Загружаем письмо
        $lt->SetHeader($modelmessage->header);
        $lt->setSubject($modelmessage->subject);

        $lt->SetBodyHeader($modelmessage->bodyheader);
        $lt->setBody($modelmessage->body);
        $lt->SetBodyFooter($modelmessage->bodyfooter);

        $name = $this->randomNameGen();
        $lt->AddHeadBody('Здраствуйте пользователь ' . $name . '</br>');
        $lt->Send($this->randomEmailGen($name));
    }

    /*
      Процесс отправки письма
      @param string time_exec первое выполнение, чтобы не держать пользователя
     */

    public function execProcess($time_exec = false) {
        $modelmessage = MessageForm::model()->findByPk($this->id_message);
        if ($modelmessage === null)
            exit();

        // Узнаем время запуска скрипта
        $start_time = microtime();

        // Разделяем секунды и миллисекунды
        $start_array = explode(" ", $start_time);
        // Получаем стартовое время скрипта
        $start_time = $start_array[1];

        // Для первого запуска
        if ($time_exec)
            $max_exec = 2;
        else
        //Получаем максимально возможное время работы скрипта
            $max_exec = ini_get('max_execution_time') - 5;

        //Yii::app()->charset
        $lt = new Letter('UTF8');

        // Загружаем письмо
        $lt->SetHeader($modelmessage->header);
        $lt->setSubject($modelmessage->subject);

        $lt->SetBodyHeader($modelmessage->bodyheader);
        $lt->setBody($modelmessage->body);
        $lt->SetBodyFooter($modelmessage->bodyfooter);

        $current = $this->currentel;
        do {
            $name = $this->randomNameGen();
            $lt->AddHeadBody('Здраствуйте пользователь ' . $name . '</br>');

            /* ДЛЯ ТЕСТирования */
            //sleep(2);

            $lt->Send($this->randomEmailGen($name));
            $current +=1;

            // Узнаем текущее время чтобы проверить, дальше ли вести цикл или перезапустить
            $now_time = microtime();
            $now_array = explode(" ", $now_time);
            $now_time = $now_array[1];

            // Вычитаем из текущего времени начальное
            $exec_time = $now_time - $start_time;

            //Удалить cookies
            if ($current >= $this->maxel) {
                $this->updateProcess($current, $exec_time);
                $this->delete();
                exit();
            }
        } while ($exec_time < $max_exec);

        // Обновляем данные и отправляем запрос на повторную обработку
        $url = Yii::app()->request->getBaseUrl(true) . Yii::app()->createUrl('process/exec');
        $this->sendPostSocket($url, array('sectoken' => $this->updateProcess($current, $exec_time)));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Process the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

}
