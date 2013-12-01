<?php
/**
 * Created by IntelliJ IDEA.
 * User: yuanxijie
 * Date: 13-11-30
 * Time: 下午2:15
 * To change this template use File | Settings | File Templates.
 */ 
class AccountManager extends Manager {
    public function get_all_account() {
        $partitions = G::$conf['bdc']['PARTITIONS'];
    }

    public function get_partition_by_word($word) {
        $partitions = G::$conf['bdc']['PARTITIONS'];
        $word_info = $this->word->get_pure_word_info($word);
        if(!$word_info) {
            return false;
        }

        return intval($word_info['id'])  % $partitions;
    }

    public function get_account_by_partition_id($partition_id) {
        $sql = "select id, username, email, password, ak, sk, bucket from baidu_account where partition_id = ?";

        return $this->executeQuery($sql, array($partition_id), false);
    }

    public function get_bae_account_for_word($word) {
        //static的变量最好是随即的，否则可能多个进程同时请求同一个备份
        static $index = 0;

        $partition_id = $this->get_partition_by_word($word);
        if($partition_id == false) {
            $this->logger->error('word:' . $word . '不存在');
            return $this->arrayResult(1, 'word:' . $word . '不存在');
        }

        $accounts = $this->get_account_by_partition_id($partition_id);
        if(empty($accounts)) {
            $this->logger->error('get no account for partition:' . $partition_id);
            return $this->arrayResult(1, 'get no account for partition:' . $partition_id);
        }

        $account_num = count($accounts);
        $account = $accounts[$index % $account_num];

        $baes = $this->get_all_bae_by_account($account['id']);
        if(empty($baes)) {
            $this->logger->error('no bae found for account:' . $account['id'], $word);
            return $this->arrayResult(1, 'no bae found for account:' . $account['id']);
        }

        $bae = $baes[$index % count($baes)];
        $index++;

        return $this->arrayResult(0, 'ok', array('account'=>$account, 'bae'=>$bae));
    }

    public function get_all_bae_by_account($account_id) {
        $sql = "select domain_name, app_name, app_id from bae where baidu_account_id = ?";
        return $this->executeQuery($sql, array($account_id), false);
    }
}
