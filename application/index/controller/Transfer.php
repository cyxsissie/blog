<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/14
 * Time: 18:58
 */

namespace app\index\controller;

use think\Db;

set_time_limit(0);
ini_set('memory_limit', '-1');

class Transfer
{
    //问卷协议同步
    public function survey()
    {
        $t1 = microtime(true);
        $dbsurvey = Db::connect('dbbry')->name('survey');
        $i = 1;
        $option = Db::name('crm_question_option')->field('id,title_id')->select();
        $questionOption = [];
        foreach ($option as $val) {
            $questionOption[$val['title_id']][] = $val['id'];
        }
        while ($i !== false) {
            $bsurvey = $dbsurvey->order('id')->where('id', 'gt', $i)->limit(500)->select();
            if (count($bsurvey) > 0) {
                $i = end($bsurvey)['id'];
            } else {
                $i = false;
                break;
            }
            Db::startTrans();
            foreach ($bsurvey as $k => $val) {
                if ($val['auditUname'] == 'admin') {
                    continue;
                }
                $question = [];
                $qk = 1;
                foreach (unserialize($val['record']) as $v) {
                    switch ($v) {
                        case 'A':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][0]
                            ];
                            break;
                        case 'B':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][1]
                            ];
                            break;
                        case 'C':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][2]
                            ];
                            break;
                        case 'D':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][3]
                            ];
                            break;
                        case 'E':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][4]
                            ];
                            break;
                    }
                    $qk++;
                }
                //问卷详情
                $questionnaire = [
                    'name' => $val['pgname'],
                    'mobile' => lock_url($val['kehutel']),
                    'id_card' => $val['pgidcard'],
                    'position' => $val['zhiye'],
                    'bad_records' => $val['chengxin'],
                    'address' => $val['txaddr'],
                    'face_id_card' => $val['cardfront'],
                    'back_id_card' => $val['cardback'],
                    'head_img' => $val['bodytalk'],
                    'score' => $val['mark'],
                    'ip' => $val['ip'],
                    'device' => $val['device'],
                    'question' => serialize($question),
                    'audit_id' => $val['auditUname'],
                    'audit_time' => $val['audittime'],
                    'audit_remark' => $val['notes'],
                    'add_time' => $val['Createtime'],
                ];
                $questionnaire_id = Db::name('crm_questionnaire')->insertGetId($questionnaire);
                if (!$questionnaire_id) {
                    continue;
                }
                //协议详情
                $protocol[] = [
                    'question_id' => $questionnaire_id,
                    'visit_class' => ($val['teacher'] > 0) ? 1 : ($val['teacher'] < 0 ? 2 : 0),
                    'teacher_id' => $val['teacher'] < 0 ? 0 : $val['teacher'],
                    'contract_no' => $val['contractno'],
                    'contract_product' => $val['resource_status'],
                    'contract_start_time' => $val['start_time'] ? $val['start_time'] : 0,
                    'contract_end_time' => $val['over_time'] ? $val['over_time'] : 0,
                    'contract_money' => $val['start_money'] ? $val['start_money'] : 0,
                    'contract_protocol_version' => $val['xYstatus'],
                    'contract_addition' => $val['mcbc'],
                    'is_new' => $val['ifnew'],
                    'aprotocol' => $val['aprotocol'],
                    'aprotocol_img' => $val['aprotocolimg'],
                    'aprotocol_time' => $val['aprotocoltime'] ? $val['aprotocoltime'] : 0,
                    'a_is_audit' => $val['a'],
                    'a_audit_time' => $val['atime'] ? $val['atime'] : 0,
                    'a_audit_id' => $val['aUname'],
                    'bprotocol' => $val['bprotocol'],
                    'bprotocol_img' => $val['bprotocolimg'],
                    'bprotocol_time' => $val['bprotocoltime'] ? $val['bprotocoltime'] : 0,
                    'b_is_audit' => $val['b'],
                    'b_audit_time' => $val['btime'] ? $val['btime'] : 0,
                    'b_audit_id' => $val['bUname'],
                    'cprotocol' => $val['protocol'],
                    'cprotocol_img' => $val['protocolimg'],
                    'cprotocol_time' => $val['protocoltime'] ? $val['protocoltime'] : 0,
                    'c_is_audit' => $val['ti'],
                    'c_audit_time' => $val['titime'] ? $val['titime'] : 0,
                    'c_audit_id' => $val['tiUname'],
                    'dprotocol' => $val['cprotocol'],
                    'dprotocol_img' => $val['cprotocolimg'],
                    'dprotocol_time' => $val['cprotocoltime'] ? $val['cprotocoltime'] : 0,
                    'd_is_audit' => $val['c'],
                    'd_audit_time' => $val['ctime'] ? $val['ctime'] : 0,
                    'd_audit_id' => $val['cUname'],
                    'eprotocol' => $val['dprotocol'],
                    'eprotocol_img' => $val['dprotocolimg'],
                    'eprotocol_time' => $val['dprotocoltime'] ? $val['dprotocoltime'] : 0,
                    'e_is_audit' => $val['d'],
                    'e_audit_time' => $val['dtime'] ? $val['dtime'] : 0,
                    'e_audit_id' => $val['dUname'],
                    'f_contract_no' => $val['contractnobc'],
                    'f_product' => $val['resource_statusbc'],
                    'f_start_time' => $val['start_timebc'] ? $val['start_timebc'] : 0,
                    'f_end_time' => $val['over_timebc'] ? $val['over_timebc'] : 0,
                    'f_money' => $val['start_moneybc'] ? $val['start_moneybc'] : 0,
                    'f_addition' => $val['mcbcbc'],
                    'fprotocol' => $val['fprotocol'],
                    'fprotocol_img' => $val['fprotocolimg'],
                    'fprotocol_time' => $val['fprotocoltime'] ? $val['fprotocoltime'] : 0,
                    'f_is_audit' => $val['f'],
                    'f_audit_time' => $val['ftime'] ? $val['ftime'] : 0,
                    'f_audit_id' => $val['fUname'],
                    'remark' => $val['notesxy'],
                    'add_id' => 0,
                    'add_time' => $val['Createtime'],
                ];
            }
            if (Db::name('crm_protocol')->insertAll($protocol)) {
                Db::commit();
            } else {
                Db::rollback();
            }
            unset ($bsurvey);
            unset ($questionnaire);
            unset ($protocol);
        }
        // ... 执行代码 ...
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //问卷协议同步
    public function delsurvey()
    {
        $t1 = microtime(true);
        $dbsurvey = Db::connect('dbbry')->name('delsurvey');
        $i = 1;
        $option = Db::name('crm_question_option')->field('id,title_id')->select();
        $questionOption = [];
        foreach ($option as $val) {
            $questionOption[$val['title_id']][] = $val['id'];
        }
        while ($i !== false) {
            $bsurvey = $dbsurvey->order('id')->where('id', 'gt', $i)->limit(500)->select();
            if (count($bsurvey) > 0) {
                $i = end($bsurvey)['id'];
            } else {
                $i = false;
                break;
            }
            foreach ($bsurvey as $k => $val) {
                $question = [];
                $qk = 1;
                foreach (unserialize($val['record']) as $v) {
                    switch ($v) {
                        case 'A':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][0]
                            ];
                            break;
                        case 'B':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][1]
                            ];
                            break;
                        case 'C':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][2]
                            ];
                            break;
                        case 'D':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][3]
                            ];
                            break;
                        case 'E':
                            $question[] = [
                                'question_id' => $qk,
                                'option' => $questionOption[$qk],
                                'checked' => $questionOption[$qk][4]
                            ];
                            break;
                    }
                    $qk++;
                }
                if ($val['auditUname'] == 'admin') {
                    continue;
                }
                //问卷详情
                $questionnaire[] = [
                    'name' => $val['pgname'],
                    'mobile' => lock_url($val['kehutel']),
                    'id_card' => $val['pgidcard'],
                    'position' => $val['zhiye'],
                    'bad_records' => $val['chengxin'],
                    'address' => $val['txaddr'],
                    'face_id_card' => $val['cardfront'],
                    'back_id_card' => $val['cardback'],
                    'head_img' => $val['bodytalk'],
                    'score' => $val['mark'],
                    'ip' => $val['ip'],
                    'device' => $val['device'],
                    'question' => serialize($question),
                    'audit_id' => $val['auditUname'],
                    'audit_time' => $val['audittime'],
                    'audit_remark' => $val['notes'],
                    'add_time' => $val['Createtime'],
                    'is_del' => 1
                ];
            }
            Db::name('crm_questionnaire')->insertAll($questionnaire);
            unset ($bsurvey);
            unset ($questionnaire);
        }
        // ... 执行代码 ...
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //同步通话记录
    public function call_record()
    {
        $t1 = microtime(true);
        $dbcall_record = Db::connect('dbbry')->name('tonghuajilu');
        $i = 0;    //分两次同步
        while ($i !== false) {
            $bcall_record = $dbcall_record->order('timestamp asc')->where('timestamp', 'gt', $i)->limit(2000)->select();
            if (count($bcall_record) > 0) {
                $i = end($bcall_record)['timestamp'];
            } else {
                $i = false;
                break;
            }
            foreach ($bcall_record as $k => $val) {
                $data[] = [
                    'callUuid' => $val['uniqueId'],
                    'timestamp' => $val['timestamp'],
                    'resource_id' => 0,
                    'user_id' => 0,
                    'job_number' => $val['usrc'],
                    'call_phone' => $val['src'] > 9999 ? lock_url($val['src']) : $val['src'],
                    'call_name' => $val['srcAgentname'],
                    'answer_phone' => $val['dst'] > 9999 ? lock_url($val['dst']) : $val['dst'],
                    'answer_name' => $val['dstAgentname'],
                    'call_time' => $val['answered'] == 1 ? $val['bcs'] : 0,
                    'fail_call_time' => $val['answered'] == 0 ? $val['bcs'] : strtotime($val['answerTime']) - strtotime($val['startTime']),
                    'total_time' => strtotime($val['endTime']) - strtotime($val['startTime']),
                    'style' => $val['callDirection'] == 'INBOUND' ? 2 : 1,
                    'direction' => $val['callDirection'],
                    'status' => $val['answered'] == 1 ? 2 : 1,
                    'sound' => $val['recordUrl'],
                    'add_time' => strtotime($val['startTime']),
                ];
            }
            Db::name('crm_call_record')->insertAll($data);
            unset($bcall_record);
            unset($data);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //通话记录userid同步
    public function call_record_user()
    {
        $t1 = microtime(true);
        $user = Db::name('user')->where('is_deal', 0)->field('id,job_number,username')->select();
        foreach ($user as $val) {
            //投顾
            DB::name('crm_call_record')->where('job_number', $val['job_number'])->update(['user_id' => $val['id']]);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //代分配资源同步
    public function resource1()
    {
        $dbresource = Db::connect('dbbry')->name('resourcelist2');
        $i = 0;
        while ($i !== false) {
            $bresource = $dbresource->order('id asc')->where('id', 'gt', $i)->limit(1000)->select();
            if (count($bresource) > 0) {
                $i = end($bresource)['id'];
            } else {
                $i = false;
                break;
            }
            foreach ($bresource as $k => $val) {
                $data[] = [
                    'name' => $val['Name'],
                    'age' => $val['Age'],
                    'sex' => $val['Sex'] == '男' ? 1 : ($val['Sex'] == '女' ? 2 : 0),
                    'mobile' => lock_url($val['Mobile']),
                    'job' => $val['Job'],
                    'customer_address' => $val['Addr'],
                    'resource_type' => $val['source'],
                    'resource_note' => $val['notes'],
                    'stock_code' => $val['gpdm'],
                    'source_address' => $val['lydz'],
                    'source_page' => $val['lyym'],
                    'source_detail' => $val['lymx'],
                    'status' => 1,
                    'add_time' => $val['Createtime']
                ];
            }
            Db::name('crm_resource_ori')->insertAll($data);
        }
    }

    //待开发和意向客户
    public function resource2()
    {
        $t1 = microtime(true);
        $dbresource = Db::connect('dbbry');
        $i = 0;
        while ($i >= 0) {
            $bresource = $dbresource->name('my_resource')->order('id asc')->where('id', 'gt', $i)->limit(500)->select();
            if (count($bresource) > 0) {
                $i = end($bresource)['id'];
            } else {
                $i = -1;
                break;
            }
            $insert = [];
            foreach ($bresource as $k => $val) {
                if (in_array($val['Createtime'] . $val['Mobile'], $insert)) {
                    continue;
                }
                array_push($insert, $val['Createtime'] . $val['Mobile']);
                if (!is_numeric($val['Uname'])) {
                    continue;
                }
                $id = Db::name('crm_resource_ori')->where('mobile', lock_url($val['Mobile']))->where('tg_job_number', $val['Uname'])->where('pay_type', $val['Touzijingli'])->where('status', 'lt', 10)->value('id');
                if ($val['tel_status'] == '已接听') {
                    $val['tel_status'] = 82;
                } elseif ($val['tel_status'] == '未接听') {
                    $val['tel_status'] = 80;
                } else {
                    $val['tel_status'] = null;
                }
                $data[$k] = [
                    'name' => $val['Name'],
                    'age' => $val['Age'],
                    'sex' => $val['Sex'] == '男' ? 1 : ($val['Sex'] == '女' ? 2 : 0),
                    'job' => $val['Job'],
                    'mobile' => lock_url($val['Mobile']),
                    'weixin' => $val['wx'],
                    'qq' => $val['qq'],
                    'pay_type' => $val['Touzijingli'],
                    'tg_job_number' => $val['Uname'],
                    'id_card' => $val['kehucard'],
                    'bd_status' => $val['tel_status'],
                    'install_version' => $val['resource_status'],
                    'gz_zk' => $val['work_status'],
                    'money' => $val['start_money'],
                    'resource_start_time' => $val['Createtime'],
                    'resource_end_time' => $val['over_time'],
                    'intention' => $val['inte_status'],
                    'listen_status' => $val['tingke'] == '是' ? 1 : 0,
                    'listen_note' => $val['tingkeno'],
                    'transfer_proof' => $val['cardfront'],
                    'add_file' => $val['cardback'],
                    'customer_address' => $val['Addr'],
                    'resource_type' => $val['source'],
                    'resource_note' => $val['notes'],
                    'status' => $val['inte_status'] ? 3 : 2,
                    //'add_time' => $val['Createtime']
                ];
                if ($id) {
                    $data[$k]['id'] = $id;
                } else {
                    $data[$k]['add_time'] = $val['Createtime'];
                }
            }
            $user = model('crm_resource_ori');
            $user->saveAll($data);
            unset($data);
            unset($bresource);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //安装单申请表
    public function resource3()
    {
        $t1 = microtime(true);
        $dbresource = Db::connect('dbbry');
        $i = 0;
        $city = $dbresource->name('diqu')->column('code,name,topcode', 'code');
        $updateCity = Db::name('zone')->where('level', 'lt', 3)->column('name,id');
        while ($i !== false) {
            $bresource = $dbresource->name('openapply')->where('id', 'gt', $i)->limit(500)->select();
            if (count($bresource) > 0) {
                $i = end($bresource)['id'];
            } else {
                $i = false;
                break;
            }
            $insert = [];
            foreach ($bresource as $k => $val) {
                if (in_array($val['Createtime'] . $val['kehutel'], $insert)) {
                    continue;
                }
                array_push($insert, $val['Createtime'] . $val['kehutel']);
                //测试数据过滤掉
                if (!is_numeric($val['Uname']) || ($val['timan'] && !is_numeric($val['timan'])) || ($val['operaman'] && !is_numeric($val['operaman'])) || ($val['rujinman'] && !is_numeric($val['rujinman'])) || ($val['jihuoman'] && !is_numeric($val['jihuoman'])) || ($val['Kfzg_Uname_man'] && !is_numeric($val['Kfzg_Uname_man'])) || ($val['Kf_Uname'] && !is_numeric($val['Kf_Uname']))) {
                    continue;
                }

                $id = Db::name('crm_resource_ori')->where('mobile', lock_url($val['kehutel']))->where('tg_job_number', $val['Uname'])->where('pay_type', $val['Touzijingli'])->where('status', 'lt', 10)->where('resource_start_time', 'null')->value('id');
                if ($val['Kf_Uname']) {  //已分配客服
                    if ($val['opera'] == -1) {
                        if (!$val['Kfzg_Uname_man']) {
                            $status = 8;
                        } else {
                            $status = 10;
                        }
                    } else {
                        if (!$val['timan']) {
                            $status = 10;
                        } elseif (!$val['operaman']) {
                            $status = 5;
                        } elseif (!$val['rujinman']) {
                            $status = 6;
                        } elseif (!$val['jihuoman']) {
                            $status = 7;
                        } elseif (!$val['Kfzg_Uname_man']) {
                            $status = 8;
                        } else {
                            $status = 10;
                        }
                    }
                } elseif ($val['Kfzg_Uname_man']) {  //已分配客服主管
                    $status = 9;
                } elseif ($val['jihuoman']) {  //风控电话审核通过
                    $status = 8;
                } elseif ($val['rujinman']) {  //风控合同审核通过
                    $status = 7;
                } elseif ($val['operaman']) {  //风控已分单
                    $status = 6;
                } elseif ($val['timan']) {  //财务审核通过
                    if ($val['opera'] == -1) {
                        if ($val['Kfzg_Uname_man']) {
                            $status = 9;
                        } else {
                            $status = 8;
                        }
                    } else {
                        $status = 5;
                    }
                } else {    //已申请开户
                    $status = 4;
                }

                $data[$k] = [
                    'name' => $val['kehuname'],
                    'age' => $val['Age'],
                    'sex' => $val['Sex'] == '男' ? 1 : ($val['Sex'] == '女' ? 2 : 0),
                    'mobile' => lock_url($val['kehutel']),
                    'spare_mobile' => lock_url($val['kehutel2']),
                    'weixin' => $val['kehuwx'],
                    'qq' => $val['kehuqq'],
                    'pay_type' => $val['Touzijingli'],
                    'tg_job_number' => $val['Uname'],
                    'id_card' => $val['kehuidcard'],
                    'bank_id' => $val['bankname'],
                    'open_apply_time' => $val['Createtime'],
                    'finance_verify_note' => $val['remark'],
                    'finance_verify_id' => $val['timan'],
                    'finance_verify_time' => $val['titime'],
                    'risk_fd_id' => $val['operaman'],
                    'risk_fd_time' => $val['operatime'],
                    'risk_pact_id' => $val['Jy_Uname'],
                    'risk_pact_sn' => $val['contractno'],
                    'pact_start_time' => $val['start_time'],
                    'pact_end_time' => $val['over_time'],
                    'pact_confirm_id' => $val['rujinman'],
                    'pact_confirm_time' => $val['rujintime'],
                    'risk_call_id' => $val['jihuoman'],
                    'risk_call_time' => $val['jihuotime'],
                    'allot_kefu_leader_id' => $val['Kfzg_Uname_man'],
                    'allot_kefu_leader_time' => $val['Kfzg_Uname_time'],
                    'kefu_leader_id' => $val['Kf_Uname_man'] ? $val['Kf_Uname_man'] : $val['Kfzg_Uname'],
                    'kefu_leader_time' => $val['Kf_Uname_time'],
                    'kefu_id' => $val['Kf_Uname'],
                    'customer_status' => $val['khstate'] == 4 ? ($val['khstatec'] ? 1 . $val['khstatec'] : $val['khstate']) : $val['khstate'],                 //C类有子类
                    'install_version' => $val['resource_status'],
                    'money' => $val['start_money'],
                    'intention' => $val['inte_status'],
                    'listen_status' => $val['tingke'] == '是' ? 1 : 0,
                    'listen_note' => $val['tingkeno'],
                    'transfer_proof' => $val['cardfront'],
                    'add_file' => $val['cardback'],
                    'customer_address' => $val['kehuaddr'],
                    'customer_brithday' => $val['khsr'],
                    'gold_intention' => $val['jjyx'],
                    'gift_info' => $val['lipin'],
                    'is_kfentry' => $val['rglr'],
                    'return_visit_type' => $val['return_visit_type'],
                    'return_visit_time' => $val['return_visit_time'],
                    'last_visit_time' => $val['last_visit_time'],
                    'status' => $status
                ];
                if ($id) {
                    $data[$k]['id'] = $id;
                } else {
                    $data[$k]['add_id'] = $val['rglrno'] ? $val['rglrno'] : 0;
                    $data[$k]['add_time'] = $val['Createtime'];
                }
                if ($val['city']) {
                    $data[$k]['province_id'] = isset($updateCity['' . $city[$city[$val['city']]['topcode']]['name'] . '']) ? $updateCity['' . $city[$city[$val['city']]['topcode']]['name'] . ''] : 0;
                    $data[$k]['city_id'] = isset($updateCity['' . $city[$val['city']]['name'] . '']) ? $updateCity['' . $city[$val['city']]['name'] . ''] : 0;
                }
            }
            $user = model('crm_resource_ori');
            $user->saveAll($data);
            unset($bresource);
            unset($data);
            unset($insert);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //资源备注，分开同步
    public function notest()
    {
        $t1 = microtime(true);
        $dbresource = Db::connect('dbbry');
        $i = 0;
        while ($i !== false) {
            $bresource = $dbresource->name('notest')->order('id ASC')->where('id', 'gt', $i)->limit(500)->select();
            if (count($bresource) > 0) {
                $i = end($bresource)['id'];
            } else {
                $i = false;
                break;
            }
            foreach ($bresource as $val) {
                $oid = $dbresource->name('openapply')->where('id', $val['Uid'])->field('kehutel,Createtime')->find();
                $id = Db::name('crm_resource_ori')->where('mobile', lock_url($oid['kehutel']))->where('open_apply_time', $oid['Createtime'])->value('id');
                $data[] = [
                    'resource_id' => $id,
                    'content' => $val['notes'],
                    'add_id' => $val['source'],
                    'add_time' => $val['createtime'],
                ];
            }
            Db::name('crm_note_log')->insertAll($data);
            unset($bresource);
            unset($data);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //待操作资源
    public function resource4()
    {
        $t1 = microtime(true);
        $dbresource = Db::connect('dbbry')->name('wait_check');
        $i = 0;
        while ($i !== false) {
            $bresource = $dbresource->order('id asc')->where('id', 'gt', $i)->where('id', 'gt', $i)->limit(1000)->select();
            if (count($bresource) > 0) {
                $i = end($bresource)['id'];
            } else {
                $i = false;
                break;
            }
            foreach ($bresource as $k => $val) {
                $data[] = [
                    'name' => $val['Name'],
                    'age' => $val['Age'],
                    'sex' => $val['Sex'] == '男' ? 1 : ($val['Sex'] == '女' ? 2 : 0),
                    'mobile' => lock_url($val['Mobile']),
                    'job' => $val['Job'],
                    'customer_address' => $val['Addr'],
                    'resource_type' => $val['source'] ? $val['source'] : 0,
                    'resource_note' => $val['notes'],
                    'stock_code' => $val['gpdm'],
                    'source_address' => $val['lydz'],
                    'source_page' => $val['lyym'],
                    'source_detail' => $val['lymx'],
                    'status' => 1,
                    'add_time' => $val['Createtime'],
                    'add_type' => 2
                ];
            }
            Db::name('crm_resource_ori')->insertAll($data);
            unset($data);
            unset($bresource);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //
    public function delete()
    {
        $t1 = microtime(true);
        $dbresource = Db::connect('dbbry')->name('del_resource');
        $i = 0;
        while ($i !== false) {
            $bresource = $dbresource->order('id asc')->where('id', 'gt', $i)->limit(500)->select();
            if (count($bresource) > 0) {
                $i = end($bresource)['id'];
            } else {
                $i = false;
                break;
            }
            foreach ($bresource as $k => $val) {
                $data[] = [
                    'name' => $val['Name'],
                    'age' => $val['Age'],
                    'sex' => $val['Sex'] == '男' ? 1 : ($val['Sex'] == '女' ? 2 : 0),
                    'mobile' => lock_url($val['Mobile']),
                    'job' => $val['Job'],
                    'customer_address' => $val['Addr'],
                    'tg_job_number' => $val['Uname'],
                    'pay_type' => $val['Touzijingli'],
                    'install_version' => $val['resource_status'],
                    'gz_zk' => $val['work_status'],
                    'money' => $val['start_money'],
                    'resource_type' => $val['source'],
                    'resource_note' => $val['notes'],
                    'stock_code' => $val['gpdm'],
                    'source_address' => $val['lydz'],
                    'source_page' => $val['lyym'],
                    'source_detail' => $val['lymx'],
                    'delete_status' => 1,
                    'delete_time' => $val['Createtime'],
                    'add_time' => $val['Createtime'],
                    'add_type' => 2,
                ];
            }
            Db::name('crm_resource_ori')->insertAll($data);
            unset($data);
            unset($bresource);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    public function invalid()
    {
        $t1 = microtime(true);
        $dbresource = Db::connect('dbbry')->name('wuxiao_resource');
        $i = 0;
        while ($i !== false) {
            $bresource = $dbresource->order('id asc')->where('id', 'gt', $i)->limit(500)->select();
            if (count($bresource) > 0) {
                $i = end($bresource)['id'];
            } else {
                $i = false;
                break;
            }
            foreach ($bresource as $k => $val) {
                $data[] = [
                    'name' => $val['Name'],
                    'age' => $val['Age'],
                    'sex' => $val['Sex'] == '男' ? 1 : ($val['Sex'] == '女' ? 2 : 0),
                    'mobile' => lock_url($val['Mobile']),
                    'job' => $val['Job'],
                    'customer_address' => $val['Addr'],
                    'tg_job_number' => $val['Uname'],
                    'pay_type' => $val['Touzijingli'],
                    'install_version' => $val['resource_status'],
                    'gz_zk' => $val['work_status'],
                    'money' => $val['start_money'],
                    'resource_type' => $val['source'],
                    'resource_note' => $val['notes'],
                    'stock_code' => $val['gpdm'],
                    'source_address' => $val['lydz'],
                    'source_page' => $val['lyym'],
                    'source_detail' => $val['lymx'],
                    'invalid_status' => 1,
                    'invalid_time' => $val['Createtime'],
                    'add_time' => $val['Createtime'],
                    'add_type' => 2,
                ];
            }
            Db::name('crm_resource_ori')->insertAll($data);
            unset($data);
            unset($bresource);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    public function test()
    {
        echo lock_url(18757507501);
        //echo unlock_url('Aw4xZEM5Ux4tqGIkk');
        //$dbresource = Db::connect('dbbry1')->name('shouhui_resource');
        //dump($dbresource->select());
    }

    public function note()
    {
        $t1 = microtime(true);
        $user = Db::name('user')->where('is_deal', 0)->field('id,job_number,username')->select();

        foreach ($user as $val) {
            DB::name('crm_note_log')->where('add_id', $val['job_number'])->update(['add_id' => $val['id']]);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //同步资源将工号改成ID
    public function user()
    {
        $t1 = microtime(true);
        $user = Db::name('user')->where('is_deal', 0)->field('id,job_number,username')->select();
        foreach ($user as $val) {
            //投顾
            DB::name('crm_resource_ori')->where('tg_job_number', $val['job_number'])->where('tg', 0)->update(['tg_id' => $val['id'], 'tg_name' => $val['username'], 'tg' => 1]);
            //财务审核
            DB::name('crm_resource_ori')->where('finance_verify_id', 'gt', 0)->where('finance_verify_id', $val['job_number'])->where('finance_t', 0)->update(['finance_verify_id' => $val['id'], 'finance_t' => 1]);
            //风控分单
            DB::name('crm_resource_ori')->where('risk_fd_id', 'gt', 0)->where('risk_fd_id', $val['job_number'])->where('risk_t', 0)->update(['risk_fd_id' => $val['id'], 'risk_t' => 1]);
            //风控合同
            DB::name('crm_resource_ori')->where('risk_pact_id', 'gt', 0)->where('risk_pact_id', $val['job_number'])->where('risk_pt', 0)->update(['risk_pact_id' => $val['id'], 'risk_pt' => 1]);
            //合同确认人
            DB::name('crm_resource_ori')->where('pact_confirm_id', 'gt', 0)->where('pact_confirm_id', $val['job_number'])->where('pact_t', 0)->update(['pact_confirm_id' => $val['id'], 'pact_t' => 1]);
            DB::name('crm_resource_ori')->where('risk_call_id', 'gt', 0)->where('risk_call_id', $val['job_number'])->where('risk_ct', 0)->update(['risk_call_id' => $val['id'], 'risk_ct' => 1]);
            //分配客服主管人
            DB::name('crm_resource_ori')->where('allot_kefu_leader_id', 'gt', 0)->where('allot_kefu_leader_id', $val['job_number'])->where('allot_ht', 0)->update(['allot_kefu_leader_id' => $val['id'], 'allot_ht' => 1]);
            //客服主管
            DB::name('crm_resource_ori')->where('kefu_leader_id', 'gt', 0)->where('kefu_leader_id', $val['job_number'])->where('kefu_lt', 0)->update(['kefu_leader_id' => $val['id'], 'kefu_lt' => 1]);
            //客服专员
            DB::name('crm_resource_ori')->where('kefu_id', 'gt', 0)->where('kefu_id', $val['job_number'])->where('kefu_t', 0)->update(['kefu_id' => $val['id'], 'kefu_t' => 1]);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    public function yeji()
    {
        $t1 = microtime(true);
        $dbresource = Db::connect('dbbry')->name('yjkh');
        $i = 0;
        while ($i !== false) {
            $bresource = $dbresource->order('id')->where('id', 'gt', $i)->limit(500)->select();
            if (count($bresource) > 0) {
                $i = end($bresource)['id'];
            } else {
                $i = false;
                break;
            }
            foreach ($bresource as $k => $val) {
                $data[] = [
                    'add_friend_num' => $val['friend'],
                    'add_group_num' => $val['ugroup'],
                    'return_visit_num' => $val['callback'],
                    'achievement_num' => lock_url($val['yeji']),
                    'add_id' => 0,
                    'job_number' => $val['Uname'],
                    'add_time' => $val['addtime'],
                ];
            }
            Db::name('crm_achievement')->insertAll($data);
            unset($data);
            unset($bresource);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    //问卷协议审核人
    public function question()
    {
        $t1 = microtime(true);
        $user = Db::name('user')->where('is_deal', 0)->field('id,job_number,username')->select();

        foreach ($user as $val) {
            DB::name('crm_questionnaire')->where('audit_id', $val['job_number'])->update(['audit_id' => $val['id']]);
        }
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    public function role()
    {
        $t1 = microtime(true);
        $user = Db::name('user')->where('is_deal', 0)->field('id,job_number,username')->select();

        foreach ($user as $val) {
            if (Db::name('auth_group_access')->where('uid', $val['id'])->find()) {
                continue;
            }
            if ($val['job_number'] < 8000 || ($val['job_number'] > 8500 && $val['job_number'] < 9000)) {
                if ($val['job_number'] % 1000 == 0) {
                    $group_id = 101;
                } elseif ($val['job_number'] % 1000 > 0 && $val['job_number'] % 1000 < 10) {
                    $group_id = 102;
                } elseif ($val['job_number'] % 25 > 0) {
                    $group_id = 112;
                } elseif ($val['job_number'] % 25 == 0) {
                    $group_id = 113;
                }
                $data[] = [
                    'uid' => $val['id'],
                    'group_id' => $group_id,
                ];
            }
        }
        Db::name('auth_group_access')->insertAll($data);
        $t2 = microtime(true);
        echo '处理完成，耗时' . round($t2 - $t1, 3) . '秒';
    }

    public function yonghu()
    {
        $data = Db::query("select * from ea_user where d_id is null");

        foreach ($data as $val) {
            $d_id = Db::name('department')->where('code', $val['job_number'] - $val['job_number'] % 25)->value('id');
            $new1[] = $val['job_number'];
            $new[] = $d_id;
            Db::name('user')->where('is_deal', 0)->where('id', $val['id'])->update(['d_id' => $d_id]);
        }
        exit;
    }

    //更新问卷协议股市资产和赠送天数
    public function questionnaire_update()
    {
//        $db = Db::connect('dbbry')->name('survey')->where('gszc', 'gt', 0)->field('kehutel,gszc')->select();
//        dump($db);
//        foreach($db as $val){
//            Db::name('crm_questionnaire')->where('mobile',lock_url($val['kehutel']))->update(['asset'=>$val['gszc']]);
//        }
        $db = Db::connect('dbbry')->name('survey')->where('sendmonth', 'gt', 0)->whereOr('sendmonthbc', 'gt', 0)->field('kehutel,sendmonth,sendmonthbc')->select();
        foreach ($db as $val) {
            $id = Db::name('crm_questionnaire')->where('mobile', lock_url($val['kehutel']))->value('id');
            Db::name('crm_protocol')->where('question_id', $id)->update(['contract_give_days' => $val['sendmonth'], 'f_give_days' => $val['sendmonthbc']]);
            unset($id);
        }
        echo 'success';
    }

    //更新绑定参数
    public function front_update()
    {
        $list = Db::name('crm_resource')->where('status', 'gt', 3)->where('money', 'gt', 0)->where('front_id is null')->field('id,name,mobile,pay_type,intention,status,front_id,money')->order('add_time')->select();
        $data = [];
        foreach ($list as $val) {
            if ($val['mobile']) {
                $data[$val['mobile']][] = $val;
            }
        }
        foreach ($data as $key => $val) {
            $name = $val[0]['name'];
            foreach ($val as $v) {
                if ($v['name'] != $name) {
                    unset($data[$key]);
                }
            }
            unset($name);
            if (count($val) < 2) {
                unset($data[$key]);
            }
        }
        $due_ids = '';
        $front_ids = '';
        $records_ids = '';
        foreach ($data as $val) {
            foreach ($val as $k => $v) {
                if ($k === 0) {
                    $due_ids .= ',' . $v['id'];
                    Db::name('crm_resource_flow')->where('resource_id', $v['id'])->update(['due_status' => 0]);
                    continue;
                }
                $records_ids .= ',' . Db::name('crm_pay_records')->insertGetId(['resource_id' => $v['id'], 'pid' => $val[$k - 1]['id'], 'first_pid' => $val[0]['id'], 'user_id' => 1, 'add_time' => time()]);
                if ($k < count($val) - 1) {
                    $due_ids .= ',' . $v['id'];
                    Db::name('crm_resource_flow')->where('resource_id', $v['id'])->update(['due_status' => 0]);
                }
                $front_ids .= ',' . $v['id'];
                Db::name('crm_resource')->where('id', $v['id'])->update(['front_id' => $val[$k - 1]['id']]);
            }
        }
        file_put_contents('due_ids.txt',$due_ids);
        file_put_contents('front_ids.txt',$front_ids);
        file_put_contents('records_ids.txt',$records_ids);
        echo 'success';
    }
}