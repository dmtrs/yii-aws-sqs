<?php
class AWSMessage extends CModel
{
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

    public function attributeNames()
    {
        return array('body','id','receiptHandle','md5');
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
