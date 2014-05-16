<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'REST API BY DIZ',
        'defaultController' => 'api',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
                'application.helpers.*',
	),
    
        'sourceLanguage' => 'en',
        'language' => 'ru',

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		
		'urlManager'=>array(
		    'urlFormat'=>'path',
                    'showScriptName'=>false,
		    'rules'=>array(
			// REST
                        //GET /api/cinema/<название кинотеатра>/schedule[?hall=номер зала]
		        array( 'api/cinemaschedule', 'pattern'=>'api/cinema/<cinema_name:\w+>/schedule', 'verb'=>'GET' ),
                        //GET /api/film/<название фильма>/schedule
                        array( 'api/film', 'pattern'=>'api/film/<film_name:\w+>/schedule', 'verb'=>'GET' ),
                        
                        //POST /api/tickets/buy?session=<id сеанса>&places=1,3,5,7
                        array( 'api/buyticket', 'pattern'=>'api/tickets/buy', 'verb'=>'POST' ),
                        //POST /api/tickets/reject/<уникальный код>
                        array( 'api/rejectticket', 'pattern'=>'api/tickets/reject/<code:\w+>', 'verb'=>'POST' ),
                        
                        //GET /api/session/<id сеанса>/places
                        array( 'api/showseats', 'pattern'=>'api/session/<session_id:\d+>/places', 'verb'=>'GET' ),
		        // Others
		        '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
		    ),
		),
		
		/*
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		*/
		// uncomment the following to use a MySQL database

		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=test',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'pass_here',
			'charset' => 'utf8',
                        'enableProfiling' => true,
		),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'api/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
                            
//                            array(
//                                'class'=>'CProfileLogRoute',
//                                'levels'=>'profile',
//                                'enabled'=>true,
//                                ),
                                
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'disasterovich@mail.ru',
	),
);