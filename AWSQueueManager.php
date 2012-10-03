<?php
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
            $response = $this->parseResponse($this->_sqs->list_queues());
            $this->_queues=array();
        }
        return $this->_queues;
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
        if($response->isOK) {
            $this->_lastRequestId = (string)$response->body->ResponseMetadata->RequestId;
            return $response->body;
        } else {
            $this->_lastRequestId = (string)$response->body->RequestId;
            $this->_errors = array(
                'id'      => $response->body->RequestId,
                'code'    => $response->body->Error->Code,
                'message' => $response->body->Error->Message
            );
            Yii::log(implode(' - ', array_values($this->_errors)),'error','ext.sqs');
        }
        return null;
    }
}
