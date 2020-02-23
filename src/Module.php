<?php
namespace Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // don't access directly
};

$__THEME_CONFIG__ = (object) [];

trait ModuleTrait {

    static function __init__ ($opts)
    {
        self::extend_opts($opts);
        self::init();
    }

    static function config ()
    {
        global $__THEME_CONFIG__;
        return $__THEME_CONFIG__;
    }

    static function extend_opts ($opts = null)
    {
        if (empty($opts)) return;
        $opts_arr = (array) $opts;
        $opts_default_arr = (array) self::$opts;
        $merged_opts = array_merge($opts_default_arr, $opts_arr);
        self::$opts = (object) $merged_opts;
    }

    static function call_opt_func ($opt, $props = null)
    {
        if (empty($props)) {
            $props = (object) [];
        }
        $opts = self::$opts;
        if (is_string($opt) && substr($opt, 0, 5) === 'func:') {
            $func_name = str_replace('func:', '', $opt);
            return call_user_func_array($func_name, [$props]);
        }
        return $opts();
    }

    static function path_to_namespace ($path)
    {
        return str_replace('/', '\\', $path);
    }

    static function namespace_to_path ($namespace)
    {
        return str_replace('\\', '/', $namespace);
    }

    static function make_obj ($arr)
    {
        if (!is_object($arr)) {
            $arr = (object) $arr;
        }
        return $arr;
    }

}

class Module {
    use ModuleTrait;
}

function init ($config)
{
    if (!is_object($config)) { $config = (object) $config; }
    global $__THEME_CONFIG__;

    $__THEME_CONFIG__ = $config;

    foreach ($__THEME_CONFIG__->modules as $module_name => $module_cfg) {
        if (empty($module_cfg->disabled)) {
            $namespace = Module::path_to_namespace($module_name);
            $opts = (!empty($__THEME_CONFIG__->modules->$module_name)) ?
                $__THEME_CONFIG__->modules->$module_name :
                null;
            call_user_func_array('\\' . $namespace . '\Module::__init__', [$opts]);
        }
    }
}

function load_json ($path = '/config/theme.config.json') {
    return json_decode(file_get_contents(get_template_directory_uri() . $path));
}