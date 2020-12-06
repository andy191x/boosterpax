<?php

//
// Includes
//

require_once (__DIR__ . '/../app/include.php');

//
// Script logic
//

renderRoutePage();
exit();

//
// Global routines
//

function renderRoutePage()
{
    // Configure PHP
    unicode_configure_php_utf8();

    // Determine route
    $route = '';

    if (has_get_var('route'))
    {
        $route = mb_trim(get_get_string('route'));
    }

    if (strlen($route) == 0)
    {
        $route = client_uri();
        $temp_qpos = mb_strpos($route, '?', 0);
        if ($temp_qpos !== false)
        {
            $route = mb_substr($route, 0, $temp_qpos);
        }
    }

    if (strlen($route) == 0)
    {
        $route = '/';
    }

    // Determine query string
    $query_string = '';

    $temp = isset($_SERVER['REQUEST_URI']) ? mb_trim(utf8_sanitize($_SERVER['REQUEST_URI'])) : '';
    $temp_qpos = mb_strpos($temp, '?', 0);

    if ($temp_qpos !== false)
    {
        if ($temp_qpos != (mb_strlen($temp) - 1))
        {
            $query_string = mb_substr($temp, $temp_qpos);
        }
    }

    // Setup model
    $model = getModel();

    // Setup page data
    $pagedata = $model->getPageData();
    $pagedata->set('method', client_http_method());
    $pagedata->set('client_ip', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    $pagedata->set('route', $route);
    $pagedata->set('query_string', $query_string);

    // -----------------------

    // Load conf
    $appconf = $model->getAppConf();
    $appconf->loadFromPHPFile();

    // Setup twig
    $t_cache_folder = $appconf->get('twig_cache_folder');
    if (strlen($t_cache_folder) == 0)
    {
        render500('"twig_cache_folder" has an empty value.');
        exit();
    }

    $t = $model->getTwigWrapper();
    $t->setTemplateFolder(PROJECT_TEMPLATE_FOLDER);
    $t->setCacheFolder($t_cache_folder);

    if (!$t->open())
    {
        render500('Cannot open Twig template system: ' . $t->getLastError()->format());
        exit();
    }

    // Setup routing
    $page_map = array();
    $page_map['/'] = (PROJECT_PAGE_FOLDER . '/index.php');
    foreach (CardSet::uriSlugCreateMap() as $k => $v)
    {
        $page_map['/booster/' . $k . '/'] = (PROJECT_PAGE_FOLDER . '/booster.php');
    }
    $page_map['/about/'] = (PROJECT_PAGE_FOLDER . '/about.php');
    $page_map['/debug/'] = (PROJECT_PAGE_FOLDER . '/debug.php');
    $page_map['/debug_500/'] = (PROJECT_PAGE_FOLDER . '/500.php');
    $page_map['/debug_404/'] = (PROJECT_PAGE_FOLDER . '/404.php');

    // Render page
    if (isset($page_map[$route]))
    {
        require_once($page_map[$route]);
    }
    else
    {
        render404();
    }
}
