<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * Think公共函数库
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id$
 +------------------------------------------------------------------------------
 */

// 设置和获取统计数据
function N($key,$step=0){
    static $_num = array();
    if(!isset($_num[$key])) {
        $_num[$key]  =  0;
    }
    if(empty($step))
        return $_num[$key];
    else
        $_num[$key]  =  $_num[$key]+(int)$step;
}


// URL组装 支持不同模式和路由
function U($url,$params=array(),$redirect=false,$suffix=true) {
    if(0===strpos($url,'/'))
        $url   =  substr($url,1);
    if(!strpos($url,'://')) // 没有指定项目名 使用当前项目名
        $url   =  APP_NAME.'://'.$url;
    if(stripos($url,'@?')) { // 给路由传递参数
        $url   =  str_replace('@?','@think?',$url);
    }elseif(stripos($url,'@')) { // 没有参数的路由
        $url   =  $url.MODULE_NAME;
    }
    // 分析URL地址
    $array   =  parse_url($url);
    $app      =  isset($array['scheme'])?   $array['scheme']  :APP_NAME;
    $route    =  isset($array['user'])?$array['user']:'';
    if(defined('GROUP_NAME') && strcasecmp(GROUP_NAME,C('DEFAULT_GROUP')))
        $group=  GROUP_NAME;
    if(isset($array['path'])) {
        $action  =  substr($array['path'],1);
        if(!isset($array['host'])) {
            // 没有指定模块名
            $module = MODULE_NAME;
        }else{// 指定模块
            if(strpos($array['host'],'-')) {
                list($group,$module) = explode('-',$array['host']);
            }else{
                $module = $array['host'];
            }
        }
    }else{ // 只指定操作
        $module = MODULE_NAME;
        $action   =  $array['host'];
    }
    if(isset($array['query'])) {
        parse_str($array['query'],$query);
        $params = array_merge($query,$params);
    }

    if(C('URL_MODEL')>0) {
        $depr = C('URL_PATHINFO_MODEL')==2?C('URL_PATHINFO_DEPR'):'/';
        $str    =   $depr;
        foreach ($params as $var=>$val)
            $str .= $var.$depr.$val.$depr;
        $str = substr($str,0,-1);
        $group   = isset($group)?$group.$depr:'';
        if(!empty($route)) {
            $url    =   str_replace(APP_NAME,$app,__APP__).'/'.$group.$route.$str;
        }else{
            $url    =   str_replace(APP_NAME,$app,__APP__).'/'.$group.$module.$depr.$action.$str;
        }
        if($suffix && C('URL_HTML_SUFFIX'))
            $url .= C('URL_HTML_SUFFIX');
    }else{
        $params =   http_build_query($params);
        $params = !empty($params) ? '&'.$params : '';
        if(isset($group)) {
            $url    =   str_replace(APP_NAME,$app,__APP__).'?'.C('VAR_GROUP').'='.$group.'&'.C('VAR_MODULE').'='.$module.'&'.C('VAR_ACTION').'='.$action.$params;
        }else{
            $url    =   str_replace(APP_NAME,$app,__APP__).'?'.C('VAR_MODULE').'='.$module.'&'.C('VAR_ACTION').'='.$action.$params;
        }
    }
    if($redirect)
        redirect($url);
    else
        return $url;
}

/**
 +----------------------------------------------------------
 * 字符串命名风格转换
 * type
 * =0 将Java风格转换为C的风格
 * =1 将C风格转换为Java的风格
 +----------------------------------------------------------
 * @access protected
 +----------------------------------------------------------
 * @param string $name 字符串
 * @param integer $type 转换类型
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function parse_name($name,$type=0) {
    if($type) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    }else{
        $name = preg_replace("/[A-Z]/", "_\\0", $name);
        return strtolower(trim($name, "_"));
    }
}

// 错误输出
function halt($error) {
    if(IS_CLI)   exit ($error);
    $e = array();
    if(C('APP_DEBUG')){
        //调试模式下输出错误信息
        if(!is_array($error)) {
            $trace = debug_backtrace();
            $e['message'] = $error;
            $e['file'] = $trace[0]['file'];
            $e['class'] = $trace[0]['class'];
            $e['function'] = $trace[0]['function'];
            $e['line'] = $trace[0]['line'];
            $traceInfo='';
            $time = date("y-m-d H:i:m");
            foreach($trace as $t)
            {
                $traceInfo .= '['.$time.'] '.$t['file'].' ('.$t['line'].') ';
                $traceInfo .= $t['class'].$t['type'].$t['function'].'(';
                $traceInfo .= implode(', ', $t['args']);
                $traceInfo .=")<br/>";
            }
            $e['trace']  = $traceInfo;
        }else {
            $e = $error;
        }
        // 包含异常页面模板
        include C('TMPL_EXCEPTION_FILE');
    }
    else
    {
        //否则定向到错误页面
        $error_page =   C('ERROR_PAGE');
        if(!empty($error_page)){
            redirect($error_page);
        }else {
            if(C('SHOW_ERROR_MSG'))
                $e['message'] =  is_array($error)?$error['message']:$error;
            else
                $e['message'] = C('ERROR_MESSAGE');
            // 包含异常页面模板
            include C('TMPL_EXCEPTION_FILE');
        }
    }
    exit;
}

// URL重定向
function redirect($url,$time=0,$msg='')
{
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if(empty($msg))
        $msg    =   "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if(0===$time) {
            header("Location: ".$url);
        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str   .=   $msg;
        exit($str);
    }
}

// 自定义异常处理
function throw_exception($msg,$type='ThinkException',$code=0)
{
    if(IS_CLI)   exit($msg);
    if(class_exists($type,false))
        throw new $type($msg,$code,true);
    else
        halt($msg);        // 异常类型不存在则输出错误信息字串
}

// 区间调试开始
function debug_start($label='')
{
    $GLOBALS[$label]['_beginTime'] = microtime(TRUE);
    if ( MEMORY_LIMIT_ON )  $GLOBALS[$label]['_beginMem'] = memory_get_usage();
}

// 区间调试结束，显示指定标记到当前位置的调试
function debug_end($label='')
{
    $GLOBALS[$label]['_endTime'] = microtime(TRUE);
    echo '<div style="text-align:center;width:100%">Process '.$label.': Times '.number_format($GLOBALS[$label]['_endTime']-$GLOBALS[$label]['_beginTime'],6).'s ';
    if ( MEMORY_LIMIT_ON )  {
        $GLOBALS[$label]['_endMem'] = memory_get_usage();
        echo ' Memories '.number_format(($GLOBALS[$label]['_endMem']-$GLOBALS[$label]['_beginMem'])/1024).' k';
    }
    echo '</div>';
}

// 浏览器友好的变量输出
function dump($var, $echo=true,$label=null, $strict=true)
{
    $label = ($label===null) ? '' : rtrim($label) . ' ';
    if(!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = "<pre>".$label.htmlspecialchars($output,ENT_QUOTES)."</pre>";
        } else {
            $output = $label . print_r($var, true);
        }
    }else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if(!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre>'. $label. htmlspecialchars($output, ENT_QUOTES). '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

// 取得对象实例 支持调用类的静态方法
function get_instance_of($name,$method='',$args=array())
{
    static $_instance = array();
    $identify   =   empty($args)?$name.$method:$name.$method.to_guid_string($args);
    if (!isset($_instance[$identify])) {
        if(class_exists($name)){
            $o = new $name();
            if(method_exists($o,$method)){
                if(!empty($args)) {
                    $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                }else {
                    $_instance[$identify] = $o->$method();
                }
            }
            else
                $_instance[$identify] = $o;
        }
        else
            halt(L('_CLASS_NOT_EXIST_').':'.$name);
    }
    return $_instance[$identify];
}

/**
 +----------------------------------------------------------
 * 系统自动加载ThinkPHP基类库和当前项目的model和Action对象
 * 并且支持配置自动加载路径
 +----------------------------------------------------------
 * @param string $name 对象类名
 +----------------------------------------------------------
 * @return void
 +----------------------------------------------------------
 */
