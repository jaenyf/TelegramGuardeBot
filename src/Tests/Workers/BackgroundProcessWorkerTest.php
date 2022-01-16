<?php

declare(strict_types=1);

use TelegramGuardeBot\Tests\GuardeBotTestCase;
use TelegramGuardeBot\Workers\BackgroundProcessWorker;

class BackgroundProcessWorkerTestImpl extends  BackgroundProcessWorker
{

    public const testFileName = 'BackgroundProcessWorkerTestImpl.test';
    public function __construct($sleepSeconds)
    {
        parent::__construct(null);
        $this->withSingleRun(true)->withSleepSeconds($sleepSeconds);
    }

    public function do()
    {
        if(false === file_put_contents(BackgroundProcessWorkerTestImpl::testFileName, ''))
        {
            throw new \ErrorException("Failed to file_put_contents");
        }
    }

}

class BackgroundProcessWorkerTest extends GuardeBotTestCase
{
    private const defaultBpwSleepTime = 1;

    public function testStartCreatePhpProcFile() {
        //Arrange
        $sut = new BackgroundProcessWorkerTestImpl(BackgroundProcessWorkerTest::defaultBpwSleepTime);

        //Act
        $sut->start();

        //Assert
        $fileExist = file_exists('.bpw-' . $sut->getUid() . '.proc.php');
        $this->assertThat($fileExist, $this->isTrue());
        unlink('.bpw-' . $sut->getUid() . '.proc.php');
    }

    protected function setUp() : void{
        if(file_exists(BackgroundProcessWorkerTestImpl::testFileName)){
            unlink(BackgroundProcessWorkerTestImpl::testFileName);
        }
        parent::setUp();
    }

    protected function tearDown() : void{
        if(file_exists(BackgroundProcessWorkerTestImpl::testFileName)){
            unlink(BackgroundProcessWorkerTestImpl::testFileName);
        }
        parent::tearDown();
    }


    public function testStopRemovePhpProcFile() {
        //Arrange
        $sut = new BackgroundProcessWorkerTestImpl(BackgroundProcessWorkerTest::defaultBpwSleepTime);

        //Act
        $sut->start();
        $sut->stop();

        //Assert
        $fileExist = file_exists('.bpw-' . $sut->getUid() . '.proc.php');
        $this->assertThat($fileExist, $this->isFalse());
    }

    public function testDoMethodIsCalledWhenStarted() {
        //Arrange
        $sut = new BackgroundProcessWorkerTestImpl(BackgroundProcessWorkerTest::defaultBpwSleepTime);

        //Act
        $sut->start();
        sleep(BackgroundProcessWorkerTest::defaultBpwSleepTime + 1);

        //Assert
        $this->assertThat(file_exists(BackgroundProcessWorkerTestImpl::testFileName), $this->isTrue());
    }

    public function testStopIsCalledWhenBpwEnded() {
        $this->markTestSkipped('must be revised.');
        //Arrange
        if(file_exists(BackgroundProcessWorkerTestImpl::testFileName)){
            unlink(BackgroundProcessWorkerTestImpl::testFileName);
        }
        $sut = new BackgroundProcessWorkerTestImpl(BackgroundProcessWorkerTest::defaultBpwSleepTime);

        //Act
        $sut->start();
        sleep(BackgroundProcessWorkerTest::defaultBpwSleepTime + 1);

        //Assert
        $this->assertThat(file_exists('.bpw-' . $sut->getUid() . '.proc.php'), $this->isFalse());
    }
}
