<?php
class AWSQueueManagerTest extends CTestCase
{
    public function testExceptionalInit()
    {
        $sqs=new AWSQueueManager(array());

        $this->setExpectedException('CException');
        $sqs->init();
    }

    public function testInit()
    {
        $sqs=new AWSQueueManager();
        $sqs->accessKey = 'access';
        $sqs->secretKey = 'secret';

        $sqs->init();
        $this->assertTrue($sqs->isInitialized);
    }
}
