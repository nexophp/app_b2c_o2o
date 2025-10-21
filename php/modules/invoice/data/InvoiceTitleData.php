<?php

namespace modules\invoice\data;

class InvoiceTitleData
{
    /**
     * 获取发票类型映射
     */
    public static function getTypeMap()
    {
        return [
            'personal' => '个人',
            'company' => '企业'
        ];
    }

    /**
     * 获取状态映射
     */
    public static function getStatusMap()
    {
        return [
            0 => '禁用',
            1 => '启用'
        ];
    }

    /**
     * 验证发票抬头数据
     */
    public static function validate($data)
    {
        $errors = [];

        if (empty($data['user_id'])) {
            $errors[] = '用户ID不能为空';
        }

        if (empty($data['type'])) {
            $errors[] = '发票类型不能为空';
        } elseif (!in_array($data['type'], ['personal', 'company'])) {
            $errors[] = '无效的发票类型';
        }

        if (empty($data['title'])) {
            $errors[] = '发票抬头不能为空';
        }

        // 企业发票必须填写纳税人识别号
        if ($data['type'] === 'company' && empty($data['tax_number'])) {
            $errors[] = '企业发票必须填写纳税人识别号';
        }

        // 验证纳税人识别号格式
        if (!empty($data['tax_number']) && !preg_match('/^[0-9A-Z]{15,20}$/', $data['tax_number'])) {
            $errors[] = '纳税人识别号格式不正确';
        }

        return $errors;
    }

    /**
     * 获取用户发票抬头列表
     */
    public static function getUserTitles($user_id, $status = 1)
    {
        $model = \modules\invoice\model\InvoiceTitleModel::model();
        $where = [
            'user_id' => $user_id,
            'deleted_at' => 0,
            'ORDER' => ['is_default' => 'DESC', 'id' => 'DESC']
        ];

        if ($status !== null) {
            $where['status'] = $status;
        }

        return $model->find($where);
    }

    /**
     * 获取用户默认发票抬头
     */
    public static function getUserDefaultTitle($user_id)
    {
        $model = \modules\invoice\model\InvoiceTitleModel::model();
        return $model->findOne([
            'user_id' => $user_id,
            'is_default' => 1,
            'status' => 1, 
        ]);
    }

    /**
     * 设置为默认发票抬头
     */
    public static function setAsDefault($id, $user_id)
    {
        $model = \modules\invoice\model\InvoiceTitleModel::model();

        // 验证发票抬头是否属于该用户
        if (!self::validateUserTitle($id, $user_id)) {
            return false;
        }

        // 先将该用户的所有发票抬头设为非默认
        $model->update(['is_default' => 0], ['user_id' => $user_id],true);

        // 设置指定的为默认
        return $model->update(['is_default' => 1], ['id' => $id],true);
    }

    /**
     * 验证发票抬头是否属于用户
     */
    public static function validateUserTitle($id, $user_id)
    {
        $model = \modules\invoice\model\InvoiceTitleModel::model();
        $title = $model->findOne([
            'id' => $id,
            'user_id' => $user_id, 
        ]);

        return $title ? true : false;
    }
}
