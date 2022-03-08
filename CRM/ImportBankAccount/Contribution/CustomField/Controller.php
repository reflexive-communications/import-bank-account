<?php

/**
 * State machine controller class.
 * Based on the legacy solution: https://github.com/civicrm/civicrm-core/blob/409ffdf5d67e22566a7e9f6086900cc00b45a08d/CRM/Contribute/Import/Controller.php
 */
class CRM_ImportBankAccount_Contribution_CustomField_Controller extends CRM_Core_Controller
{

  /**
   * Class constructor.
   *
   * @param string $title
   * @param bool|int $action
   * @param bool $modal
   */
    public function __construct($title = null, $action = CRM_Core_Action::NONE, $modal = true)
    {
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
