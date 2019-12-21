<?php
namespace app\common\service;

use app\common\model\ClassroomCate;
use app\exception\BaseException;
use think\Db;
use think\Exception;
use think\Model;
use traits\controller\Jump;

/**
 * 实现对think\model的CRUD
 * @author gaofeng
 * Class ModelService
 * @package app\common\service
 */
class ModelService
{
    use Jump;
    protected $model;

    public function __construct(Model $model = null)
    {
        $this->model = $model;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }


    /**
     * 更新model的相关字段
     * @param $dataToUpdate
     * @throws \think\exception\PDOException
     * @throws BaseException
     */
    public function update($dataToUpdate)
    {
        $this->model->startTrans();
        try{
            $this->model->isUpdate(true)->save($dataToUpdate);
        }catch (Exception $e) {
            $this->model->rollback();
            throw new BaseException("更新失败".$e->getMessage());
        }
        $this->model->commit();
    }

    /**
     * 增加新的model
     * @param $dataToStore
     * @throws BaseException
     */
    public function store($dataToStore)
    {
        Db::startTrans();
        try{
            $this->model->save($dataToStore);

        }catch (Exception $exception) {
            Db::rollback();
            throw new BaseException("新增失败".$exception->getMessage());
        }
        Db::commit();
    }

    /**
     * 删除model
     * @throws BaseException
     */
    public function destroy()
    {
        Db::startTrans();
        try{
           $this->model->delete();
        }catch (Exception $exception) {
            Db::rollback();
            throw new BaseException("删除失败".$exception->getMessage());
        }
        Db::commit();
    }
}
