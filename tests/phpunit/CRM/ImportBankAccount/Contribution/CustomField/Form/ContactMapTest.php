<?php

/**
 * Testcases for the mapping form.
 *
 * @group headless
 */
class CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMapTest extends CRM_ImportBankAccount_HeadlessBase
{
    /**
     * preProcess test case.
     */
    public function testPreProcess()
    {
        $form = new CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMap();
        // The form controller seems to be null from the Form class.
        $form->controller = new CRM_ImportBankAccount_Contribution_CustomField_Controller();
        $form->set('fields', []);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
    }

    /**
     * formRule test case.
     */
    public function testFormRule()
    {
        $form = new CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMap();
        $fields = ['mapper' => []];
        $files = [];
        $form->controller = new CRM_ImportBankAccount_Contribution_CustomField_Controller();
        $form->set('fields', []);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertIsArray(CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMap::formRule($fields, $files, $form), 'It has to return error array');
    }

    /**
     * addRules test case.
     */
    public function testAddRules()
    {
        $form = new CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMap();
        self::assertEmpty($form->addRules(), 'AddRules supposed to be empty.');
    }

    /**
     * buildQuickForm test case.
     */
    public function testBuildQuickForm()
    {
        $form = new CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMap();
        $form->controller = new CRM_ImportBankAccount_Contribution_CustomField_Controller();
        $form->controller->set('fields', []);
        self::assertEmpty($form->preProcess(), 'PreProcess supposed to be empty.');
        self::assertEmpty($form->buildQuickForm(), 'BuildQuickForm supposed to be empty.');
    }
}
