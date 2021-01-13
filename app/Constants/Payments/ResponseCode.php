<?php


namespace App\Constants\Payments;


class ResponseCode
{
    // todo use interface for temp, not testing yet
    public const SUCCESS                        = 100;
    public const FAIL                           = 101;  // 传送失败
    public const RESOURCE_NOT_FOUND             = 107;  // 查無資料
    public const ERROR_PARAMETERS               = 111;  // 请输入完整信息
    public const ERROR_CONFIG_PARAMETERS        = 157;  // 設定檔無此參數
    public const DATABASE_FAILED                = 120;  // 资料库错误
    public const DUPLICATE_ORDERID              = 302;  // 已有该订单


/**  參考error code 以下要用的時候要全部 + 100
    "platFormCode": {
    '001':'传送失败',
    '002':'登入異常',
    '003':'Token 过期',
    '004':'查无权限设定',
    '005':'权限未开放',
    '006':'参数错误（查無 token）',
    '007':'查无资料',
    '008':'发生例外',
    '009':'资料错误',
    '010':'登入参数错误',
    '011':'请输入完整信息',
    '012':'档案格式错误',
    '013':'新增失败!请选择第三方插件',
    '014':'新增失败!请选择支付方式类别',
    '015':'新增失败!每个项目下,只能有一笔类别',
    '016':'序号不可重复',
    '017':'未设定IP',
    '018':'账号或密码错误',
    '019':'账户资讯错误',
    '020':'资料库错误',
    '021':'目标路径错误',
    '022':'档案内无内容',
    '023':'不可操作建立时间超过48小时的订单',
    '024':'不可操作建立时间超过2个月以上的订单',
    '025':'此订单不存在',
    '026':'请输入会员账号',
    '027':'必须为未入账的快速充值订单（M开头的订单号）',
    '028':'修改会员账号失败',
    '029':'密码变更参数错误',
    '030':'旧密码与新密码不可相同',
    '031':'变更失败-旧密码输入错误',
    '032':'变更失败',
    '033':'只能修改未处理订单',
    '034':'不可修改第三方、快速充值的来源端',
    '035':'信息错误-请重新整理!!',
    '036':'选单错误-请重新整理',
    '037':'金额范围不可小于1',
    '038':'金额范围错误!最大金额不可小于最小金额',
    '039':'信息不可为空白',
    '040':'上传图片格式错误,副档名必须为: jpg或png或gif,档案大小:1.5MB(1536Kb)以下',
    '041':'图片路径错误',
    '042':'查询开始时间不能大于结束时间',
    '043':'设定失败!快速充值只能有一笔第四方设定',
    '044':'请设定可用层级',
    "045":"会员账号错误-无此会员",
    "046":"最小金额不可大于最大金额",
    "047":"修改失败!请选择第三方插件",
    "048":"修改失败!请选择支付方式类别",
    "049":"修改失败!每个项目下,只能有一笔类别",
    "050":"超过最大查询范围31天!",
    "051":'账号重复',
    "052":"彩票系统异常,请稍后操作",
    "053":"IP重复输入",
    "054":"订单状态已变更，请刷新操作",
    '055':'信息重复',
    '056':'输入格式错误',
    '100':'资料库异常错误',
    '101':'资料库错误 - 资料重复',
    '201':'无此支付方式资讯',
    '202':'已有该订单',
    '203':'无此通道资讯',
    '204':'无此通道资讯',
    '205':'无此订单',
    '206':'系统错误',
    '207':'无此渠道资讯',
    '208':'无此支付方式资讯',
    '209':'此支付方式维护中',
    '210':'无此支付方式资讯',
    '211':'系统错误',
    '212':'无此订单',
    '213':'操作时间过长，请重新下单！',
    '214':'无支付方式资讯',
    '215':'图片档案错误,请重新截图上传',
    '216':'上传格式错误请重新上传或联络客服',
    "999": " 使用者尚未登录",
},
 */


}