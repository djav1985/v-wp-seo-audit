<?php
class Website extends CActiveRecord {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return Yii::app()->db->tablePrefix.'website';
	}

    public function total() {
        return $this->cache(60*60*5)->count();
    }

    public static function removeByDomain($domain)
    {
        $domain = idn_to_ascii($domain);
        $model = self::model()->findByAttributes(array(
            "md5domain"=>md5($domain),
        ));
        if(!$model) {
            return false;
        }
        $website_id = $model->id;
        $transaction = Yii::app() -> db -> beginTransaction();
        $command = Yii::app() -> db -> createCommand();
        try {
            $command -> delete('{{website}}', 'id=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{w3c}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{pagespeed}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{misc}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{metatags}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{links}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{issetobject}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{document}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{content}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $command -> delete('{{cloud}}', 'wid=:id', array(':id'=>$website_id));
            $command -> reset();

            $transaction->commit();
        } catch(Exception $e) {
            Yii::log($e, CLogger::LEVEL_ERROR);
            $transaction -> rollback();
            return false;
        }
        return true;
    }
}