<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TelegramGuardeBot\Workers\BackgroundProcessWorker;

class BackgroundProcessWorkerTestImpl extends  BackgroundProcessWorker{

    public const testFileName = 'BackgroundProcessWorkerTestImpl.test';
    public function __construct($sleepSeconds)
    {
        parent::__construct();
        $this->withSingleRun(true)->withSleepSeconds($sleepSeconds);
        $this->doMethodWasCalled = false;
    }

    public function do(){
        fclose(fopen(BackgroundProcessWorkerTestImpl::testFileName, 'w'));
    }

}

class BackgroundProcessWorkerTest extends TestCase
{
    private const defaultBpwSleepTime = 1;

    public function testStartCreatePhpProcFile() {
        //Arrange
        $sut = new BackgroundProcessWorkerTestImpl(BackgroundProcessWorkerTest::defaultBpwSleepTime);

        //Act
        $sut->start();

        //Assert
        $fileExist = file_exists('_worker-' . $sut->getUid() . '.proc.php');
        $this->assertThat($fileExist, $this->isTrue());
    }


    public function testStopRemovePhpProcFile() {
        //Arrange
        $sut = new BackgroundProcessWorkerTestImpl(BackgroundProcessWorkerTest::defaultBpwSleepTime);

        //Act
        $sut->start();
        $sut->stop();

        //Assert
        $fileExist = file_exists('_worker-' . $sut->getUid() . '.proc.php');
        $this->assertThat($fileExist, $this->isFalse());
    }

    public function testDoMethodIsCalledWhenStarted() {
        //Arrange
        if(file_exists(BackgroundProcessWorkerTestImpl::testFileName)){
            unlink(BackgroundProcessWorkerTestImpl::testFileName);
        }
        $sut = new BackgroundProcessWorkerTestImpl(BackgroundProcessWorkerTest::defaultBpwSleepTime);

        //Act
        $sut->start();
        sleep(BackgroundProcessWorkerTest::defaultBpwSleepTime + 1);
        $sut->stop();

        //Assert
        $this->assertThat(file_exists(BackgroundProcessWorkerTestImpl::testFileName), $this->isTrue());
        unlink(BackgroundProcessWorkerTestImpl::testFileName);
    }

    //TODO fix this test, currently inconsistent uid after bpw persistance to php file
    public function testStopIsCalledWhenBpwEnded() {
        //Arrange
        if(file_exists(BackgroundProcessWorkerTestImpl::testFileName)){
            unlink(BackgroundProcessWorkerTestImpl::testFileName);
        }
        $sut = new BackgroundProcessWorkerTestImpl(BackgroundProcessWorkerTest::defaultBpwSleepTime);

        //Act
        $sut->start();
        sleep(BackgroundProcessWorkerTest::defaultBpwSleepTime + 2);


        //Assert
        $this->assertThat(file_exists(BackgroundProcessWorkerTestImpl::testFileName), $this->isFalse());
    }
}
