<?php

/**
 * Testclass for the service tests.
 *
 * @group headless
 */
class CRM_ImportBankAccount_ServiceTest extends CRM_ImportBankAccount_HeadlessBase
{
    /**
     * It tests the custom text field gathering method.
     */
    public function testCustomTextFields():void
    {
        // Create one good field.
        $this->createCustomField('Contact', 'String', 'Text');
        self::assertCount(1, CRM_ImportBankAccount_Service::customTextFields());
        // html type different
        $this->createCustomField('Contact', 'String', 'CheckBox');
        self::assertCount(1, CRM_ImportBankAccount_Service::customTextFields());
        // Data type
        $this->createCustomField('Contact', 'Money', 'Text');
        self::assertCount(1, CRM_ImportBankAccount_Service::customTextFields());
        // Organization field
        $this->createCustomField('Organization', 'String', 'Text');
        self::assertCount(1, CRM_ImportBankAccount_Service::customTextFields());
        // Create other good field.
        $this->createCustomField('Contact', 'String', 'Text');
        self::assertCount(2, CRM_ImportBankAccount_Service::customTextFields());
    }
    /**
     * It tests the custom text field option values.
     */
    public function testMapCustomFieldsToSelectOptions():void
    {
        $customData = $this->createCustomField('Contact', 'String', 'Text');
        $mapped = CRM_ImportBankAccount_Service::mapCustomFieldsToSelectOptions(CRM_ImportBankAccount_Service::customTextFields());
        $keyName = 'custom_'.$customData['field']['id'];
        $expectedName = $customData['field']['label'].' :: '.$customData['group']['title'].' (match to contact)';
        self::assertTrue(array_key_exists($keyName, $mapped));
        self::assertSame($expectedName, $mapped[$keyName]);
    }
    /**
     * It tests the custom text field extraction from a given input array.
     */
    public function testExtractCustomTextFields():void
    {
        $customData = $this->createCustomField('Contact', 'String', 'Text');
        $keyName = 'custom_'.$customData['field']['id'];
        $params = [
            'notrelevant' => '1',
            'other-not-relevant' => 2,
            $keyName => 'cool',
            'not-relevant-again' => 4,
        ];
        $cleaned = CRM_ImportBankAccount_Service::extractCustomTextFields(array_keys($params));
        self::assertCount(1, $cleaned);
        self::assertTrue(in_array($keyName, $cleaned));
    }
    /**
     * It tests the custom field to contact id mapper function.
     */
    public function testGetContactsBasedOnCustomField():void
    {
        $customData = $this->createCustomField('Contact', 'String', 'Text');
        $keyName = 'custom_'.$customData['field']['id'];
        $paramValues = [
            $keyName => '01',
        ];
        // First contact the param is not set.
        $firstContact = civicrm_api3('Contact', 'create', [
            'contact_type' => 'Individual',
            'email' => '01@email.com',
        ]);
        $contactIds = CRM_ImportBankAccount_Service::getContactsBasedOnCustomField($paramValues);
        self::assertCount(0, $contactIds);
        // Second contact the param is set with a given value.
        $secondContact = civicrm_api3('Contact', 'create', [
            'contact_type' => 'Individual',
            'email' => '02@email.com',
            $keyName => $paramValues[$keyName],
        ]);
        $contactIds = CRM_ImportBankAccount_Service::getContactsBasedOnCustomField($paramValues);
        self::assertCount(1, $contactIds);
        self::assertEquals($secondContact['id'], $contactIds[0]['id']);
        // Third contact the param is set with another value.
        $thirdContact = civicrm_api3('Contact', 'create', [
            'contact_type' => 'Individual',
            'email' => '03@email.com',
            $keyName => $paramValues[$keyName].'1',
        ]);
        $contactIds = CRM_ImportBankAccount_Service::getContactsBasedOnCustomField($paramValues);
        self::assertCount(1, $contactIds);
        self::assertEquals($secondContact['id'], $contactIds[0]['id']);
        // Last contact the param is set with the first given value.
        $lastContact = civicrm_api3('Contact', 'create', [
            'contact_type' => 'Individual',
            'email' => '04@email.com',
            $keyName => $paramValues[$keyName],
        ]);
        $contactIds = CRM_ImportBankAccount_Service::getContactsBasedOnCustomField($paramValues);
        self::assertCount(2, $contactIds);
    }
}
