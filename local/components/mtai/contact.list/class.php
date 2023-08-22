<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\ContactTable;
use Bitrix\Crm\CompanyTable;
use Bitrix\Main\UserTable;
use Bitrix\Main\Loader;

class ContactListComponent extends \CBitrixComponent
{
    protected $gridId = 'contact_grid_list';

    public function __construct($component = null)
    {
        parent::__construct($component);

        Loader::includeModule("crm");
    }

    public function executeComponent()
    {
        $pageNav = $this->getPageNavigation();

        $this->arResult['GridId'] = $this->gridId;
        $this->arResult['GridColumns'] = $this->getGridColumns();
        $this->arResult['GridFilter'] = $this->getFilterFields();

        $filter = $this->prepareFilter($this->arResult['GridFilter']);
        $this->arResult['GridRows'] = $this->getGridRows($pageNav, $filter);

        $this->arResult['PageNavigation'] = $pageNav;
        $this->arResult['PageSizes'] = $this->getPageSizes();

        return $this->includeComponentTemplate();
    }

    protected function getGridColumns(): array
    {
        return [
            ['id' => 'ID', 'name' => Loc::getMessage('MTH_COLUMN_ID'), 'default' => true],
            ['id' => 'FULL_NAME', 'name' => Loc::getMessage('MTH_COLUMN_FULL_NAME'), 'default' => true],
            ['id' => 'COMPANY_TITLE', 'name' => Loc::getMessage('MTH_COLUMN_COMPANY_TITLE'), 'default' => true],
            ['id' => 'DATE_CREATE', 'name' => Loc::getMessage('MTH_COLUMN_CREATED_DATE'), 'default' => true],
            ['id' => 'ASSIGNED_BY', 'name' => Loc::getMessage('MTH_COLUMN_ASSIGNED_BY'), 'default' => true],
        ];
    }

    protected function getGridRows(PageNavigation $pageNavigation, array $filter): array
    {
        $rows = [];
        $order = ['ID' => 'desc'];

        $filter["=ID"] = \Bitrix\Crm\Binding\DealContactTable::getDealContactIDs($this->arParams["ENTITY_ID"]);
        $query = ContactTable::query()
            ->setSelect([
                '*',
                'COMPANY_TITLE' => 'COMPANY.TITLE',
                'ASSIGNED_BY_NAME' => 'ASSIGNED_BY.NAME',
                'ASSIGNED_BY_LAST_NAME' => 'ASSIGNED_BY.LAST_NAME',
                'ASSIGNED_BY_SECOND_NAME' => 'ASSIGNED_BY.SECOND_NAME',
                'ASSIGNED_BY_LOGIN' => 'ASSIGNED_BY.LOGIN'
            ])
            ->setFilter($filter)
            ->setOrder($order)
            ->setLimit($pageNavigation->getLimit())
            ->setOffset($pageNavigation->getOffset());

        if(!isset($_SESSION['CRM_GRID_DATA']))
        {
            $_SESSION['CRM_GRID_DATA'] = array();
        }
        $_SESSION['CRM_GRID_DATA'][$this->gridId] = array('FILTER' => $filter);

        $userRelation = new Reference(
            'ASSIGNED_BY',
            UserTable::class,
            Join::on('this.ASSIGNED_BY_ID', 'ref.ID'),
        );
        $query->registerRuntimeField($userRelation);

        $companyRelation = new Reference(
            'COMPANY',
            CompanyTable::class,
            Join::on('this.COMPANY_ID', 'ref.ID'),
        );
        $query->registerRuntimeField($companyRelation);

        $pageNavigation->setRecordCount($query->queryCountTotal());

        $resRows = $query->fetchAll();
        foreach ($resRows as $row) {
            $rowActions = [];

            $rows[] = [
                'id' => $row["ID"],
                'data' => $row,
                'actions' => $rowActions
            ];
        }

        return $rows;
    }

    protected function getPageNavigation(): PageNavigation
    {
        $gridOptions = new Options($this->gridId);
        $navParams = $gridOptions->GetNavParams();

        $pageNavigation = new PageNavigation($this->gridId);
        $pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();
        if($this->arParams["AJAX_REQUEST"] == 'Y' && $this->request->get("page")){
            $pageNavigation->setCurrentPage($this->request->get("page"));
        }

        return $pageNavigation;
    }

    protected function getPageSizes(): array
    {
        return [
            ['NAME' => '5', 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100']
        ];
    }

    protected function getFilterFields(): array
    {
        return [
            [
                'id' => 'FULL_NAME',
                'name' => Loc::getMessage('MTH_COLUMN_FULL_NAME'),
                'default' => true
            ],
            [
                'id' => 'COMPANY_ID',
                'name' => Loc::getMessage('MTH_COLUMN_COMPANY_TITLE'),
                'default' => true,
                'type' => 'dest_selector',
                'params' => [
                    'apiVersion' => 3,
                    'context' => 'CRM_CONTACT_LIST_FILTER_COMPANY_ID',
                    'contextCode' => 'CRM',
                    'useClientDatabase' => 'N',
                    'enableAll' => 'N',
                    'enableDepartments' => 'N',
                    'enableUsers' => 'N',
                    'enableSonetgroups' => 'N',
                    'allowEmailInvitation' => 'N',
                    'allowSearchEmailUsers' => 'N',
                    'departmentSelectDisable' => 'Y',
                    'enableCrm' => 'Y',
                    'enableCrmContacts' => 'N',
                    'enableCrmCompanies' => 'Y',
                    'addTabCrmCompanies' => 'Y',
                    'addTabCrmContacts' => 'N',
                    'convertJson' => 'Y'
                ]
            ],
            [
                'id' => 'ASSIGNED_BY_ID',
                'name' => Loc::getMessage('MTH_COLUMN_ASSIGNED_BY'),
                'default' => true,
                'type' => 'dest_selector',
                'params' =>
                    [
                        'context' => 'CRM_CONTACT_LIST_FILTER_ASSIGNED_BY_ID',
                        'multiple' => 'N',
                        'contextCode' => 'U',
                        'enableAll' => 'N',
                        'enableSonetgroups' => 'N',
                        'allowEmailInvitation' => 'N',
                        'allowSearchEmailUsers' => 'N',
                        'departmentSelectDisable' => 'Y',
                        'isNumeric' => 'Y',
                        'prefix' => 'U',
                    ],
            ],
            [
                'id' => 'DATE_CREATE',
                'name' => Loc::getMessage('MTH_COLUMN_CREATED_DATE'),
                'type' => 'date',
                'default' => true
            ]
        ];
    }

    protected function prepareFilter(array $gridFilter): array
    {
        $filter = [];

        $filterOption = new \Bitrix\Crm\Filter\UiFilterOptions($this->gridId, []);
        $filterData = $filterOption->getFilter();

        foreach ($filterData as $k => $v) {
            if($k == "COMPANY_ID"){
                $v = json_decode($v, true);
                if(isset($v["COMPANY"])){
                    $v = $v["COMPANY"];
                }
            }
            $filter[$k] = $v;
        }

        $filterPrepared = \Bitrix\Main\UI\Filter\Type::getLogicFilter($filter, $gridFilter);

        if (!empty($filter['FIND'])) {
            $findFilter = [
                'LOGIC' => 'OR',
                [
                    '%FULL_NAME' => $filter['FIND']
                ]
            ];

            if (!empty($filterPrepared)) {
                $filterPrepared[] = $findFilter;
            } else {
                $filterPrepared = $findFilter;
            }
        }

        return $filterPrepared;
    }

}