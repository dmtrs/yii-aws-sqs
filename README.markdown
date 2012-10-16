yii-aws-sqs
===========

AWS Simple Queue Service component for the yii framework version 0.1

##Requirements

1. [Yii 1.1.0 and greater](http://yiiframework.com/download/)
2. [Amazon sdk for php](https://github.com/amazonwebservices/aws-sdk-for-php)

##Installation 
Get a copy extensions on your codebase, preferably added a git submodule with the following way:

        $ git submodule add https://github.com/dmtrs/yii-aws-sqs.git extensions/yii-aws-sqs


Copy file under extensions ( or folder of your choice ) and import it in your config file

``` php
<?php 
return array(
    ...
    'import' => array(
        ...
        'ext.yii-aws-sqs.*',
        ...
    ),
    ...
);
```

Also, config component in your config file:

``` php
<?php 
return array(
    ...
    'components' => array(
        ...
        'sqs' => array(
            'class'     => 'AWSQueueManager',//'ext.yii-aws-sqs.AWSQueueManager' if not imported
            'accessKey' => 'Access Key Id',
            'secretKey' => 'Secret Key Id',
        )
    ),
    ...
);
```

(Need to be fixed) Download the amazon sdk for php (check requirements) and include it in your `index.php`. Something ugly like:

``` php
<?php
    ...
    require_once $dir . '/../lib/aws-sdk-php/sdk.class.php';
    ...
?>
```

##Running test

In order you want to run test:

1. Access your test folder in your yii application. Usually `/var/www/html/myproject/tests/`
2. Define your SQS access & secret key in your `bootstrap.php` like:

``` php
<?php
....
define('SQS_ACCESS_KEY', 'KJGVJRHJ24v...');
define('SQS_SECRET_KEY', 'KJGVJRHJ24v...');
...
?>
```
3. Run the test. Example: 

```
    phpunit ../extensions/yii-aws-sqs/test/unit/
``` 

##Examples

###Queues
In order to get a list of queues access the property `AWSQueueManager::$queues` like

``` php
<?php
    //This will trigger a request to SQS the first time it is called & will return AWSQueueList object.
    $myAwsQueues = Yii::app()->queues;
    //In order to refresh use the `$refresh` param;
    $myAwsQueues = Yii::app()->getQueues(true);
```

A queue can be accessed either from the queues object either from the sqs object. Like:

``` php 
<?php 
    Yii::app()->sqs->queues->myTestQueue; // is equal to 
    Yii::app()->sqs->myTestQueue;
```

Creating a queue is easy

``` php
<?php
    if(($newQueue=Yii::app()->sqs->create('myTestingQueue'))!==null) {
        $newQueue->send('message');//Sending a message, see later for instructions
    }
``` 

[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/226c74050760aa30915ae903c7c32c4c "githalytics.com")](http://githalytics.com/dmtrs/yii-aws-sqs)
