<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

/**
 * State machine for managing different states of the Import process.
 * Based on the legacy solution: https://github.com/civicrm/civicrm-core/blob/409ffdf5d67e22566a7e9f6086900cc00b45a08d/CRM/Import/StateMachine.php
 */
class CRM_ImportBankAccount_Contribution_CustomField_StateMachine extends CRM_Core_StateMachine {

    public const PAGES = [
        'CRM_Contribute_Import_Form_DataSource' => null,
        'CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMap' => null,
        'CRM_ImportBankAccount_Contribution_CustomField_Form_Preview' => null,
        'CRM_Contribute_Import_Form_Summary' => null,
    ];

  /**
   * Class constructor.
   *
   * @param object $controller
   * @param \const|int $action
   */
  public function __construct($controller, $action = CRM_Core_Action::NONE) {
    parent::__construct($controller, $action);

    $this->_pages = self::PAGES;

    $this->addSequentialPages($this->_pages, $action);
  }

}
