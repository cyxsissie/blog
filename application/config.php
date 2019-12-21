<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    //设置模板缓存  开发为false 线上为true
    'TMPL_CACHE_ON' => false,
    // 应用调试模式
    'app_debug'              => TRUE,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => 'trim',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'admin',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'adminindex',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => true,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,
    // 全局请求缓存排除规则
    'request_cache_except'   => [],

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [

        '__DATA__'   => 'data/',


        '__UPLOAD__'=>  '/data/upload',
        '__JS__'=>  '/static/assets/js',
        '__CSS__'=> '/static/assets/css',
        '__IMG__'=>  '/static/assets/img',
        '__FONTS__'=>  '/static/assets/fonts',
        '__PLUGINS__'=>  '/static/assets/plugins',
    ],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => ['error'], //只记录错误日志
        // //日志文件最多只会保留30个
        // 'max_files' => 30,
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
        //redis
        'REDIS_W_HOST'=>'127.0.0.1',
        'REDIS_W_PORT'=>6379,

        'REDIS_R_HOST'=>'127.0.0.1',
        'REDIS_R_PORT'=>6379,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],

    //反馈类型
    'feedback_style' => [
        1 => '系统及其它问题反馈',
        2 => '意见及建议反馈',
    ],
    //反馈答复
    'answer_style' => [
        1 => '未答复',
        2 => '已答复',
    ],

    //用户等级配置
    'user_star_config' => [
        0 => '无',
        1 => '一星',
        2 => '二星',
        3 => '三星',
        4 => '四星',
        5 => '五星',
    ],

    //七牛云配置
    'qiniu' =>[
        'ACCESSKEY' => 'FE6sptTXQs57oiysCE0QlPPcK_7uUQXSO_jFCizX',//你的accessKey
        'SECRETKEY' => 'euvb5hb68sXu9HsrSx7SOIGDvtNoK7u9NmGyXuHv',//你的secretKey
        'BUCKET' => 'qiyue',//上传的空间
        'DOMAIN'=>'http://img.ahjsn.com/',//空间绑定的域名
    ],

    //短信签名
    'sms_sign' => '百瑞赢',
    //短信错误码列表
    'ali_code_config' => array(
        'isp.RAM_PERMISSION_DENY'=> 'RAM权限DENY',
        'isv.OUT_OF_SERVICE'=> '业务停机',
        'isv.PRODUCT_UN_SUBSCRIPT'=> '未开通云通信产品的阿里云客户',
        'isv.PRODUCT_UNSUBSCRIBE'=> '产品未开通',
        'isv.ACCOUNT_NOT_EXISTS'=> '账户不存在',
        'isv.ACCOUNT_ABNORMAL'=> '账户异常',
        'isv.SMS_TEMPLATE_ILLEGAL'=> '短信模板不合法',
        'isv.SMS_SIGNATURE_ILLEGAL'=> '短信签名不合法',
        'isv.INVALID_PARAMETERS'=> '参数异常',
        'isp.SYSTEM_ERROR'=> '系统错误',
        'isv.MOBILE_NUMBER_ILLEGAL'=> '非法手机号',
        'isv.MOBILE_COUNT_OVER_LIMIT'=> '手机号码数量超过限制',
        'isv.TEMPLATE_MISSING_PARAMETERS'=> '模板缺少变量',
        'isv.BUSINESS_LIMIT_CONTROL'=> '今日短信次数已用完，请明日再试',
        'isv.INVALID_JSON_PARAM'=> 'JSON参数不合法，只接受字符串值',
        'isv.BLACK_KEY_CONTROL_LIMIT'=> '黑名单管控',
        'isv.PARAM_LENGTH_LIMIT'=> '参数超出长度限制',
        'isv.PARAM_NOT_SUPPORT_URL'=> '不支持URL',
        'isv.AMOUNT_NOT_ENOUGH'=> '账户余额不足',
    ),

    //通话记录中接听状态
    'call_status_config' => [
        1 => '未接听',
        2 => '已接听',
        3 => '被挂断',
    ],

    //手机加密参数配置
    'lock_phone_key'  => 'Jushuoniu_@+&%',

    //资源增加自动注册前端用户时默认密码
    'default_pwd'   => '123456',

    //客户手机展示状态
    'mobile_hide_status' => [
        0 => '加密展示',
        1 => '解密展示',
        2 => '完全隐藏',
        3 => '中间隐藏',
    ],

    //投资风险承受能力类型
    'invest_type' => [
        1 => [
            'score' => [0,20],
            'type' => '保守型'
        ],
        2 => [
            'score' => [20,40],
            'type' => '谨慎型'
        ],
        3 => [
            'score' => [41,60],
            'type' => '稳健型'
        ],
        4 => [
            'score' => [61,80],
            'type' => '积极型'
        ],
        5 => [
            'score' => [80,99999],
            'type' => '激进型'
        ],
    ],

    //证件类型
    'id_type' => [
        '1' => '身份证',
        '2' => '居住证',
        '3' => '签证',
        '4' => '护照',
        '5' => '户口本',
        '6' => '军人证',
        '7' => '团员证',
        '8' => '党员证',
        '9' => '港澳通行证'
    ],

    //合同购买类型
    'buy_type' => [
        '1' => '新购',
        '2' => '续费',
        '3' => '升级',
    ],

    //协议签订类型
    'protocol_catid' => [
        1 => '新购',
        2 => '升级',
        3 => '续费'
    ],

    //订单类型
    'order_types' => [
        1 => '小单',
        2 => '大单',
    ],

    //百瑞赢数据
    'dbbry' => [
        'type'     => 'mysql',   // 数据库类型
        'hostname' => '127.0.0.1',  //服务器地址
        'database' => 'tbry',  // 数据库名
        'username' => 'root',  // 数据库用户名
        'password' => 'L6f4GjhLaLKA23PL',  // 数据库密码
        'params'   => [],   // 数据库连接参数
        'charset'  => 'utf8',  // 数据库编码默认采用utf8
        'prefix'   => 'cms_',   // 数据库表前缀
    ],

    //百瑞赢数据直连
    'dbbry1' => [
        'type'     => 'mysql',   // 数据库类型
        'hostname' => '192.168.1.109',  //服务器地址
        'database' => 'brycrm',  // 数据库名
        'username' => 'root',  // 数据库用户名
        'password' => 'brycrm**++',  // 数据库密码
        'params'   => [],   // 数据库连接参数
        'charset'  => 'utf8',  // 数据库编码默认采用utf8
        'prefix'   => 'cms_',   // 数据库表前缀
    ],

    //据说牛中间件token
    'token' => 'jushuoniu@1919',

    //直播上传图片请求地址
    //'live_url' => 'http://zb.yuntougu888.com/admin/upload/live_upimage.html',//线上
    'live_url' => 'http://121.199.31.185:8089/admin/upload/live_upimage.html',//测试
    //图片显示链接地址
    //'img_url' => 'http://zb.yuntougu888.com',//线上
    'img_url' => 'http://121.199.31.185:8089',//测试
    //直播前端socket地址
    'live_socket'=>'http://121.199.31.185:8999',//测试
    //'live_socket'=>'http://zb.yuntougu888.com:8999',//线上
    //直播前端redis地址
    'live_redis' => '127.0.0.1',//测试
    //'live_redis' => '47.103.60.81',//线上
    //ws通知地址
    "notice_address"=>"192.168.0.181:9000",
];

