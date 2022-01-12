<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Managers\CsvManager;

class TestCsvManagerTestImpl extends CsvManager
{
    public const csvTestFileName = 'test.csv';
    public const csvTestLockFileName = 'test.csv.lock';
    public const headers = ['H1','H2','H3','H4'];
    private bool $useLocking;

    public function __construct(bool $useHeaders = false, bool $useLocking = true)
    {
        $this->useLocking = $useLocking;
        parent::__construct($useHeaders ? TestCsvManagerTestImpl::headers : []);
    }

    public function getFileName() : string
    {
        return TestCsvManagerTestImpl::csvTestFileName;
    }

    public function getLockFileName() : string
    {
        return TestCsvManagerTestImpl::csvTestLockFileName;
    }

    public function useLocking() : bool
    {
        return $this->useLocking;
    }

    public function addFields (array $fields)
    {
        parent::addFields($fields);
    }

}

final class CsvManagerTest extends GuardeBotTestCase
{
    protected function tearDown(): void
    {
        if(file_exists(TestCsvManagerTestImpl::csvTestFileName))
        {
            unlink(TestCsvManagerTestImpl::csvTestFileName);
        }

        if(file_exists(TestCsvManagerTestImpl::csvTestLockFileName))
        {
            unlink(TestCsvManagerTestImpl::csvTestLockFileName);
        }
    }

    /**
     * @testWith [true, true]
     *           [true, false]
     *           [false, true]
     *           [false, false]
     */
    public function testAddFieldsWriteToFile(bool $useHeaders, bool $useLocking)
    {
        //Arrange
        $sut = new TestCsvManagerTestImpl($useHeaders, $useLocking);
        $headers = $useHeaders ? implode(",",TestCsvManagerTestImpl::headers) : "";

        //Act
        $sut->addFields(['abc','123','def','456']);

        //Assert
        $tested = file_get_contents(TestCsvManagerTestImpl::csvTestFileName);
        $this->assertThat($tested, $this->equalTo($headers."\nabc,123,def,456\n"));
    }

    /**
     * @testWith [true, true]
     *           [true, false]
     *           [false, true]
     *           [false, false]
     */
    public function testHasFieldsReturnTrue(bool $useHeaders, bool $useLocking)
    {
        //Arrange
        $testedData = ["joe", "dan", "mike", "bart"];
        $data = implode(",",TestCsvManagerTestImpl::headers) . "\n" . implode(",",$testedData);
        file_put_contents(TestCsvManagerTestImpl::csvTestFileName, $data);
        $sut = new TestCsvManagerTestImpl($useHeaders, $useLocking);

        //Act
        $tested = $sut->hasFields($testedData);

        //Assert
        $this->assertThat($tested, $this->isTrue());
    }

    /**
     * @testWith [true, true]
     *           [true, false]
     *           [false, true]
     *           [false, false]
     */
    public function testHasFieldsReturnFalse(bool $useHeaders, bool $useLocking)
    {
        //Arrange
        $testedData = ["joe", "dan", "mike", "bart"];
        $data = implode(",",TestCsvManagerTestImpl::headers) . "\n" . implode(",",$testedData);
        file_put_contents(TestCsvManagerTestImpl::csvTestFileName, $data);
        $sut = new TestCsvManagerTestImpl($useHeaders, $useLocking);

        //Act
        $tested = $sut->hasFields(["should", "not", "be", "found"]);

        //Assert
        $this->assertThat($tested, $this->isFalse());
    }
}
