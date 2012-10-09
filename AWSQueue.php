<?php
/**
 * AWSQueue
 */
class AWSQueue extends CModel
{
    /*
     * @var AWSQueueManager
     */
    private static $sqs;

    /**
     * @var string name of the queue accepts letter, numbers, - and _
     */
    private $_name;

    /**
     * @var string url of the queue
     */
    private $_url;

    public function __construct($url=null)
    {
        if($url!==null)
            $this->url = $url;
    }

    /**
     * @return string name of the queue
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string url of the queue
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @param string $url url of the queue
     */
    public function setUrl($url)
    {
        $p = parse_url($url);
        if(isset($p['path'])) {
            $path = explode('/',$p['path']);
            $this->_name = array_pop($path);
        }
        $this->_url=$url;
    }

    /**
     * @return array attributes of queue
     */
    public function attributeNames()
    {
        return array('name','url');
    }

    /**
     * @return AWSQueueManager the sqs application component
     */
    private function sqs()
    {
        if(self::$sqs!==null)
            return self::$sqs;
        else {
            self::$sqs=Yii::app()->sqs;
            if(self::$sqs instanceof AWSQueueManager)
                return self::$sqs;
            else
                throw new CException(Yii::t('yii','AWSQueue requires a "sqs" AWSQueueManager application component.'));
        }
    }

    /**
     * @param $message mixed message to add to the queue
     * @param $options array options for this message
     * @return boolean if added with success
     */
    public function send($message, $options=array())
    {
        if($this->_url!==null)
            return (boolean)$this->sqs()->send($this->url, (string)$message, $options);

        return false;
    }

    public function sendBatch($messageArray, $options=array())
    {
        return $this->sqs()->sendBatch($this->url, $messageArray, $options);
    }

    public function receiveBatch($items=10, $options=array())
    {
        $options['MaxNumberOfMessages']=$items;
        return $this->sqs()->receive($this->url, $options);
    }

    public function receive($options=array())
    {
        if($this->_url!==null)
            return $this->sqs()->receive($this->url, $options);

        return null;
    }

    public function delete($handle, $options=array())
    {
        if($this->_url!==null)
            return (boolean)$this->sqs()->delete($this->_url, $handle, $options);

        return false;
    }
}
