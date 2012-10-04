<?php
Yii::import('ext.yii-aws-sqs.*');
/**
 * AWSQueueManager
 */
class AWSQueueManager extends CApplicationComponent
{
    /**
     * @var string SQS access key (a.k.a. AWS_KEY)
     */
    public $accessKey;

    /**
     * @var string SQS secret key (a.k.a. AWS_SECRET_KEY)
     */
    public $secretKey;

    /**
     * @var AmazonSQS
     */
    private $_sqs;

    /**
     * @var CList queues list
     */
    private $_queues;

    /**
     * @var string last request id
     * @see AWSQueueManager::getLastRequestId()
     */
    private $_lastRequestId;

    /**
     * @var array last error
     */
    private $_errors=array();

    /**
     * Initializes the application component.
     */
    public function init()
    {
        if($this->accessKey===null || $this->secretKey===null)
            throw new CException(__CLASS__.' $accessKey and $secretKey must be set');

        $this->_sqs = new AmazonSQS(array(
            'key'    => $this->accessKey,
            'secret' => $this->secretKey
        ));
        parent::init();
    }

    /** 
     * @return array error details
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @var string last request id
     */
    public function getLastRequestId()
    {
        return $this->_lastRequestId;
    }

    /**
     * @return CList queues list
     */
    public function getQueues($refresh=false)
    {
        if($this->_queues===null || $refresh) {
            $this->_queues = new AWSQueueList();
            $this->_queues->caseSensitive = true;
            $response = $this->parseResponse($this->_sqs->list_queues());

            if(!empty($response->body->ListQueuesResult)) {
                var_dump(1);
                foreach($response->body->ListQueuesResult->QueueUrl as $qUrl)
                {
                    $q = new AWSQueue($qUrl);
                    $this->_queues->add($q->name,$q);
                }
            }
        }
        return $this->_queues;
    }

    /** 
     * @param string $url     url of the queue to send message
     * @param string $message message to send
     * @param array  $options extra options for the message
     * @return boolean message was succesfull 
     */
    public function send($url, $message, $options=array())
    {
        return (($r=$this->parseResponse($this->_sqs->send_message($url, $message, $options)))!==false);
    }

    /**
     * @param string $url     url of the queue to send message
     * @param array  $options extra options for the message
     * @return AWSMessage the message received
     */
    public function receive($url, $options=array())
    {
        $msg = null;
        if(($r=$this->parseResponse($this->_sqs->receive_message($url, $options)))!==false) {
            if(!empty($r->body->ReceiveMessageResult)) { 
                $msg = new AWSMessage();
                $msg->body          = (string)$r->body->ReceiveMessageResult->Message->Body;
                $msg->md5           = (string)$r->body->ReceiveMessageResult->Message->MD5OfBody;
                $msg->id            = (string)$r->body->ReceiveMessageResult->Message->MessageId;
                $msg->receiptHandle = (string)$r->body->ReceiveMessageResult->Message->ReceiptHandle;
            }
        }
        return $msg;
    }

    /**
     * Delete a message from a queue
     *
     * @param string $url           url of the queue
     * @param mixed  $receiptHandle AWSMessage contain the receiptHandle or the receipthandle for the message
     * @return boolean if message was delete succesfull
     */
    public function delete($url, $handle, $options=array())
    {
        $receiptHandle = ($handle instanceof AWSMessage) ? $handle->receiptHandle : $handle;
        return (($r=$this->parseResponse($this->_sqs->delete_message($url, $receiptHandle, $options)))!==false);
    }

    /**
     * Parse response to get the last request id, check for errrors
     *
     * @param CFResponse response object to parse
     * @return array
     */
    private function parseResponse($response)
    {
        $this->_errors=array();
        $this->_lastRequestId = (string)$response->body->ResponseMetadata->RequestId;

        if($response->isOK()) {
            return $response;
        } else {
            $this->_errors = array(
                'id'      => (string)$response->body->RequestId,
                'code'    => (string)$response->body->Error->Code,
                'message' => (string)$response->body->Error->Message,
            );
            Yii::log(implode(' - ', array_values($this->_errors)),'error','ext.sqs');
        }
        return false;
    }
    
    public function create($name)
    {
        return ($this->parseResponse($this->_sqs->create_queue($name))!==false);
    }
}
