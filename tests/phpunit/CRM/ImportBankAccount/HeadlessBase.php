<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use Civi\Test\CiviEnvBuilder;
use Civi\Api4\CustomGroup;
use Civi\Api4\CustomField;

/**
 * Base testclass to eliminate the code duplication.
 *
 * @group headless
 */
class CRM_ImportBankAccount_HeadlessBase extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    protected static $index = 1;
    /**
     * Setup used when HeadlessInterface is implemented.
     *
     * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
     *
     * @link https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
     *
     * @return \Civi\Test\CiviEnvBuilder
     *
     * @throws \CRM_Extension_Exception_ParseException
     */
    public function setUpHeadless(): CiviEnvBuilder
    {
        return \Civi\Test::headless()
            ->installMe(__DIR__)
            ->apply();
    }

    public function setUp():void
    {
        parent::setUp();
    }

    public function tearDown():void
    {
        parent::tearDown();
    }
    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws \CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB and install depended extension
        \Civi\Test::headless()
            ->installMe(__DIR__)
            ->apply(true);
    }

    /**
     * Create a clean DB after running tests
     *
     * @throws CRM_Extension_Exception_ParseException
     */
    public static function tearDownAfterClass(): void
    {
        \Civi\Test::headless()
            ->uninstallMe(__DIR__)
            ->apply(true);
    }

    /**
     * Helper function for creating a brand new custom group and custom field
     * that could be used for contact mapping.
     *
     * @param string $extends
     * @param string $dataType
     * @param string $htmlType
     *
     * @return array
     */
    protected function createCustomField(string $extends, string $dataType, string $htmlType): array
    {
        $customGroup = CustomGroup::create(false)
            ->addValue('title', 'Test custom group v'.self::$index)
            ->addValue('extends', $extends)
            ->addValue('is_active', 1)
            ->addValue('is_public', 1)
            ->addValue('style', 'Inline')
            ->execute()
            ->first();
        $customField = CustomField::create(false)
            ->addValue('custom_group_id', $customGroup['id'])
            ->addValue('label', 'Field label v'.self::$index)
            ->addValue('data_type', $dataType)
            ->addValue('html_type', $htmlType)
            ->execute()
            ->first();
        self::$index += 1;
        return [
            'field' => $customField,
            'group' => $customGroup,
        ];
    }
}
