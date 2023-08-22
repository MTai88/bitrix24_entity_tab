<?php

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site'])? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
if($siteID !== '')
{
    define('SITE_ID', $siteID);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}

if (!check_bitrix_sessid())
{
    die();
}

$componentData = isset($_REQUEST['PARAMS']) && is_array($_REQUEST['PARAMS']) ? $_REQUEST['PARAMS'] : [];
$params = isset($componentData['params']) && is_array($componentData['params']) ? $componentData['params'] : [];

global $APPLICATION;
Header('Content-Type: text/html; charset='.LANG_CHARSET);
$APPLICATION->ShowAjaxHead();

$params['AJAX_LOADER'] = array(
    'url' => '/local/components/mtai/contact.list/lazyload.ajax.php?&site='.SITE_ID.'&'.bitrix_sessid_get(),
    'method' => 'POST',
    'dataType' => 'ajax',
    'data' => array('PARAMS' => $params)
);

$params["AJAX_REQUEST"] = "Y";

$APPLICATION->IncludeComponent('mtai:contact.list',
    $params['template'] ?? '',
    $params
);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();