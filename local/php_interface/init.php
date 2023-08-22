<?php

Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'crm',
    'onEntityDetailsTabsInitialized',
    static function(\Bitrix\Main\Event $event) {
        $params = $event->getParameters();

        if($params["entityTypeID"] == CCrmOwnerType::Deal) {
            $params['tabs'][] = [
                'id' => 'tab_contacts',
                'name' => 'Контакты',
                'loader' => [
                    'serviceUrl' => '/local/components/mtai/contact.list/lazyload.ajax.php?&site=' . SITE_ID . '&' . bitrix_sessid_get(),
                    'componentData' => [
                        'template' => '',
                        'params' => array(
                            'ENTITY_ID' => $params['entityID'],
                            'ENTITY_TYPE_ID' => CCrmOwnerType::Deal,
                        )
                    ],
                ]
            ];
        }

        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, $params);
    }
);