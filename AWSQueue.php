<?php
/** 
 * AWSQueue
 */
class AWSQueue extends CFormModel
{
    /** 
     * @var string name of the queue accepts letter, numbers, - and _
     */
    public $name;

    /** 
     * The length of time (in seconds) that a message that has been received
     * from a queue will be invisible to other receiving components when they
     * ask to receive messages. During the visibility timeout, the component
     * that received the message usually processes the message and then deletes
     * it from the queue.
     * @var integer Default 30 seconds
     */
    public $visibilityTimeout = 30;

    /**
     * TBD
     */
    public $policy; 
}
