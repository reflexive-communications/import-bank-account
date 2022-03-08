<?php

class CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMap extends CRM_Contribute_Import_Form_MapField
{
    /*
     * It removes the original contact map fields and then extends the list
     * with the custom fields.
     */
    public function preProcess()
    {
        parent::preProcess();
        // Unset the current contact map fields.
        foreach (CRM_ImportBankAccount_Service::ORIGINAL_CONTACT_IDENTIFIERS as $mapField) {
            unset($this->_mapperFields[$mapField]);
        }
        // Extend the fieldset with the custom fields.
        $contactCustomFields = CRM_ImportBankAccount_Service::mapCustomFieldsToSelectOptions(CRM_ImportBankAccount_Service::customTextFields());
        $this->_mapperFields = array_merge($this->_mapperFields, $contactCustomFields);
        asort($this->_mapperFields);
    }

    /**
     * Check if required fields (amount and financial type) are present.
     * Exactly one custom field must be set for the contact mapping.
     *
     * @param CRM_ImportBankAccount_CustomField_Form_ContactMap $self
     * @param array $importKeys
     * @param array $errors
     *
     * @return array
     */
    protected static function customCheckRequiredFields($self, array $importKeys, array $errors): array
    {
        $requiredFields = [
            'total_amount' => ts('Total Amount'),
            'financial_type' => ts('Financial Type'),
        ];
        foreach ($requiredFields as $field => $title) {
            if (!in_array($field, $importKeys)) {
                if (empty($errors['_qf_default'])) {
                    $errors['_qf_default'] = '';
                }
                $errors['_qf_default'] .= ts('Missing required field: %1', [1 => $title]) . '<br />';
            }
        }
        // One field has to be given exactly.
        if (count(CRM_ImportBankAccount_Service::extractCustomTextFields($importKeys)) !== 1) {
            $errors['_qf_default'] .= ts('One custom field has to be set for contact mapping.');
        }
        return $errors;
    }

    /**
     * Global validation rules for the form.
     * Based on the legacy solution: https://github.com/civicrm/civicrm-core/blob/5db0bc3c1f54eaca4307f103a73bda596ae914d6/CRM/Contribute/Import/Form/MapField.php#L311-L411
     * The email based deduplication related functionality has been removed, as this import does not expect emails as mapping parameters.
     *
     * @param array $fields
     *   Posted values of the form.
     *
     * @param $files
     * @param $self
     *
     * @return array
     *   list of errors to be posted back to the form
     */
    public static function formRule($fields, $files, $self)
    {
        $errors = [];
        $fieldMessage = null;
        $contactORContributionId = $self->_onDuplicate == CRM_Import_Parser::DUPLICATE_UPDATE ? 'contribution_id' : 'contribution_contact_id';
        if (!array_key_exists('savedMapping', $fields)) {
            $importKeys = [];
            foreach ($fields['mapper'] as $mapperPart) {
                $importKeys[] = $mapperPart[0];
            }

            $contactTypeId = $self->get('contactType');
            $contactTypes = [
                CRM_Import_Parser::CONTACT_INDIVIDUAL => 'Individual',
                CRM_Import_Parser::CONTACT_HOUSEHOLD => 'Household',
                CRM_Import_Parser::CONTACT_ORGANIZATION => 'Organization',
            ];
            foreach ($importKeys as $key => $val) {
                if ($val == "soft_credit") {
                    $mapperKey = CRM_Utils_Array::key('soft_credit', $importKeys);
                    if (empty($fields['mapper'][$mapperKey][1])) {
                        if (empty($errors['_qf_default'])) {
                            $errors['_qf_default'] = '';
                        }
                        $errors['_qf_default'] .= ts('Missing required fields: Soft Credit') . '<br />';
                    }
                }
            }
            $errors = self::customCheckRequiredFields($self, $importKeys, $errors);
            //at least one field should be mapped during update.
            if ($self->_onDuplicate == CRM_Import_Parser::DUPLICATE_UPDATE) {
                $atleastOne = false;
                foreach ($self->_mapperFields as $key => $field) {
                    if (in_array($key, $importKeys) &&
                        !in_array($key, [
                            'doNotImport',
                            'contribution_id',
                            'invoice_id',
                            'trxn_id',
                        ])
                    ) {
                        $atleastOne = true;
                        break;
                    }
                }
                if (!$atleastOne) {
                    $errors['_qf_default'] .= ts('At least one contribution field needs to be mapped for update during update mode.') . '<br />';
                }
            }
        }
        if (!empty($fields['saveMapping'])) {
            $nameField = $fields['saveMappingName'] ?? null;
            if (empty($nameField)) {
                $errors['saveMappingName'] = ts('Name is required to save Import Mapping');
            } else {
                if (CRM_Core_BAO_Mapping::checkMapping($nameField, CRM_Core_PseudoConstant::getKey('CRM_Core_BAO_Mapping', 'mapping_type_id', 'Import Contribution'))) {
                    $errors['saveMappingName'] = ts('Duplicate Import Contribution Mapping Name');
                }
            }
        }
        if (!empty($errors)) {
            if (!empty($errors['saveMappingName'])) {
                $_flag = 1;
                $assignError = new CRM_Core_Page();
                $assignError->assign('mappingDetailsError', $_flag);
            }
            if (!empty($errors['_qf_default'])) {
                CRM_Core_Session::setStatus($errors['_qf_default'], ts("Error"), "error");
                return $errors;
            }
        }
        return true;
    }

