<?php

class CRM_ImportBankAccount_Service
{
    public const ORIGINAL_CONTACT_IDENTIFIERS = ['contribution_contact_id', 'email', 'external_identifier'];
    /*
     * It returns the custom field options for the contact mapping.
     * Only thise are relevants where the data type is Strings and
     * the html type is Text.
     *
     * @return array
     */
    public static function customTextFields(): array
    {
        $fields = CRM_Core_BAO_UFField::getAvailableFields();
        $contactParamNames = ['Contact', 'Individual'];
        $customFields = [];
        foreach ($fields as $k => $v) {
            if (array_search($k, $contactParamNames) === false) {
                continue;
            }
            foreach ($v as $key => $value) {
                if (!array_key_exists('data_type', $value) || $value['data_type'] !== 'String' || !array_key_exists('html_type', $value) || $value['html_type'] !== 'Text') {
                    continue;
                }
                if (CRM_Core_BAO_CustomField::getKeyID($key)) {
                    $customFields[$key] = $value;
                }
            }
        }
        return $customFields;
    }
    /*
     * It creates an array from the custom field list that could be used as
     * options in a select.
     *
     * @param array $fields the output of the customTextFields function.
     *
     * @return array
     */
    public static function mapCustomFieldsToSelectOptions(array $fields): array
    {
        $options = [];
        foreach ($fields as $key => $value) {
            $customFieldId = CRM_Core_BAO_CustomField::getKeyID($key);
            $customGroupId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', $customFieldId, 'custom_group_id');
            $customGroupName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', $customGroupId, 'title');
            $options[$key] = $value['title'] . ' :: ' . $customGroupName . ' (match to contact)';
        }
        return $options;
    }
    /**
     * It gets an array as input and extracts those that are custom text fields
     *
     * @param array $data
     *
     * @return array
     */
    public static function extractCustomTextFields(array $data): array
    {
        $contactCustomFields = self::mapCustomFieldsToSelectOptions(self::customTextFields());
        $extracted = [];
        foreach ($contactCustomFields as $k => $v) {
            if (in_array($k, $data)) {
                $extracted[] = $k;
            }
        }
        return $extracted;
    }
    /**
     * It gets an array as an input, extract the contact custom field, and maps it to contact ids.
     *
     * @param array $data
     *
     * @return array
     */
    public static function getContactsBasedOnCustomField(array $data): array
    {
        $contactCustomFields = self::mapCustomFieldsToSelectOptions(self::customTextFields());
        $customField = [
            'name' => '',
            'value' => '',
        ];
        $inputKeys = array_keys($data);
        foreach ($contactCustomFields as $k => $v) {
            if (in_array($k, $inputKeys)) {
                $customField['name'] = $k;
                $customField['value'] = $data[$k];
                break;
            }
        }
        $contacts = civicrm_api3('Contact', 'get', [
            'sequential' => 1,
            'return' => ['id', 'contact_type'],
            $customField['name'] => $customField['value'],
        ]);
        return $contacts['values'];
    }
}
