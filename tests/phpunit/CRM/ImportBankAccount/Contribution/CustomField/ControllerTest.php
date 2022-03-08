<?php

/**
 * Testcases for the controller class.
 *
 * @group headless
 */
class CRM_ImportBankAccount_Contribution_CustomField_ControllerTest extends CRM_ImportBankAccount_HeadlessBase
{
    /**
     * It tests the class constructor.
     * The state machine has to be our custom one.
     */
    public function testConstructor():void
    {
        $controller = new CRM_ImportBankAccount_Contribution_CustomField_Controller();
        $sm = $controller->getStateMachine();
        self::assertSame('CRM_ImportBankAccount_Contribution_CustomField_StateMachine', get_class($sm));
    }
}
