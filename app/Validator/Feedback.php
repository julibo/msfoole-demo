<?php
/**
 *  异常集合
 */

namespace App\Validator;

class Feedback
{
    public static $Exception = [
        // 权限验证
        'AUTH_SIGN_ERROR' => ['code' => 30, 'msg' => '签名验证失败'],
        'AUTH_TOKEN_ERROR' => ['code' => 31, 'msg' => '令牌验证失败'],
        'AUTH_OVERSTEP_ERROR' => ['code' => 32, 'msg' => '权限验证失败'],
        // 操作异常
        'HANDLE_DADA_CHECK' => ['code' => 40, 'msg' => '操作对象不存在'],
        'HANDLE_ABNORMAL' => ['code' => 41, 'msg' => '非法操作'],

        // 参数异常
        'PARAMETER_MISSING' => ['code' => 50, 'msg' => '缺少必要的参数'],
        'PARAMETER_TYPE_ERROR' => ['code' => 51, 'msg' => '参数类型不合法'],
        'PARAMETER_FORMAT_ERROR' => ['code' => 52, 'msg' => '参数格式不合法'],
        'PARAMETER_LENGTH_LONG' => ['code' => 52, 'msg' => '参数长度太长'],
        'PARAMETER_LENGTH_SHORT' => ['code' => 54, 'msg' => '参数长度太短'],
        'PARAMETER_OUT_RANGE' => ['code' => 55, 'msg' => '参数超出范围'],
        // 其他异常
        'INTERFACE_EXCEPTION' => ['code' => 90, 'msg' => 'API接口异常'],

    ];


}