    /**
     * If your form requires special validation, add one or more callbacks here
     * In the legacy solution the rule was added in the buildQuickForm function.
     */
    public function addRules()
    {
        $this->addFormRule([
            'CRM_ImportBankAccount_Contribution_CustomField_Form_ContactMap',
            'formRule',
        ], $this);
    }

    /**
     * Build the form object.
     * Based on the legacy solution: https://github.com/civicrm/civicrm-core/blob/5db0bc3c1f54eaca4307f103a73bda596ae914d6/CRM/Contribute/Import/Form/MapField.php#L141-L309
     * The duplication was necessary due to the legacy code sets the form validation rules in this function instead of the addRules.
     *
     * @throws \CiviCRM_API3_Exception
     */
    public function buildQuickForm()
    {
        $savedMappingID = $this->get('savedMapping');
        $this->buildSavedMappingFields($savedMappingID);
        //-------- end of saved mapping stuff ---------

        $defaults = [];
        $mapperKeys = array_keys($this->_mapperFields);
        $hasHeaders = !empty($this->_columnHeaders);
        $headerPatterns = $this->get('headerPatterns');
        $dataPatterns = $this->get('dataPatterns');
        $mapperKeysValues = $this->controller->exportValue($this->_name, 'mapper');

        /* Initialize all field usages to false */
        foreach ($mapperKeys as $key) {
            $this->_fieldUsed[$key] = false;
        }
        $this->_location_types = CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id');
        $sel1 = $this->_mapperFields;

        if (!$this->get('onDuplicate')) {
            unset($sel1['id']);
            unset($sel1['contribution_id']);
        }

        $softCreditFields['contact_id'] = ts('Contact ID');
        $softCreditFields['external_identifier'] = ts('External ID');
        $softCreditFields['email'] = ts('Email');

        $sel2['soft_credit'] = $softCreditFields;
        $sel3['soft_credit']['contact_id'] = $sel3['soft_credit']['external_identifier'] = $sel3['soft_credit']['email'] = CRM_Core_OptionGroup::values('soft_credit_type');
        $sel4 = null;

        // end of soft credit section
        $js = "<script type='text/javascript'>\n";
        $formName = 'document.forms.' . $this->_name;

        //used to warn for mismatch column count or mismatch mapping
        $warning = 0;

        for ($i = 0; $i < $this->_columnCount; $i++) {
            $sel = &$this->addElement('hierselect', "mapper[$i]", ts('Mapper for Field %1', [1 => $i]), null);
            $jsSet = false;
            if ($this->get('savedMapping')) {
                [$mappingName, $mappingContactType] = CRM_Core_BAO_Mapping::getMappingFields($savedMappingID);

                $mappingName = $mappingName[1];
                $mappingContactType = $mappingContactType[1];
                if (isset($mappingName[$i])) {
                    if ($mappingName[$i] != ts('- do not import -')) {
                        $mappingHeader = array_keys($this->_mapperFields, $mappingName[$i]);
                        // reusing contact_type field array for soft credit
                        $softField = $mappingContactType[$i] ?? 0;

                        if (!$softField) {
                            $js .= "{$formName}['mapper[$i][1]'].style.display = 'none';\n";
                        }

                        $js .= "{$formName}['mapper[$i][2]'].style.display = 'none';\n";
                        $js .= "{$formName}['mapper[$i][3]'].style.display = 'none';\n";
                        $defaults["mapper[$i]"] = [
                            CRM_Utils_Array::value(0, $mappingHeader),
                            ($softField) ? $softField : "",
                            "",
                            "",
                        ];
                        $jsSet = true;
                    } else {
                        $defaults["mapper[$i]"] = [];
                    }
                    if (!$jsSet) {
                        for ($k = 1; $k < 4; $k++) {
                            $js .= "{$formName}['mapper[$i][$k]'].style.display = 'none';\n";
                        }
                    }
                } else {
                    // this load section to help mapping if we ran out of saved columns when doing Load Mapping
                    $js .= "swapOptions($formName, 'mapper[$i]', 0, 3, 'hs_mapper_0_');\n";

                    if ($hasHeaders) {
                        $defaults["mapper[$i]"] = [$this->defaultFromHeader($this->_columnHeaders[$i], $headerPatterns)];
                    } else {
                        $defaults["mapper[$i]"] = [$this->defaultFromData($dataPatterns, $i)];
                    }
                }
                //end of load mapping
            } else {
                $js .= "swapOptions($formName, 'mapper[$i]', 0, 3, 'hs_mapper_0_');\n";
                if ($hasHeaders) {
                    // do array search first to see if has mapped key
                    $columnKey = array_search($this->_columnHeaders[$i], $this->_mapperFields);
                    if (isset($this->_fieldUsed[$columnKey])) {
                        $defaults["mapper[$i]"] = $columnKey;
                        $this->_fieldUsed[$key] = true;
                    } else {
                        // Infer the default from the column names if we have them
                        $defaults["mapper[$i]"] = [
                            $this->defaultFromHeader($this->_columnHeaders[$i], $headerPatterns),
                            0,
                        ];
                    }
                } else {
                    // Otherwise guess the default from the form of the data
                    $defaults["mapper[$i]"] = [
                        $this->defaultFromData($dataPatterns, $i),
                        0,
                    ];
                }
                if (!empty($mapperKeysValues) && $mapperKeysValues[$i][0] == 'soft_credit') {
                    $js .= "cj('#mapper_" . $i . "_1').val($mapperKeysValues[$i][1]);\n";
                    $js .= "cj('#mapper_" . $i . "_2').val($mapperKeysValues[$i][2]);\n";
                }
            }
            $sel->setOptions([$sel1, $sel2, $sel3, $sel4]);
        }
        $js .= "</script>\n";
        $this->assign('initHideBoxes', $js);

        //set warning if mismatch in more than
        if (isset($mappingName)) {
            if (($this->_columnCount != count($mappingName))) {
                $warning++;
            }
        }
        if ($warning != 0 && $this->get('savedMapping')) {
            $session = CRM_Core_Session::singleton();
            $session->setStatus(ts('The data columns in this import file appear to be different from the saved mapping. Please verify that you have selected the correct saved mapping before continuing.'));
        } else {
            $session = CRM_Core_Session::singleton();
            $session->setStatus(null);
        }

        $this->setDefaults($defaults);

        $this->addButtons([
            [
                'type' => 'back',
                'name' => ts('Previous'),
            ],
            [
                'type' => 'next',
                'name' => ts('Continue'),
                'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                'isDefault' => true,
            ],
            [
                'type' => 'cancel',
                'name' => ts('Cancel'),
            ],
        ]);
    }
}
