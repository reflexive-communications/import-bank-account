<?php

/**
 * This class previews the uploaded file and returns summary statistics.
 * Based on the legacy solution: https://github.com/civicrm/civicrm-core/blob/5db0bc3c1f54eaca4307f103a73bda596ae914d6/CRM/Contribute/Import/Form/Preview.php
 */
class CRM_ImportBankAccount_Contribution_CustomField_Form_Preview extends CRM_Contribute_Import_Form_Preview
{
    /**
     * Process the mapped fields and map it into the uploaded file preview the file and extract some summary statistics.
     * The duplication was necessary due to the import parser is instantiated and executed here.
     */
    public function postProcess()
    {
        $fileName = $this->controller->exportValue('DataSource', 'uploadFile');
        $separator = $this->controller->exportValue('DataSource', 'fieldSeparator');
        $skipColumnHeader = $this->controller->exportValue('DataSource', 'skipColumnHeader');
        $invalidRowCount = $this->get('invalidRowCount');
        $conflictRowCount = $this->get('conflictRowCount');
        $onDuplicate = $this->get('onDuplicate');
        $mapperSoftCreditType = $this->get('mapperSoftCreditType');

        $mapper = $this->controller->exportValue('ContactMap', 'mapper');
        $mapperKeys = [];
        $mapperSoftCredit = [];
        $mapperPhoneType = [];

        foreach ($mapper as $key => $value) {
            $mapperKeys[$key] = $mapper[$key][0];
            if (isset($mapper[$key][0]) && $mapper[$key][0] == 'soft_credit' && isset($mapper[$key])) {
                $mapperSoftCredit[$key] = $mapper[$key][1] ?? '';
                $mapperSoftCreditType[$key] = $mapperSoftCreditType[$key]['value'];
            } else {
                $mapperSoftCredit[$key] = $mapperSoftCreditType[$key] = null;
            }
        }

        // Replace the original parcer with the one implemented for the custom field contact mapping.
        $parser = new CRM_ImportBankAccount_Contribution_CustomField_Parser($mapperKeys, $mapperSoftCredit, $mapperPhoneType, $mapperSoftCreditType);

        $mapFields = $this->get('fields');

        foreach ($mapper as $key => $value) {
            $header = [];
            if (isset($mapFields[$mapper[$key][0]])) {
                $header[] = $mapFields[$mapper[$key][0]];
            }
            $mapperFields[] = implode(' - ', $header);
        }
        $parser->run(
            $fileName,
            $separator,
            $mapperFields,
            $skipColumnHeader,
            CRM_Import_Parser::MODE_IMPORT,
            $this->get('contactType'),
            $onDuplicate,
            $this->get('statusID'),
            $this->get('totalRowCount')
        );

        // Add all the necessary variables to the form.
        $parser->set($this, CRM_Import_Parser::MODE_IMPORT);

        // Check if there is any error occurred.

        $errorStack = CRM_Core_Error::singleton();
        $errors = $errorStack->getErrors();
        $errorMessage = [];

        if (is_array($errors)) {
            foreach ($errors as $key => $value) {
                $errorMessage[] = $value['message'];
            }

            $errorFile = $fileName['name'] . '.error.log';

            if ($fd = fopen($errorFile, 'w')) {
                fwrite($fd, implode('\n', $errorMessage));
            }
            fclose($fd);

            $this->set('errorFile', $errorFile);
            $urlParams = 'type=' . CRM_Import_Parser::ERROR . '&parser=CRM_Contribute_Import_Parser';
            $this->set('downloadErrorRecordsUrl', CRM_Utils_System::url('civicrm/export', $urlParams));
            $urlParams = 'type=' . CRM_Import_Parser::CONFLICT . '&parser=CRM_Contribute_Import_Parser';
            $this->set('downloadConflictRecordsUrl', CRM_Utils_System::url('civicrm/export', $urlParams));
            $urlParams = 'type=' . CRM_Import_Parser::NO_MATCH . '&parser=CRM_Contribute_Import_Parser';
            $this->set('downloadMismatchRecordsUrl', CRM_Utils_System::url('civicrm/export', $urlParams));
        }
    }
}
