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
 * State machine controller class.
 * Based on the legacy solution: https://github.com/civicrm/civicrm-core/blob/409ffdf5d67e22566a7e9f6086900cc00b45a08d/CRM/Contribute/Import/Controller.php
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */
class CRM_ImportBankAccount_Contribution_CustomField_Controller extends CRM_Core_Controller {

  /**
   * Class constructor.
   *
   * @param string $title
   * @param bool|int $action
   * @param bool $modal
   */
  public function __construct($title = NULL, $action = CRM_Core_Action::NONE, $modal = TRUE) {
    parent::__construct($title, $modal);

    set_time_limit(0);

    $this->_stateMachine = new CRM_ImportBankAccount_Contribution_CustomField_StateMachine($this, $action);

    // create and instantiate the pages
    $this->addPages($this->_stateMachine, $action);

    // add all the actions
    $config = CRM_Core_Config::singleton();
    $this->addActions($config->uploadDir, ['uploadFile']);
  }

}
