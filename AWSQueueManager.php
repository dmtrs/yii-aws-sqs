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
     * Returns a queue, property value, an event handler list or a behavior based on its name.
     * Do not call this method.
     */
    public function __get($name)
    {
        if($this->getQueues()->itemAt($name)!==null)
            return $this->queues->{$name};
        else
            return parent::__get($name);
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
     * Send a batch of messages. AWS SQS limits the message batches
     * with a limit of 10 per request. If $messageArray has more than 10 messages
     * then 2 requests will be triggered.
     *
     * @param string $url          url of the queue to send message
     * @param string $messageArray message to send
     * @param array  $options      extra options for the message
     * @return boolean message was succesfull 
     */
    public function sendBatch($url, $messageArray, $options=array())
    {
        $r=true;
        foreach(array_chunk($messageArray,10) as $batch)
        {
            $messages=array();
            foreach($batch as $i=>$message)
            {
                $messages[]=array(
                    'Id'          => $i,
                    'MessageBody' => (string)$message,
                );
            }
            $r=$r&&($this->parseResponse($this->_sqs->send_message_Batch($url, $messages, $options))!==false);
        }
        return $r;
    }

    /**
     * Receive messages from the queue
     * If there is no message returned then this function returns null.
     * In case of one message then a AWSMessage is returned for convienience, if more
     * then an array of AWSMessage objects is returned.
     *
     * @param string $url     url of the queue to send message
     * @param array  $options extra options for the message
     * @return mixed
     */
    public function receive($url, $options=array())
    {
        $msgs=array();
        if(($r=$this->parseResponse($this->_sqs->receive_message($url, $options)))!==false) {
            if(!empty($r->body->ReceiveMessageResult)) {
                foreach($r->body->ReceiveMessageResult->Message as $message)
                {
                    $m = new AWSMessage();
                    $m->body          = (string)$message->Body;
                    $m->md5           = (string)$message->MD5OfBody;
                    $m->id            = (string)$message->MessageId;
                    $m->receiptHandle = (string)$message->ReceiptHandle;
                    $msgs[]=$m;
                }
                return (count($msgs)===1) ? array_pop($msgs) : $msgs;
            }
        }
        return (isset($options['MaxNumberOfMessages'])) ? array() : null;
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
    
    /** 
     * @return mixed AWSQueue object if creation was succesfull, null else
     */
    public function create($name)
    {
        if(($r=$this->parseResponse($this->_sqs->create_queue($name)))!==false) {
            $q=new AWSQueue((string)$r->body->CreateQueueResult->QueueUrl);
            $this->queues->add($q->name, $q);
            return $q;
        }
        return null;
    }
}
