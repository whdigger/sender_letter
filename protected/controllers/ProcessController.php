<?php

class ProcessController extends Controller {

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView() {
        if (Yii::app()->request->isAjaxRequest) {
            // Проверяем cookies и отдаём пользователю json ответ
            $value_cookie = isset(Yii::app()->request->cookies['status']) ? Yii::app()->request->cookies['status']->value : '';
            // Загружаем модель если такая существует в БД
            $model = Process::model()->find('cookies=:line', array(':line' => $value_cookie));
            if ($model === null) {
                Yii::app()->request->cookies->clear();
                $result['success'] = false;
                $result['redirect'] = Yii::app()->request->getBaseUrl(true) . Yii::app()->createUrl('site/index');
                $result['notice'] = 'Вы не создавали рассылку, либо рассылка была закончена';
            } else {
                // Удаляем запись о процессе из БД, по запросу пользователя
                if (isset($_POST['delete'])) {
                    $result['success'] = false;
                    if ($model->delete()) {
                        $result['success'] = true;
                    }
                } else {
                    $result['percent'] = $model->percent;
                    $result['timeexec'] = $model->remtime;
                    $result['redirect'] = Yii::app()->request->getBaseUrl(true) . Yii::app()->createUrl('site/index');
                    $result['success'] = true;
                }
            }
            echo json_encode($result);
            Yii::app()->end();
        }
        $this->redirect(Yii::app()->createUrl('site'));
    }

    public function actionExec() {
        // Игнорировать обрыв связи с браузером
        ignore_user_abort(1);
        // Если есть секретный токен грузим данные в модель и продолжаем выполнять скрипт
        $this->loadModelExec($_POST['sectoken'])->execProcess();
    }

    public function loadModelExec($sectoken) {
        $model = Process::model()->find('sectoken=:line', array(':line' => $sectoken));
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

}
