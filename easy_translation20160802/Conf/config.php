<?php
if (!defined('THINK_PATH')) exit();
return array(
	'FILE_PATH'=>'D:\\web\\www\\product\\easy_translation\\share\\',	//文件上传路径
	//翻译服务器地址
	'POST_SERVER'=>'127.0.0.1',
	'POST_PORT'=>'24000',

	//术语上传文件地址
	'TERM_ADDR'=>'http://10.28.0.169/patent_online/',
    
	
	//'配置�?    => '配置�?
    'DB_TYPE'    => 'mysql',	    //使用的数据库类型
    'DB_HOST'    => '127.0.0.1',
    'DB_NAME'    => 'easy_trans',	    //数据库名 ts_patent
    'DB_USER'    => 'root',	    //访问数据库账�?    'DB_PWD'     => '123456',       //访问数据库密�?    'DB_PORT'    => '3306',
    'DB_PWD'     => '123456',       //访问数据库密码
	'DB_PORT'    => '3306' ,         //端口
    'DB_PREFIX'  => '',	    //表前缀

    
	'TIME_OUT'=>120,	//socket超时
	'READ_BUF_SIZE'=>8192,	//socket 读取块大小
//翻译服务器
	'PPG_SERVER'=>'127.0.0.1',	//翻译服务器地址
	'PPG_PORT'=>'24000',	//翻译服务器端口


    'URL_PATHINFO_DEPR'    => '/',		//PATHINFO URL 模式下，各参数之间的分割符号
'DEFAULT_THEME'        => 'default',	//默认模板主题
'URL_HTML_SUFFIX'      => '.html',  	//URL伪静态后缀设置
'DEFAULT_CHARSET'      => 'utf8',     // 默认输出编码
'DEFAULT_TIMEZONE'     => 'PRC',	// 默认时区
'DEFAULT_AJAX_RETURN'  => 'JSON',      // 默认AJAX 数据返回格式,可选JSON XML ...
//'APP_GROUP_LIST'       => 'Home,Admin',	//项目分组
//'DEFAULT_GROUP'        => 'Home',	//默认分组
 'TEMPLATE_CHARSET' =>'utf8',        // 模板文件编码
 'OUTPUT_CHARSET' =>'utf8',        // 默认输出编码
/* Cookie设置 */
'COOKIE_EXPIRE'        => 360000,    // Coodie有效�?'COOKIE_DOMAIN'        => '',      // Cookie有效域名
'COOKIE_PATH'          => '/',     // Cookie路径
'COOKIE_PREFIX'        => 'jianyi_',      // Cookie前缀 避免冲突

/* 静态缓存设�?*/
'HTML_CACHE_ON'        => true,   	//默认关闭静态缓�?'HTML_CACHE_TIME'      => 60,      	//静态缓存有效期
'HTML_READ_TYPE'       => 0,       	//静态缓存读取方�?0 readfile 1 redirect
'HTML_FILE_SUFFIX'     => '.shtml',	//默认静态文件后缀

/* 错误设置 */
'ERROR_MESSAGE'        => '您浏览的页面暂时发生了错误！请稍后再试～',//错误显示信息,非调试模式有�?'ERROR_PAGE'           => '',		// 错误定向页面
);
?>