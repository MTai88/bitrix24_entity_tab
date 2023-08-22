<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */

\Bitrix\Main\UI\Extension::load("ui.tooltip");
\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/interface_grid.js');
\Bitrix\Main\UI\Extension::load("ui.icons.b24");

$formatAuthorByCell = function($row)
{
	$format = \CSite::getNameFormat();
	$name = \CUser::FormatName($format, [
		'NAME' => $row['ASSIGNED_BY_NAME'],
		'SECOND_NAME' => $row['ASSIGNED_BY_SECOND_NAME'],
		'LAST_NAME' => $row['ASSIGNED_BY_LAST_NAME'],
		'LOGIN' => $row['ASSIGNED_BY_LOGIN'],
	],
		false,
		false
	);
	$url = "/company/personal/user/{$row['ASSIGNED_BY_ID']}/";

	return sprintf(
		'<a href="%s" bx-tooltip-user-id="%s" bx-tooltip-classname="intrantet-user-selector-tooltip">%s</a>',
		$url,
		$row['ASSIGNED_BY'],
		htmlspecialcharsbx($name)
	);
};

foreach ($arResult['GridRows'] as $index => $gridRow)
{
	$arResult['GridRows'][$index]['data']['ASSIGNED_BY'] = $formatAuthorByCell($gridRow['data']);
}

$APPLICATION->ShowViewContent('crm-internal-filter');

$gridManagerID = $arResult['GridId'].'_MANAGER';
$APPLICATION->IncludeComponent(
    'bitrix:crm.interface.grid',
    'titleflex',
    array(
        'GRID_ID' => $arResult['GridId'],
        'HEADERS' => $arResult['GridColumns'],
        'SORT' => [],
        'SORT_VARS' => [],
        'ROWS' => $arResult['GridRows'],
        'FORM_ID' => "",
        'TAB_ID' => "tab_contacts",
        'AJAX_ID' => \CAjax::getComponentID('mtai:contact.list', '.default', ''),
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_HISTORY' => 'N',
        'FILTER' => $arResult['GridFilter'],
        'FILTER_PRESETS' => [],
        'RENDER_FILTER_INTO_VIEW' => 'crm-internal-filter',
        'DISABLE_SEARCH' => true,
        'ACTION_PANEL' => array(),
        'PAGINATION' => [
            'PAGE_NUM' => $arResult['PageNavigation']->getCurrentPage(),
            'ENABLE_NEXT_PAGE' => $arResult['PageNavigation']->getCurrentPage() < $arResult['PageNavigation']->getPageCount(),
        ],
        'ENABLE_ROW_COUNT_LOADER' => true,
        'PRESERVE_HISTORY' => null,
        'IS_EXTERNAL_FILTER' => null,
        'EXTENSION' => array(
            'ID' => $gridManagerID,
            'CONFIG' => array(
                'ownerTypeName' => 'CONTACT',
                'gridId' => $arResult['GridId'],
                'serviceUrl' => '/local/components/mtai/contact.list/list.ajax.php?site='.SITE_ID.'&'.bitrix_sessid_get(),
                'loaderData' => $arParams['AJAX_LOADER'] ?? null
            ),
        ),
    ),
    $component
);