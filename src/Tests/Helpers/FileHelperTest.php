<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Helpers\FileHelper;

final class FileHelperTest extends GuardeBotTestCase
{

    private const testLockFileName = 'FileHelperTest.lock';
    private $lockPointer;

    protected function setUp(): void
    {
        $this->lockPointer = null;
    }

    protected function tearDown(): void
    {
        if(isset($this->lockPointer))
        {
            FileHelper::unlock($this->lockPointer, FileHelperTest::testLockFileName);
        }
        if(file_exists(FileHelperTest::testLockFileName))
        {
            unlink(FileHelperTest::testLockFileName);
        }
    }

    public function testLockCreateFile(): void
    {
        //Arrange / Act
        $this->lockPointer = FileHelper::lock(FileHelperTest::testLockFileName);

        //Assert
        $this->assertThat(file_exists(FileHelperTest::testLockFileName), $this->isTrue());
    }

    public function testUnlockRemoveFile(): void
    {
        //Arrange / Act
        $this->lockPointer = FileHelper::lock(FileHelperTest::testLockFileName);
        FileHelper::unlock($this->lockPointer, FileHelperTest::testLockFileName);
        $this->lockPointer = null;

        //Assert
        $this->assertThat(file_exists(FileHelperTest::testLockFileName), $this->isFalse());
    }

    public function testLockAquireLock(): void
    {
        //Arrange / Act
        $this->lockPointer = FileHelper::lock(FileHelperTest::testLockFileName);

        $wouldblock = false;
        $fp = fopen(FileHelperTest::testLockFileName, "w");
        if(flock($fp, LOCK_EX|LOCK_NB, $wouldblock))
        {
            $this->fail("flock succeeded while it should not");
        }
        fclose($fp);

        //Assert
        $this->assertThat($wouldblock, $this->equalTo(1));
    }

    public function testUnlockReleaseLock(): void
    {
        //Arrange / Act
        $this->lockPointer = FileHelper::lock(FileHelperTest::testLockFileName);
        FileHelper::unlock($this->lockPointer, FileHelperTest::testLockFileName);
        $this->lockPointer = null;

        $lockObtained = false;
        $fp = fopen(FileHelperTest::testLockFileName, "w");
        if(flock($fp, LOCK_EX|LOCK_NB, $wouldblock))
        {
            $lockObtained = true;
        }
        else
        {
            $this->fail("flock failed while it should not");
        }
        fclose($fp);

        //Assert
        $this->assertThat($wouldblock, $this->equalTo(0));
        $this->assertThat($lockObtained, $this->isTrue());
    }
}
