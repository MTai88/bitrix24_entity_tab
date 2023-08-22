### Bitrix24 example of custom tab in CRM entity with filter & grid in it

### Notes
- `local/php_interface/init.php` -> `onEntityDetailsTabsInitialized` event handler to add tab to CRM entity
- `local/components/mtai/contact.list/` component with filter & grid based on `bitrix:crm.interface.grid` component
 