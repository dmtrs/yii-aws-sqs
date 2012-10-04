<?php
/** 
 * AWSQueue
 */
class AWSQueue extends CModel
{
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

    public function getName()
    {
        return $this->_name;
    }
    public function getUrl()
    {
        return $this->_url;
    }
    public function setUrl($url)
    {
        $p = parse_url($url);
        if(isset($p['path'])) {
            $path = explode('/',$p['path']);
            $this->_name = array_pop($path);
        }
        $this->_url=$url;
    }

    public function attributeNames()
    {
        return array(
            'name', 
            'url',
        );
    }
}
