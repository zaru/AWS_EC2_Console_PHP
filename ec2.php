<?php
/**
 * Amazon EC2 簡易操作ツール
 *
 * パラメータ説明
 *
 * list                         インスタンスの一覧表示
 * launch_ec2                   EC2インスタンスを新規作成
 * start_ec2 インスタンスID      指定インスタンスを起動
 * stop_ec2 インスタンスID       指定インスタンスを停止
 * start_ec2_all                すべてのインスタンスを起動
 * stop_ec2_all                 すべてのインスタンスを停止
 *
 */
require_once('sdk/sdk.class.php');

if(isset($argv['1'])){
    $command = $argv['1'];
}else{
    exit;
}

$param1 = '';
if(isset($argv['2'])){
    $param1 = $argv['2'];
}

$aws = new ManageAWS();

switch ($command) {
    case 'list':
        $aws->viewLists();
        break;
    case 'launch_ec2':
        $aws->launchEC2();
        break;
    case 'start_ec2':
        $aws->startEC2($param1);
        break;
    case 'stop_ec2':
        $aws->stopEC2($param1);
        break;
    case 'start_ec2_all':
        $aws->startEC2All();
        break;
    case 'stop_ec2_all':
        $aws->stopEC2All();
        break;
    default:
        echo PHP_EOL;
}

class ManageAWS{
    //リージョン設定
    public $region = AmazonEC2::REGION_APAC_NE1;
    
    //EC2インスタンスオプション
    public $ec2Options = array(
        'KeyName'          => 'ec2_aws',
        'InstanceType'     => 't1.micro',
        'SecurityGroupId'  => 'sg-fd88fcfc',
    );
    public $ec2ImageName = 'ami-5769ed56';
    
    //インスタンスの一覧表示
    public function viewLists(){
        $ec2 = new AmazonEC2();
        $ec2->set_region($this->region);
        $ret = $ec2->describe_instances();
        if (!empty($ret->body->reservationSet->item)) {
            foreach ($ret->body->reservationSet->item as $item) {
                //print_r($item->instancesSet->item);
                $itemInfo = $item->instancesSet->item;
                printf("[%s] ID %s / Status %s" . PHP_EOL, $region, $itemInfo->instanceId, $itemInfo->instanceState->name);
            }
        }
    }
    
    //インスタンスの一覧取得
    private function findIdLists(){
        $ec2 = new AmazonEC2();
        $ec2->set_region($this->region);
        $ret = $ec2->describe_instances();
        
        $lists = array();
        if (!empty($ret->body->reservationSet->item)) {
            foreach ($ret->body->reservationSet->item as $item) {
                $itemInfo = $item->instancesSet->item;
                $lists[] = $itemInfo->instanceId;
            }
        }
        
        return $lists;
    }
    
    //インスタンスの新規作成
    public function launchEC2(){
        $ec2 = new AmazonEC2();
        $ec2->set_region($this->region);
        $response = $ec2->run_instances($this->ec2ImageName, 1, 1, $this->ec2Options);
        if($response->isOK()){
            echo 'launch EC2 OK' . PHP_EOL;
        }else{
            echo 'launch EC2 Error : ' . $response->body->Errors->Error->Message . PHP_EOL;
        }
    }
    
    //インスタンスの起動
    public function startEC2($id){
        if($id == ''){
            echo 'missing params' . PHP_EOL;
            return;
        }
        $ec2 = new AmazonEC2();
        $ec2->set_region($this->region);
        $response = $ec2->start_instances($id);
        if($response->isOK()){
            echo 'start ' . $id . ' EC2 OK' . PHP_EOL;
        }else{
            echo 'start ' . $id . ' EC2 Error : ' . $response->body->Errors->Error->Message . PHP_EOL;
        }
    }
    
    //インスタンスの停止
    public function stopEC2($id){
        if($id == ''){
            echo 'missing params' . PHP_EOL;
            return;
        }
        $ec2 = new AmazonEC2();
        $ec2->set_region($this->region);
        $response = $ec2->stop_instances($id);
        if($response->isOK()){
            echo 'stop ' . $id . ' EC2 OK' . PHP_EOL;
        }else{
            echo 'stop ' . $id . ' EC2 Error : ' . $response->body->Errors->Error->Message . PHP_EOL;
        }
    }
    
    //全インスタンスの起動
    public function startEC2All(){
        $lists = $this->findIdLists();
        
        $ec2 = new AmazonEC2();
        $ec2->set_region($this->region);
        foreach($lists as $id){
            $response = $ec2->start_instances($id);
            if($response->isOK()){
                echo 'start ' . $id . ' EC2 OK' . PHP_EOL;
            }else{
                echo 'start ' . $id . ' EC2 Error : ' . $response->body->Errors->Error->Message . PHP_EOL;
            }
        }
    }
    
    //全インスタンスの停止
    public function stopEC2All(){
        $lists = $this->findIdLists();
        
        $ec2 = new AmazonEC2();
        $ec2->set_region($this->region);
        foreach($lists as $id){
            $response = $ec2->stop_instances($id);
            if($response->isOK()){
                echo 'stop ' . $id . ' EC2 OK' . PHP_EOL;
            }else{
                echo 'stop ' . $id . ' EC2 Error : ' . $response->body->Errors->Error->Message . PHP_EOL;
            }
        }
    }
}