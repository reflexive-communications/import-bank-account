<?php

/**
 * Testcases for the preview form.
 *
 * @group headless
 */
class CRM_ImportBankAccount_Contribution_CustomField_Form_PreviewTest extends CRM_ImportBankAccount_HeadlessBase
{
    /**
     * postProcess test case.
     */
    public function testPostProcess()
    {
        $customData = $this->createCustomField('Contact', 'String', 'Text');
        $keyName = 'custom_'.$customData['field']['id'];
        $contactWithDuplicationA = civicrm_api3('Contact', 'create', [
            'sequential' => 1,
            'contact_type' => 'Individual',
            'email' => '01@email.com',
            $keyName => '11',
        ]);
        $contactWithDuplicationB = civicrm_api3('Contact', 'create', [
            'sequential' => 1,
            'contact_type' => 'Individual',
            'email' => '02@email.com',
            $keyName => '11',
        ]);
        $contactWrongType = civicrm_api3('Contact', 'create', [
            'sequential' => 1,
            'contact_type' => 'Organization',
            'email' => '03@email.com',
            'organization_name' => 'Evil Corp',
            $keyName => '13',
        ]);
        $contactWithContribution = civicrm_api3('Contact', 'create', [
            'sequential' => 1,
            'contact_type' => 'Individual',
            'email' => '04@email.com',
            $keyName => '14',
        ]);
        $result = civicrm_api3('Contribution', 'create', [
            'sequential' => 1,
            'financial_type_id' => 'Donation',
            'receive_date' => '2021-01-01 11:11:11',
            'total_amount' => 1500,
            'trxn_id' => 'trxn005',
            'contact_id' => $contactWithContribution['values'][0]['id'],
        ]);
        $originalNumberOfContributionDuplicationA = civicrm_api3('Contribution', 'getcount', [
            'contact_id' => $contactWithDuplicationA['values'][0]['id'],
        ]);
        $originalNumberOfContributionDuplicationB = civicrm_api3('Contribution', 'getcount', [
            'contact_id' => $contactWithDuplicationB['values'][0]['id'],
        ]);
        $originalNumberOfContributiontWrongType = civicrm_api3('Contribution', 'getcount', [
            'contact_id' => $contactWrongType['values'][0]['id'],
        ]);
        $originalNumberOfContributiontContribution = civicrm_api3('Contribution', 'getcount', [
            'contact_id' => $contactWithContribution['values'][0]['id'],
        ]);
        $form = new CRM_ImportBankAccount_Contribution_CustomField_Form_Preview();
        $form->controller = new CRM_ImportBankAccount_Contribution_CustomField_Controller();
        $container =& $form->controller->container();
        $container['values']['ContactMap']['mapper'] = [
            0 => [0 => $keyName],
            1 => [0 => 'financial_type'],
            2 => [0 => 'total_amount'],
            3 => [0 => 'trxn_id'],
            4 => [0 => 'doNotImport'],
        ];
        $container['values']['DataSource']['uploadFile'] = ['name' => __DIR__.'/test.csv'];
        $container['values']['DataSource']['fieldSeparator'] = ',';
        $form->set('contactType', CRM_Import_Parser::CONTACT_INDIVIDUAL);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->postProcess(), 'PostProcess supposed to be empty.');
        // duplicationA has to be the same.
        $numberOfContributionDuplicationA = civicrm_api3('Contribution', 'getcount', [
            'contact_id' => $contactWithDuplicationA['values'][0]['id'],
        ]);
        self::assertSame($originalNumberOfContributionDuplicationA, $numberOfContributionDuplicationA);
        // duplicationB has to be the same.
        $numberOfContributionDuplicationB = civicrm_api3('Contribution', 'getcount', [
            'contact_id' => $contactWithDuplicationB['values'][0]['id'],
        ]);
        self::assertSame($originalNumberOfContributionDuplicationB, $numberOfContributionDuplicationB);
        // wrong type has to be the same.
        $numberOfContributiontWrongType = civicrm_api3('Contribution', 'getcount', [
            'contact_id' => $contactWrongType['values'][0]['id'],
        ]);
        self::assertSame($originalNumberOfContributiontWrongType, $numberOfContributiontWrongType);
        // contribution has to be increased.
        $numberOfContributiontContribution = civicrm_api3('Contribution', 'getcount', [
            'contact_id' => $contactWithContribution['values'][0]['id'],
        ]);
        self::assertSame($originalNumberOfContributiontContribution+1, $numberOfContributiontContribution);
    }
}