function __autoload($name)
{
    // 检查是否存在别名定义
    if(alias_import($name)) return ;
    // 自动加载当前项目的Actioon类和Model类
    if(substr($name,-5)=="Model") {
        require_cache(LIB_PATH.'Model/'.$name.'.class.php');
    }elseif(substr($name,-6)=="Action"){
        require_cache(LIB_PATH.'Action/'.$name.'.class.php');
    }else {
        // 根据自动加载路径设置进行尝试搜索
        if(C('APP_AUTOLOAD_PATH')) {
            $paths  =   explode(',',C('APP_AUTOLOAD_PATH'));
            foreach ($paths as $path){
                if(import($path.$name)) {
                    // 如果加载类成功则返回
                    return ;
                }
            }
        }
    }
    return ;
}

// 优化的require_once
function require_cache($filename)
{
    static $_importFiles = array();
    $filename   =  realpath($filename);
    if (!isset($_importFiles[$filename])) {
        if(file_exists_case($filename)){
            require $filename;
            $_importFiles[$filename] = true;
        }
        else
        {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

// 区分大小写的文件存在判断
function file_exists_case($filename) {
    if(is_file($filename)) {
        if(IS_WIN && C('APP_FILE_CASE')) {
            if(basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

/**
 +----------------------------------------------------------
 * 导入所需的类库 同java的Import
 * 本函数有缓存功能
 +----------------------------------------------------------
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 +----------------------------------------------------------
 * @return boolen
 +----------------------------------------------------------
 */
function import($class,$baseUrl = '',$ext='.class.php')
{
    static $_file = array();
    static $_class = array();
    $class    =   str_replace(array('.','#'), array('/','.'), $class);
    if('' === $baseUrl && false === strpos($class,'/')) {
        // 检查别名导入
        return alias_import($class);
    }    //echo('<br>'.$class.$baseUrl);
    if(isset($_file[$class.$baseUrl]))
        return true;
    else
        $_file[$class.$baseUrl] = true;
    $class_strut = explode("/",$class);
    if(empty($baseUrl)) {
        if('@'==$class_strut[0] || APP_NAME == $class_strut[0] ) {
            //加载当前项目应用类库
            $baseUrl   =  dirname(LIB_PATH);
            $class =  str_replace(array(APP_NAME.'/','@/'),LIB_DIR.'/',$class);
        }elseif(in_array(strtolower($class_strut[0]),array('think','org','com'))) {
            //加载ThinkPHP基类库或者公共类库
            // think 官方基类库 org 第三方公共类库 com 企业公共类库
            $baseUrl =  THINK_PATH.'/Lib/';
        }else {
            // 加载其他项目应用类库
            $class    =   substr_replace($class, '', 0,strlen($class_strut[0])+1);
            $baseUrl =  APP_PATH.'/../'.$class_strut[0].'/'.LIB_DIR.'/';
        }
    }
    if(substr($baseUrl, -1) != "/")    $baseUrl .= "/";
    $classfile = $baseUrl . $class . $ext;
    if($ext == '.class.php' && is_file($classfile)) {
        // 冲突检测
        $class = basename($classfile,$ext);
        if(isset($_class[$class]))
            throw_exception(L('_CLASS_CONFLICT_').':'.$_class[$class].' '.$classfile);
        $_class[$class] = $classfile;
    }
    //导入目录下的指定类库文件
    return require_cache($classfile);
}

/**
 +----------------------------------------------------------
 * 基于命名空间方式导入函数库
 * load('@.Util.Array')
 +----------------------------------------------------------
 * @param string $name 函数库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 +----------------------------------------------------------
 * @return void
 +----------------------------------------------------------
 */
function load($name,$baseUrl='',$ext='.php') {
    $name    =   str_replace(array('.','#'), array('/','.'), $name);
    if(empty($baseUrl)) {
        if(0 === strpos($name,'@/')) {
            //加载当前项目函数库
            $baseUrl   =  APP_PATH.'/Common/';
            $name =  substr($name,2);
        }else{
            //加载ThinkPHP 系统函数库
            $baseUrl =  THINK_PATH.'/Common/';
        }
    }
    if(substr($baseUrl, -1) != "/")    $baseUrl .= "/";
    include $baseUrl . $name . $ext;
}

// 快速导入第三方框架类库
// 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
// 并且默认都是以.php后缀导入
function vendor($class,$baseUrl = '',$ext='.php')
{
    if(empty($baseUrl))  $baseUrl    =   VENDOR_PATH;
    return import($class,$baseUrl,$ext);
}

// 快速定义和导入别名
function alias_import($alias,$classfile='') {
    static $_alias   =  array();
    if('' !== $classfile) {
        // 定义别名导入
        $_alias[$alias]  = $classfile;
        return ;
    }
    if(is_string($alias)) {
        if(isset($_alias[$alias]))
            return require_cache($_alias[$alias]);
    }elseif(is_array($alias)){
        foreach ($alias as $key=>$val)
            $_alias[$key]  =  $val;
        return ;
    }
    return false;
}

/**
 +----------------------------------------------------------
 * D函数用于实例化Model
 +----------------------------------------------------------
 * @param string name Model名称
 * @param string app Model所在项目
 +----------------------------------------------------------
 * @return Model
 +----------------------------------------------------------
 */
function D($name='',$app='')
{
    static $_model = array();
    if(empty($name)) return new Model;
    if(empty($app))   $app =  C('DEFAULT_APP');
    if(isset($_model[$app.$name]))
        return $_model[$app.$name];
    $OriClassName = $name;
    if(strpos($name,'.')) {
        $array   =  explode('.',$name);
        $name = array_pop($array);
        $className =  $name.'Model';
        import($app.'.Model.'.implode('.',$array).'.'.$className);
    }else{
        $className =  $name.'Model';
        import($app.'.Model.'.$className);
    }
    if(class_exists($className)) {
        $model = new $className();
    }else {
        $model  = new Model($name);
    }
    $_model[$app.$OriClassName] =  $model;
    return $model;
}

/**
 +----------------------------------------------------------
 * M函数用于实例化一个没有模型文件的Model
 +----------------------------------------------------------
 * @param string name Model名称
 +----------------------------------------------------------
 * @return Model
 +----------------------------------------------------------
 */
function M($name='',$class='Model') {
    static $_model = array();
    if(!isset($_model[$name.'_'.$class]))
        $_model[$name.'_'.$class]   = new $class($name);
    return $_model[$name.'_'.$class];
}

/**
 +----------------------------------------------------------
 * A函数用于实例化Action
 +----------------------------------------------------------
 * @param string name Action名称
 * @param string app Model所在项目
 +----------------------------------------------------------
 * @return Action
 +----------------------------------------------------------
 */
function A($name,$app='@')
{
    static $_action = array();
    if(isset($_action[$app.$name]))
        return $_action[$app.$name];
    $OriClassName = $name;
    if(strpos($name,'.')) {
        $array   =  explode('.',$name);
        $name = array_pop($array);
        $className =  $name.'Action';
        import($app.'.Action.'.implode('.',$array).'.'.$className);
    }else{
        $className =  $name.'Action';
        import($app.'.Action.'.$className);
    }
    if(class_exists($className)) {
        $action = new $className();
        $_action[$app.$OriClassName] = $action;
        return $action;
    }else {
        return false;
    }
}

// 远程调用模块的操作方法
function R($module,$action,$app='@') {
    $class = A($module,$app);
    if($class)
        return call_user_func(array(&$class,$action));
    else
        return false;
}

// 获取和设置语言定义(不区分大小写)
function L($name=null,$value=null) {
    static $_lang = array();
    // 空参数返回所有定义
    if(empty($name)) return $_lang;
    // 判断语言获取(或设置)
    // 若不存在,直接返回全大写$name
    if (is_string($name) )
    {
        $name = strtoupper($name);
        if (is_null($value))
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        $_lang[$name] = $value;// 语言定义
        return;
    }
    // 批量定义
    if (is_array($name))
        $_lang = array_merge($_lang,array_change_key_case($name,CASE_UPPER));
    return;
}

// 获取配置值
function C($name=null,$value=null)
{
    static $_config = array();
    // 无参数时获取所有
    if(empty($name)) return $_config;
    // 优先执行设置获取或赋值
    if (is_string($name))
    {
        if (!strpos($name,'.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name])? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.',$name);
        $name[0]   = strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if(is_array($name))
        return $_config = array_merge($_config,array_change_key_case($name));
    return null;// 避免非法参数
}

// 处理标签
function tag($name,$params=array()) {
    $tags   =  C('TAGS_FILTER_LIST.'.$name);
    if(!empty($tags)) {
        foreach ($tags   as $key=>$call){
            $result   =  B($call,$params);
        }
    }
}

// 过滤器方法
function filter($name,&$content) {
    $class = $name.'Filter';
    require_cache(LIB_PATH.'Filter/'.$class.'.class.php');
    $filter   =  new $class();
    $content = $filter->run($content);
}

// 执行行为
function B($name,$params=array()) {
    $class = $name.'Behavior';
    require_cache(LIB_PATH.'Behavior/'.$class.'.class.php');
    $behavior   =  new $class();
    return $behavior->run($params);
}

// 渲染输出Widget
function W($name,$data=array(),$return=false) {
    $class = $name.'Widget';
    require_cache(LIB_PATH.'Widget/'.$class.'.class.php');
    if(!class_exists($class))
        throw_exception(L('_CLASS_NOT_EXIST_').':'.$class);
    $widget  =  Think::instance($class);
    $content = $widget->render($data);
    if($return)
        return $content;
    else
        echo $content;
}

// 全局缓存设置和读取
function S($name,$value='',$expire='',$type='') {
    static $_cache = array();
    alias_import('Cache');
    //取得缓存对象实例
    $cache  = Cache::getInstance($type);
    if('' !== $value) {
        if(is_null($value)) {
            // 删除缓存
            $result =   $cache->rm($name);
            if($result)   unset($_cache[$type.'_'.$name]);
            return $result;
        }else{
            // 缓存数据
            $cache->set($name,$value,$expire);
            $_cache[$type.'_'.$name]     =   $value;
        }
        return ;
    }
    if(isset($_cache[$type.'_'.$name]))
        return $_cache[$type.'_'.$name];
    // 获取缓存数据
    $value      =  $cache->get($name);
    $_cache[$type.'_'.$name]     =   $value;
    return $value;
}

// 快速文件数据读取和保存 针对简单类型数据 字符串、数组
function F($name,$value='',$path=DATA_PATH) {
    static $_cache = array();
    $filename   =   $path.$name.'.php';
    if('' !== $value) {
        if(is_null($value)) {
            // 删除缓存
            return unlink($filename);
        }else{
            // 缓存数据
            $dir   =  dirname($filename);
            // 目录不存在则创建
            if(!is_dir($dir))  mkdir($dir);
            return file_put_contents($filename,"<?php\nreturn ".var_export($value,true).";\n?>");
        }
    }
    if(isset($_cache[$name])) return $_cache[$name];
    // 获取缓存数据
    if(is_file($filename)) {
        $value   =  include $filename;
        $_cache[$name]   =   $value;
    }else{
        $value  =   false;
    }
    return $value;
}

// 根据PHP各种类型变量生成唯一标识号
function to_guid_string($mix)
{
    if(is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    }elseif(is_resource($mix)){
        $mix = get_resource_type($mix).strval($mix);
    }else{
        $mix = serialize($mix);
    }
    return md5($mix);
}

//[RUNTIME]
// 编译文件
// 去除代码中的空白和注释
function strip_whitespace($content) {
    $stripStr = '';
    //分析php源码
    $tokens =   token_get_all ($content);
    $last_space = false;
    for ($i = 0, $j = count ($tokens); $i < $j; $i++)
    {
        if (is_string ($tokens[$i]))
        {
            $last_space = false;
            $stripStr .= $tokens[$i];
        }
        else
        {
            switch ($tokens[$i][0])
            {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space)
                    {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

function compile($filename,$runtime=false) {
    $content = file_get_contents($filename);
    if(true === $runtime)
        // 替换预编译指令
        $content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s','',$content);
    $content = substr(trim($content),5);
    if('?>' == substr($content,-2))
        $content = substr($content,0,-2);
    return $content;
}

// 根据数组生成常量定义
function array_define($array) {
    $content = '';
    foreach($array as $key=>$val) {
        $key =  strtoupper($key);
        if(in_array($key,array('THINK_PATH','APP_NAME','APP_PATH','APP_CACHE_NAME','RUNTIME_PATH','RUNTIME_ALLINONE','THINK_MODE')))
            $content .= 'if(!defined(\''.$key.'\')) ';
        if(is_int($val) || is_float($val)) {
            $content .= "define('".$key."',".$val.");";
        }elseif(is_bool($val)) {
            $val = ($val)?'true':'false';
            $content .= "define('".$key."',".$val.");";
        }elseif(is_string($val)) {
            $content .= "define('".$key."','".addslashes($val)."');";
        }
    }
    return $content;
}
//[/RUNTIME]

// 循环创建目录
function mk_dir($dir, $mode = 0777)
{
  if (is_dir($dir) || @mkdir($dir,$mode)) return true;
  if (!mk_dir(dirname($dir),$mode)) return false;
  return @mkdir($dir,$mode);
}

// 自动转换字符集 支持数组转换
function auto_charset($fContents,$from='gbk',$to='utf-8'){
    $from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
    $to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
    if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if(is_string($fContents) ) {
        if(function_exists('mb_convert_encoding')){
            return mb_convert_encoding ($fContents, $to, $from);
        }elseif(function_exists('iconv')){
            return iconv($from,$to,$fContents);
        }else{
            return $fContents;
        }
    }
    elseif(is_array($fContents)){
        foreach ( $fContents as $key => $val ) {
            $_key =     auto_charset($key,$from,$to);
            $fContents[$_key] = auto_charset($val,$from,$to);
            if($key != $_key )
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else{
        return $fContents;
    }
}

// xml编码
function xml_encode($data,$encoding='utf-8',$root="think") {
    $xml = '<?xml version="1.0" encoding="'.$encoding.'"?>';
    $xml.= '<'.$root.'>';
    $xml.= data_to_xml($data);
    $xml.= '</'.$root.'>';
    return $xml;
}

function data_to_xml($data) {
    if(is_object($data)) {
        $data = get_object_vars($data);
    }
    $xml = '';
    foreach($data as $key=>$val) {
        is_numeric($key) && $key="item id=\"$key\"";
        $xml.="<$key>";
        $xml.=(is_array($val)||is_object($val))?data_to_xml($val):$val;
        list($key,)=explode(' ',$key);
        $xml.="</$key>";
    }
    return $xml;
}

/**
 +----------------------------------------------------------
 * Cookie 设置、获取、清除
 +----------------------------------------------------------
 * 1 获取cookie: cookie('name')
 * 2 清空当前设置前缀的所有cookie: cookie(null)
 * 3 删除指定前缀所有cookie: cookie(null,'think_') | 注：前缀将不区分大小写
 * 4 设置cookie: cookie('name','value') | 指定保存时间: cookie('name','value',3600)
 * 5 删除cookie: cookie('name',null)
 +----------------------------------------------------------
 * $option 可用设置prefix,expire,path,domain
 * 支持数组形式对参数设置:cookie('name','value',array('expire'=>1,'prefix'=>'think_'))
 * 支持query形式字符串对参数设置:cookie('name','value','prefix=tp_&expire=10000')
 */
function cookie($name,$value='',$option=null)
{
    // 默认设置
    $config = array(
        'prefix' => C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire' => C('COOKIE_EXPIRE'), // cookie 保存时间
        'path'   => C('COOKIE_PATH'),   // cookie 保存路径
        'domain' => C('COOKIE_DOMAIN'), // cookie 有效域名
    );
    // 参数设置(会覆盖黙认设置)
    if (!empty($option)) {
        if (is_numeric($option))
            $option = array('expire'=>$option);
        elseif( is_string($option) )
            parse_str($option,$option);
        $config = array_merge($config,array_change_key_case($option));
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
       if (empty($_COOKIE)) return;
       // 要删除的cookie前缀，不指定则删除config设置的指定前缀
       $prefix = empty($value)? $config['prefix'] : $value;
       if (!empty($prefix))// 如果前缀为空字符串将不作处理直接返回
       {
           foreach($_COOKIE as $key=>$val) {
               if (0 === stripos($key,$prefix)){
                    setcookie($key,'',time()-3600,$config['path'],$config['domain']);
                    unset($_COOKIE[$key]);
               }
           }
       }
       return;
    }
    $name = $config['prefix'].$name;
    if (''===$value){
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;// 获取指定Cookie
    }else {
        if (is_null($value)) {
            setcookie($name,'',time()-3600,$config['path'],$config['domain']);
            unset($_COOKIE[$name]);// 删除指定cookie
        }else {
            // 设置cookie
            $expire = !empty($config['expire'])? time()+ intval($config['expire']):0;
            setcookie($name,$value,$expire,$config['path'],$config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}
	//输出安全的html
	function h($text, $tags = null){
	$text	=	trim($text);
	//完全过滤注释
	$text	=	preg_replace('/<!--?.*-->/','',$text);
	//完全过滤动态代码
	$text	=	preg_replace('/<\?|\?'.'>/','',$text);
	//完全过滤js
	$text	=	preg_replace('/<script?.*\/script>/','',$text);

	$text	=	str_replace('[','&#091;',$text);
	$text	=	str_replace(']','&#093;',$text);
	$text	=	str_replace('|','&#124;',$text);
	//过滤换行符
	$text	=	preg_replace('/\r?\n/','',$text);
	//br
	$text	=	preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
	$text	=	preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
	//过滤危险的属性，如：过滤on事件lang js
	while(preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1],$text);
	}
	while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].$mat[3],$text);
	}
	if(empty($tags)) {
		$tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
	}
	//允许的HTML标签
	$text	=	preg_replace('/<('.$tags.')( [^><\[\]]*)>/i','[\1\2]',$text);
	//过滤多余html
	$text	=	preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i','',$text);
	//过滤合法的html标签
	while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
	}
	//转换引号
	while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
		$text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
	}
	//过滤错误的单个引号
	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
		$text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
	}
	//转换其它所有不合法的 < >
	$text	=	str_replace('<','&lt;',$text);
	$text	=	str_replace('>','&gt;',$text);
	$text	=	str_replace('"','&quot;',$text);
	 //反转换
	$text	=	str_replace('[','<',$text);
	$text	=	str_replace(']','>',$text);
	$text	=	str_replace('|','"',$text);
	//过滤多余空格
	$text	=	str_replace('  ',' ',$text);
	return $text;
}
	function get_guid()
	{
		$guid="";
		if (function_exists('com_create_guid'))
	   	{
			$guid=substr(com_create_guid(),1,-1);
		}
		//linux
		else
		{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
	        $charid = strtoupper(md5(uniqid(rand(), true)));
	        $hyphen = chr(45);// "-"
	        $uuid = chr(123)// "{"
	                .substr($charid, 0, 8).$hyphen
	                .substr($charid, 8, 4).$hyphen
	                .substr($charid,12, 4).$hyphen
	                .substr($charid,16, 4).$hyphen
	                .substr($charid,20,12)
	                .chr(125);// "}"
	        $guid=substr($uuid,1,-1);
	       // return $uuid;
		}
		return $guid;
	}
	function interact_server($PPG_SERVER,$PPG_PORT,$sockstr)
	{

        $TIME_OUT=C("TIME_OUT");

		$fp = @fsockopen($PPG_SERVER,$PPG_PORT,$errno,$errstr,$TIME_OUT);

		if (!$fp)
		{
			//return $PPG_SERVER.$PPG_PORT;
			return "ERROR404";
		}
		$content="";
		fwrite($fp,$sockstr);
		stream_set_timeout($fp, $TIME_OUT);
		do
		{
		   $data = fread($fp, C("READ_BUF_SIZE"));
		   $content.=$data;
		   if (strlen($data) == 0)
		   {
		   		break;
		   }

		} while (true);
  		fclose($fp);
  		if($content!="")
		return $content;
		else
		return "服务器没有启动";
	}
	//解析返回报文
	function parse_code($str)
	{
		$sp=strpos($str,"<ResCode>");
		$ep=strpos($str,"</ResCode>");
		$code=substr($str,$sp+9,$ep-$sp-9);
		return $code;
	}
	//即时翻译
	function get_trans($username,$content,$src,$tgt,$type,$transtyle)
	{

		$sstr.="<Msg>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";
		$sstr.="<TransSrc>".$content."</TransSrc>";
		$sstr.="<UsrID>".$username."</UsrID>";
		$sstr.="<style>".$transtyle."</style>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: RealTimeTrans\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//文件翻译
	function get_file_trans($username,$src,$tgt,$type,$guid,$transtyle)
	{
		//$bird="bird";
		$sstr.="<Msg>";
		$sstr.="<UsrID>".$username."</UsrID>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<style>".$transtyle."</style>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";
		$sstr.="<TextList number='1'>";
		$sstr.="<Text seq='0'>";
		$sstr.="<TextID>".$guid."</TextID>";
		$sstr.="</Text>";
		$sstr.="</TextList>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: OffLineTrans\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
	//	return $str;
     	return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//挂起，恢复，取消翻译
	function set_job_operate($username,$guid,$op,$type,$src,$tgt)
	{
		$sstr.="<Msg>";
		$sstr.="<ControlType>".$op."</ControlType>";
		$sstr.="<UsrID>".$username."</UsrID>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";		
		$sstr.="<TextID>".$guid."</TextID>";		
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: OltControl\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//下载翻译文件
	function get_file_download($guid)
	{
		$sstr.="<Msg>";
		$sstr.="<TextID>".$guid."</TextID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: OltDownload\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//下载翻译中间结果文件
	function get_temp_file_download($guid)
	{
		$sstr.="<Msg>";
		$sstr.="<TextID type='detail'>".$guid."</TextID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: OltDownload\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//导入系统词典
	function import_sys_dict($idstr,$toid)
	{
		$sstr.="<Msg>";
		$sstr.="<SrcWordID".$idstr." />";
		$sstr.="<TgtDictID>".$toid."</TgtDictID>";		
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: InsertSysDict\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("SYSDICT_SERVER"), C("SYSDICT_PORT"), $str);
	}
	//导入系统词条（旧版本，已停用）
	function import_dict_socket($src_id,$tgt_id)
	{
		$sstr.="<Msg>";
		$sstr.="<SrcDictID>".$src_id."</SrcDictID>";
		$sstr.="<TgtDictID>".$tgt_id."</TgtDictID>";		
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: InsertSysDict\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("SYSDICT_SERVER"), C("SYSDICT_PORT"), $str);
	}
	//添加词典
	function add_dict_socket($username,$src,$tgt,$type,$dictname,$dictdescription,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<UsrID>".$username."</UsrID>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";
		$sstr.="<DictName>";
		$sstr.=$dictname;
		$sstr.="</DictName>";
		$sstr.="<DictDescription>";
		$sstr.=$dictdescription;
		$sstr.="</DictDescription>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="</Msg>";
		
		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: DictCreate\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//print_r($str);
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//添加后处理词典
	function add_postdict_socket($username,$src,$tgt,$type,$dictname,$dictdescription,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<UsrID>".$username."</UsrID>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";
		$sstr.="<AfterDictName>";
		$sstr.=$dictname;
		$sstr.="</AfterDictName>";
		$sstr.="<AfterDictDescription>";
		$sstr.=$dictdescription;
		$sstr.="</AfterDictDescription>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterDictCreate\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//添加分词词典
	function add_segdict_socket($segword,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<InsertChSegWord>";		
		$sstr.="<Src>".$segword."</Src>";		
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="</InsertChSegWord>";		
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: ChSegWordInsert\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//修改分词词典
	function mod_segdict_socket($tid,$segword,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<ModifyChSegWord>";
		$sstr.="<Id>".$tid."</Id>";
		$sstr.="<Src>".$segword."</Src>";		
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="</ModifyChSegWord>";		
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: ChSegWordModify\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//删除分词词典
	function del_segdict_socket($tid)
	{
		$sstr.="<Msg>";		
		$sstr.="<DeleteChSegWordID>".$tid."</DeleteChSegWordID>";		
		$sstr.="</Msg>";
		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: ChSegWordDelete\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//恢复删除分词词典
	function recover_segdict_socket($tid)
	{
		$sstr.="<Msg>";		
		$sstr.="<RecoveryChSegWordID>".$tid."</RecoveryChSegWordID>";		
		$sstr.="</Msg>";
		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: ChSegWordRecovery\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//上传分词词条
	function upload_segitem_socket($filename)
	{
		$sstr.="<Msg>";		
		$sstr.="<WordFileName>".$filename."</WordFileName>";
		$sstr.="<IsActive>1</IsActive>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: ChSegWordLoad\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//修改词典
	function mod_dict_socket($dictid,$src,$tgt,$type,$dictname,$dictdescription,$isactive,$issystem)
	{
		$sstr.="<Msg>";
		$sstr.="<DictID>".$dictid."</DictID>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";
		$sstr.="<DictName>";
		$sstr.=$dictname;
		$sstr.="</DictName>";
		$sstr.="<DictDescription>";
		$sstr.=$dictdescription;
		$sstr.="</DictDescription>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="<IsSystem>".$issystem."</IsSystem>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: DictModify\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//修改后处理词典
	function mod_postdict_socket($dictid,$src,$tgt,$type,$dictname,$dictdescription,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<AfterDictID>".$dictid."</AfterDictID>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";
		$sstr.="<AfterDictName>";
		$sstr.=$dictname;
		$sstr.="</AfterDictName>";
		$sstr.="<AfterDictDescription>";
		$sstr.=$dictdescription;
		$sstr.="</AfterDictDescription>";
		$sstr.="<IsActive>".$isactive."</IsActive>";		
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterDictModify\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//删除词典
	function del_dict_socket($dictid)
	{
		$sstr.="<Msg>";
		$sstr.="<DictID>".$dictid."</DictID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: DictDelete\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//删除后处理词典
	function del_postdict_socket($dictid)
	{
		$sstr.="<Msg>";
		$sstr.="<AfterDictID>".$dictid."</AfterDictID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterDictDelete\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//恢复被删除词典
	function recover_dict_socket($dictid)
	{
		$sstr.="<Msg>";
		$sstr.="<DictID>".$dictid."</DictID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: DictRecover\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//恢复被删除后处理词典
	function recover_postdict_socket($dictid)
	{
		$sstr.="<Msg>";
		$sstr.="<AfterDictID>".$dictid."</AfterDictID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterDictRecover\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//添加词条
	function add_item_socket($dictid,$src,$tgt,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<DictID>".$dictid."</DictID>";
		$sstr.="<InsertWord><Src>";
		$sstr.=$src."</Src>";
		$sstr.="<Tgt>".$tgt."</Tgt>";
		$sstr.="<IsActive>".$isactive."</IsActive></InsertWord>";

		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: WordInsert\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		//echo "Message:<br>".$str."<br>"."<Msg>"."<br>"; ,$ischecked=1
		
		return interact_server(C("POST_SERVER"), C("POST_PORT"), $str);
	}
	//添加后处理词条
	function add_postitem_socket($dictid,$src,$tgt,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<AfterDictID>".$dictid."</AfterDictID>";
		$sstr.="<AfterInsertWord><Src>";
		$sstr.=$src."</Src>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="<Tgt>".$tgt."</Tgt></AfterInsertWord>";

		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterWordInsert\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//修改词条
	function mod_item_socket($dictid,$wordid,$src,$tgt,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<DictID>".$dictid."</DictID>";
		$sstr.="<ModifyWord><Src>";
		$sstr.=$src."</Src>";
		$sstr.="<Tgt>".$tgt."</Tgt>";
		$sstr.="<WordID>".$wordid."</WordID>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
	//	$sstr.="<IsChecked>".$ischecked."</IsChecked>";
		$sstr.="</ModifyWord>";

		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: WordModify\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;,$ischecked=1
		//echo "Message:<br>".$str."<br>";
		return interact_server(C("POST_SERVER"), C("POST_PORT"), $str);
	}
	//修改后处理词条
	function mod_postitem_socket($dictid,$wordid,$src,$tgt,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<AfterDictID>".$dictid."</AfterDictID>";
		$sstr.="<AfterModifyWord><Src>";
		$sstr.=$src."</Src>";
		$sstr.="<AfterWordID>".$wordid."</AfterWordID>";
		$sstr.="<IsActive>".$isactive."</IsActive>";		
		$sstr.="<Tgt>".$tgt."</Tgt></AfterModifyWord>";

		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterWordModify\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//删除词条
	function del_item_socket($dictid,$itemid)
	{
		$sstr.="<Msg>";
		$sstr.="<DictID>".$dictid."</DictID>";
		$sstr.="<DeleteWordID>".$itemid."</DeleteWordID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: WordDelete\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("POST_SERVER"), C("POST_PORT"), $str);
	}
	//删除后处理词条
	function del_postitem_socket($dictid,$itemid)
	{
		$sstr.="<Msg>";
		$sstr.="<AfterDictID>".$dictid."</AfterDictID>";
		$sstr.="<AfterDeleteWordID>".$itemid."</AfterDeleteWordID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterWordDelete\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//恢复被删除词条
	function recover_item_socket($dictid,$itemid)
	{
		$sstr.="<Msg>";
		$sstr.="<DictID>".$dictid."</DictID>";
		$sstr.="<RecoverWordID>".$itemid."</RecoverWordID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: WordRecover\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//恢复被删除后处理词条
	function recover_postitem_socket($dictid,$itemid)
	{
		$sstr.="<Msg>";
		$sstr.="<AfterDictID>".$dictid."</AfterDictID>";
		$sstr.="<AfterRecoverWordID>".$itemid."</AfterRecoverWordID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterWordRecover\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//上传词条
	function upload_item_socket($dictid,$filename,$isutf8)
	{
		$sstr.="<Msg>";
		$sstr.="<DictID>".$dictid."</DictID>";
		$sstr.="<WordFileName>".$filename."</WordFileName>";
		$sstr.="<IsActive>1</IsActive>";
	    $sstr.="<IsUTF8>".$isutf8."</IsUTF8>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: WordLoad\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//上传后处理词条
	function upload_postitem_socket($dictid,$filename)
	{
		$sstr.="<Msg>";
		$sstr.="<AfterDictID>".$dictid."</AfterDictID>";
		$sstr.="<AfterWordFileName>".$filename."</AfterWordFileName>";	
		$sstr.="<IsActive>1</IsActive>";	
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: AfterWordLoad\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//添加模板库
	function add_template_socket($username,$src,$tgt,$type,$dictname,$dictdescription,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<UsrID>".$username."</UsrID>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";
		$sstr.="<TemplateLibName>";
		$sstr.=$dictname;
		$sstr.="</TemplateLibName>";
		$sstr.="<TemplateLibDescription>";
		$sstr.=$dictdescription;
		$sstr.="</TemplateLibDescription>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateLibCreate\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//修改模板库
	function mod_template_socket($dictid,$src,$tgt,$type,$dictname,$dictdescription,$isactive,$issystem=0)
	{
		$sstr.="<Msg>";
		$sstr.="<TemplateLibID>".$dictid."</TemplateLibID>";
		$sstr.="<Domain>".$type."</Domain>";
		$sstr.="<Language src='".$src."' tgt='".$tgt."'/>";
		$sstr.="<TemplateLibName>";
		$sstr.=$dictname;
		$sstr.="</TemplateLibName>";
		$sstr.="<TemplateLibDescription>";
		$sstr.=$dictdescription;
		$sstr.="</TemplateLibDescription>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="<IsSystem>".$issystem."</IsSystem>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateLibModify\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//删除模板库
	function del_template_socket($dictid)
	{
		$sstr.="<Msg>";
		$sstr.="<TemplateLibID>".$dictid."</TemplateLibID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateLibDelete\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//恢复被删除模版
	function recover_template_socket($dictid)
	{
		$sstr.="<Msg>";
		$sstr.="<TemplateLibID>".$dictid."</TemplateLibID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateLibRecover\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//恢复被删除词条
	function recover_templateitem_socket($dictid,$itemid)
	{
		$sstr.="<Msg>";
		$sstr.="<TemplateLibID>".$dictid."</TemplateLibID>";
		$sstr.="<RecoverTemplateID>".$itemid."</RecoverTemplateID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateRecover\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//导入系统模版
	function import_sys_template($idstr,$toid)
	{
		$sstr.="<Msg>";
		$sstr.="<SrcTemplateID ".$idstr." />";
		$sstr.="<TgtTemplateLibID>".$toid."</TgtTemplateLibID>";		
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: InsertSysTemplateLib\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("SYSDICT_SERVER"), C("SYSDICT_PORT"), $str);
	}
	//添加模板
	function add_templateitem_socket($dictid,$src,$tgt,$isactive)
	{
		$sstr.="<Msg>";
		$sstr.="<TemplateLibID>".$dictid."</TemplateLibID>";
		$sstr.="<InsertTemplate><Src>";
		$sstr.=$src."</Src>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="<Tgt>".$tgt."</Tgt></InsertTemplate>";

		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateInsert\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//修改模版
	function mod_templateitem_socket($dictid,$wordid,$src,$tgt,$isactive,$ischecked=1)
	{
		$sstr.="<Msg>";
		$sstr.="<TemplateLibID>".$dictid."</TemplateLibID>";
		$sstr.="<ModifyTemplate><Src>";
		$sstr.=$src."</Src>";
		$sstr.="<TemplateID>".$wordid."</TemplateID>";
		$sstr.="<IsActive>".$isactive."</IsActive>";
		$sstr.="<IsChecked>".$ischecked."</IsChecked>";
		$sstr.="<Tgt>".$tgt."</Tgt></ModifyTemplate>";

		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateModify\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//删除词条
	function del_templateitem_socket($dictid,$itemid)
	{
		$sstr.="<Msg>";
		$sstr.="<TemplateLibID>".$dictid."</TemplateLibID>";
		$sstr.="<DeleteTemplateID>".$itemid."</DeleteTemplateID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateDelete\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}
	//上传词条
	function upload_templateitem_socket($dictid,$filename)
	{
		$sstr.="<Msg>";
		$sstr.="<TemplateLibID>".$dictid."</TemplateLibID>";
		$sstr.="<TemplateFileName>".$filename."</TemplateFileName>";
		$sstr.="<IsActive>1</IsActive>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: TemplateLoad\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("PPG_SERVER"), C("PPG_PORT"), $str);
	}

	//翻译评价
	function eval_socket($guid)
	{
		$sstr.="<Msg>";
		$sstr.="<EvalGUID>".$guid."</EvalGUID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: Evalulation\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;

		return interact_server(C("EVAL_SERVER"), C("EVAL_PORT"), $str);
	}
	//对齐
	function align_socket($guid)
	{
		$sstr.="<Msg>";
		$sstr.="<CorpusGUID>".$guid."</CorpusGUID>";
		$sstr.="</Msg>";

		$len=strlen($sstr);
		$str="Request: 1.0\r\n";
		$str.="Msg-Type: CorpusAlign\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;

		return interact_server(C("ALIGN_SERVER"), C("ALIGN_PORT"), $str);
	}
	
	function post_socket($usrid,$domain,$src,$tgt,$content)
	{
		$sstr.="{";
		$sstr.="\"usrid\":"."\"$usrid\"";
		$sstr.=",";
		
		$sstr.="\"domain\":"."\"$domain\"";
		$sstr.=",";
		
		$sstr.="\"src\":"."\"$src\"";
		$sstr.=",";
		
		$sstr.="\"tgt\":"."\"$tgt\"";
		$sstr.=",";	
		
		$sstr.="\"content\":"."\"$content\"";		
		$sstr.="}";
		
		$len=strlen($sstr);
		$str="POST /translate.do HTTP/1.1\r\n";
		$str.="Host: ".C("POST_SERVER").":".C("POST_PORT")."\r\n";
		$str.="Connection: keep-alive\r\n";
		$str.="Cache-Control: max-age=0\r\n";
		$str.="User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.77 Safari/535.7\r\n";
		$str.="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$str.="Accept-Encoding: gzip,deflate,sdch\r\n";
		$str.="Accept-Language: zh-CN,zh;q=0.8\r\n";
		$str.="Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3\r\n";
		$str.="Content-Length: ".$len."\r\n";
		$str.="\r\n";
		$str.=$sstr;
		//return $str;
		return interact_server(C("POST_SERVER"), C("POST_PORT"), $str);
	}
		
		
	function get_dictsearch($usrid,$content)
	{
		
		$str="GET /dictsearch.do?usrid=".$usrid."&content=".$content." HTTP/1.1\r\n";
		$str.="Host: ".C("GET_SERVER").":".C("GET_PORT")."\r\n";
		$str.="Connection: keep-alive\r\n";
		$str.="Cache-Control: max-age=0\r\n";
		$str.="User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.77 Safari/535.7\r\n";
		$str.="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$str.="Accept-Encoding: gzip,deflate,sdch\r\n";
		$str.="Accept-Language: zh-CN,zh;q=0.8\r\n";
		$str.="Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3\r\n";
		$str.="\r\n";
		//return $str;
		return interact_server(C("GET_SERVER"), C("GET_PORT"), $str);
	}
	function get_sentsearch($range,$content)
	{
		
		$str="GET /sentsearch.do?range=".$range."&content=".$content." HTTP/1.1\r\n";
		$str.="Host: ".C("GET_SERVER").":".C("GET_PORT")."\r\n";
		$str.="Connection: keep-alive\r\n";
		$str.="Cache-Control: max-age=0\r\n";
		$str.="User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.77 Safari/535.7\r\n";
		$str.="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$str.="Accept-Encoding: gzip,deflate,sdch\r\n";
		$str.="Accept-Language: zh-CN,zh;q=0.8\r\n";
		$str.="Accept-Charset: GBK,utf-8;q=0.7,*;q=0.3\r\n";
		$str.="\r\n";
		//return $str;
		return interact_server(C("GET_SERVER"), C("GET_PORT"), $str);
	}
	function a_array_unique($array)//数组去掉重复的
	{
		$out = array();
			$it=0;
		foreach ($array as $value) {
		if (!in_array($value, $out))
		{
           $out[$it] = $value;
		   $it=$it+1;
		}
		}
			return $out;
	} 

?>