<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanxijie
 * Date: 13-10-27
 * Time: 下午8:26
 * To change this template use File | Settings | File Templates.
 */
class WordStemRel extends CActiveRecord
{
    public static function model($className = __CLASS__) {
        return parent::model();
    }

    public function tableName() {
        return 'word_stem_rel';
    }
}