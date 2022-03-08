<?php

/**
 * Testcases for the state machine class.
 *
 * @group headless
 */
class CRM_ImportBankAccount_Contribution_CustomField_StateMachineTest extends CRM_ImportBankAccount_HeadlessBase
{
    /**
     * It tests the class constructor.
     * The pages has to be our custom one.
     */
    public function testConstructor():void
    {
        $sm = new CRM_ImportBankAccount_Contribution_CustomField_StateMachine(new CRM_ImportBankAccount_Contribution_CustomField_Controller());
        self::assertSame(CRM_ImportBankAccount_Contribution_CustomField_StateMachine::PAGES, $sm->getPages());
    }
}
