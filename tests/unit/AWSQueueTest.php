<?php
class AWSQueueTest extends CTestCase
{
    /** 
     * Test validation rules for creating a queue
     */
    public function testNameValidation()
    {
        $q = new AWSQueue();
        $invalidCharacterNames = array(
            'Testing queue', //KO
            'Testing*^quee', //KO
        );

        foreach($invalidNames $name)
        {
            $q->name=$name;
            $this->assertFalse($q->validate());
        }

        $validCharacterNames = array(
            'Testing123456', //OK
            'Testing_13-45', //OK
        );

        foreach($validNames as $name)
        {
            $q->name=$name;
            $this->assertTrue($q->validate());
        }

        $q->name = sha1('just to get').sha1('80 characters string');
        $this->assertTrue($q->validate());

        $q->name .= 'oops';
        $this->assertFalse($q->validate());
    }

    /** 
     * Test rest of the attributes
     */
    public function testAttributesValidation()
    {
        $q = new AWSQueue();
        $q->name = 'validename';

        $q->visibilityTimeout = 43201;
        $this->assertFalse($q->validate());

        $q->visibilityTimeout = 0;
        $this->assertTrue($q->validate());

        $q->visibilityTimeout = 43200;
        $this->assertTrue($q->validate());
    
        $q->maximumMessageSize = 70000;
        $this->assertFalse($q->validate());

        $q->maximumMessageSize = 
    }
}
