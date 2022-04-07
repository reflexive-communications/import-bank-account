# import-bank-account

[![CI](https://github.com/reflexive-communications/import-bank-account/actions/workflows/main.yml/badge.svg)](https://github.com/reflexive-communications/import-bank-account/actions/workflows/main.yml)

This extension provides a custom importer.

Constribution import where the contact mapping is based on a contact custom field. If you want to execute this importer,
navigate to the `Contributions > Import Contributions (Bank Account)` menu. The data mapping screen provides the custom
fields as options for contact mapping instead of the legacy contact mapping options.

For the mapping to work, you need to set the bank account numbers for each contact beforehand. After this, the importer
can find the contact based on the account number and add the contribution.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.4+
* CiviCRM v5.43

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and install it
with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/reflexive-communications/import-bank-account.git
cv ext:enable import-bank-account
```
