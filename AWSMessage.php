<?php
class AWSMessage extends CModel
{

    const AWS_VISIBILITY_TIMEOUT = 'VisibilityTimeout';
    const AWS_SENT_TIMESTAMP = 'SentTimestamp';
    const AWS_SENDER_ID = 'SenderId';
    /**
     * @var mixed body of the message
     */
    public $body;

    /**
     * @var string id of the message
     */
    public $id;

    /**
     * @var string receiptHandle
     */
    public $receiptHandle;

    /**
     * @var string md5 of the message body
     */
    public $md5;
    /**
     * @var int Timestamp when the message was created
     */
    public $sentTimestamp;

    /**
     * @var string The id of the sender
     */
    public $senderId;


    public function attributeNames()
    {
        return array('body','id','receiptHandle','md5','sentTimestamp','senderId');
    }

    /**
     * Magic method to cast object to string
     * 
     * @return string the message's body
     */
    public function __toString()
    {
        return $this->body;
    }
}
