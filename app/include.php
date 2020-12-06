<?php

//
// Defines
//

// ...

//
// Autoload
//

require_once(__DIR__ . '/../phpframework/autoload.php');

//
// Defines
//

define('INCLUDE_VERSION', 7);

define('PROJECT_ROOT_FOLDER', realpath(__DIR__ . '/..'));
define('PROJECT_CONF_FOLDER', PROJECT_ROOT_FOLDER . '/conf');
define('PROJECT_APP_FOLDER', PROJECT_ROOT_FOLDER . '/app');
define('PROJECT_MODEL_FOLDER', PROJECT_ROOT_FOLDER . '/app/model');
define('PROJECT_PAGE_FOLDER', PROJECT_ROOT_FOLDER . '/app/page');
define('PROJECT_TEMPLATE_FOLDER', PROJECT_ROOT_FOLDER . '/app/page');
define('PROJECT_DB_FOLDER', PROJECT_ROOT_FOLDER . '/db');

define('URL_PROJECT', 'https://boosterpax.com');

//
// Script logic
//

appRegisterAutoload();

//
// Global routines
//

function appRegisterAutoload()
{
    static $registered = false;

    if (!$registered)
    {
        spl_autoload_register('appAutoload');
        $registered = true;
    }
}

function appAutoload($class)
{
    $class_lower = mb_strtolower($class);

    $folder_array = array();
    $folder_array[] = PROJECT_APP_FOLDER;
    $folder_array[] = PROJECT_MODEL_FOLDER;

    foreach ($folder_array as $folder)
    {
        if (try_autoload($folder, $class_lower))
        {
            return true;
        }
    }

    return false;
}

/**
 * @return AppModel
 */
function getModel()
{
    static $model = null;

    if ($model === null)
    {
        $model = new AppModel();
    }

    return $model;
}

function render404()
{
    unicode_set_contenttype_utf8('text/html');
    require_once(PROJECT_PAGE_FOLDER . '/404.php');
    exit();
}

/**
 * @param string $error
 */
function render500($error)
{
    unicode_set_contenttype_utf8('text/html');
    php_warning($error);
    require_once(PROJECT_PAGE_FOLDER . '/500.php');
    exit();
}

/**
 * @param string $file - file within the PROJECT_TEMPLATE_FOLDER
 * @param string[] $html_encode
 * @param string[] $jssq_encode
 * @param string[] $raw_encode
 * @return false|string|string[]
 */
function renderTemplate($file, $html_encode = array(), $jssq_encode = array(), $raw_encode = array())
{
    unicode_set_contenttype_utf8('text/html');

    $file = join_path(PROJECT_TEMPLATE_FOLDER, $file, '/');
    $text = @file_get_contents($file);
    if ($text === false) {
        $text = '';
    }

    $html_encode['year'] = date('Y');
    $html_encode['INCLUDE_VERSION'] = INCLUDE_VERSION;

    foreach ($html_encode as $k => $v)
    {
        $text = str_replace('{{ ' . $k . ' }}', html_encode($v), $text);
    }
    foreach ($jssq_encode as $k => $v)
    {
        $text = str_replace('{{ ' . $k . ' }}', jssq_encode($v, false), $text);
    }
    foreach ($raw_encode as $k => $v)
    {
        $text = str_replace('{{ ' . $k . ' }}', ($v), $text);
    }

    return $text;
}

/**
 * @param string $file - file within the PROJECT_TEMPLATE_FOLDER
 * @param mixed[] $var_map
 * @return string
 */
function renderTwigTemplate($file, $var_map = array())
{
    unicode_set_contenttype_utf8('text/html');

    $t = getModel()->getTwigWrapper();
    $t->setRootTemplate($file);

    $t->setVar('year', date('Y'));
    $t->setVar('INCLUDE_VERSION', INCLUDE_VERSION);
    foreach ($var_map as $k => $v)
    {
        $t->setVar($k, $v);
    }

    return $t->render();
}

/**
 * @param string $file
 * @param string $title
 * @param mixed[] $var_map
 * @return string
 */
function renderTwigLayout1($file, $title, $var_map = array())
{
    if (!isset($var_map['show_header']))
    {
        $var_map['show_header'] = true;
    }

    $t = getModel()->getTwigWrapper();
    $t->setVar('title', $title . ' | BoosterPax');
    return renderTwigTemplate($file, $var_map);
}