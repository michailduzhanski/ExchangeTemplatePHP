<?php
return [
	'aliases'=>[
		'@bower'=>'@vendor/bower-asset',
		'@npm'=>'@vendor/npm-asset',
	],
	'vendorPath'=>dirname(dirname(__DIR__)).'/vendor',
    'bootstrap' => [
      'common\modules\imageStorage\Bootstrap',
      'common\modules\spriteImage\Bootstrap'
    ],
	'components'=>[
		'cache'=>[
			'class'=>'yii\caching\FileCache',
		],
        'drole' => [
          'class' => 'common\modules\drole\components\Drole'
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'common\components\DbMessageSource',
                ],
                'app'=> [
                    'class' => 'common\components\DbMessageSource',
                ],
            ],
        ],
        'lang' => [
            'class' => 'common\components\Lang'
        ],
        'SendMail' => [
            'class' => 'common\components\SendMail'
        ],
        'Pincode' => [
            'class' => 'common\modules\pinCode\components\Pincode'
        ],
        'ImageStorage' => [
            'class' => \common\modules\imageStorage\components\ImageStorage::class,
            'uploadRoot' => '@root/data/objects/{object_id}/img',
            'cacheRoot' => '@root/cache/objects/{object_id}/img',
            'config' => [
                'quality' => 70,
                'convertTo' => 'jpg',
                'extensions' => 'jpg, jpeg, png, gif, bmp'
            ],
            'owners' => [
                'default' => [
                    'rules' => [
                        'required' => true,
                        'maxSize' => 1024*1024*2,
                        //'maxFiles' => 5,
                        'minWidth' => 90,
                        'minHeight' => 90
                    ],
                    'thumbs' => [
                        'large' => [
                            'size' => [1024, 1024]
                        ],
                        'medium' => [
                            'size' => [600, 600]
                        ],
                        'preview' => [
                            'size' => [300, 300]
                        ],
                        'small' => [
                            'size' => [100, 100]
                        ],
                    ]
                ],
                'user_photo' => [
                    'rules' => [
                        'required' => true,
                        'maxSize' => 1024*1024*2,
                        //'maxFiles' => 5,
                        'minWidth' => 90,
                        'minHeight' => 90
                    ],
                    'thumbs' => [
                        /*'large' => [
                            'operations' => ['smartResize'],
                            'size' => [1000, 900]
                        ],*/
                        'preview' => [
                            'size' => [90, 90]
                        ],
                        'small' => [
                            'size' => [35, 35]
                        ]
                    ]
                ],
                'coin' => [
                    'rules' => [
                        'maxSize' => 1024*1024*2,
                        //'required' => true,
                        //'maxFiles' => 5,
                        'minWidth' => 50,
                        'minHeight' => 50
                    ],
                    'thumbs' => [
                        'preview' => [
                            'size' => [45, 45]
                        ],
                        'small' => [
                            'size' => [25, 25]
                        ],
                    ]
                ],
                'news' => [
                    'rules' => [
                        'maxSize' => 1024*1024*2,
                        //'maxFiles' => 5,
                        'minWidth' => 600,
                        'minHeight' => 300
                    ],
                    'thumbs' => [
                        'large' => [
                            'operations' => ['smartResize'],
                            'size' => [700, 400]
                        ],
                        'preview' => [
                            'size' => [263, 200]
                        ]
                    ]
                ]
            ]
        ],
        'SpriteImage' => [
            'class' => \common\modules\spriteImage\components\SpriteImage::class,
            'config' => [
                'coins' => [
                    'objectId' => '5cb705ea-6c8c-4dae-a620-248545acab14',
                    'field' => 'image',
                ]
            ]
        ]
	],
	'modules'=>[
		'drole'=>[
			'class'=>'common\modules\drole\DynamicRole'
		],
        'image-storage' => [
            'class' => 'common\modules\imageStorage\Module',
            'storageComponent' => 'ImageStorage',
            'storageData' => [
                [
                    'object' => '7052a1e5-8d00-43fd-8f57-f2e4de0c8b24',
                    'field' => '9b576f7e-842f-4810-a2d5-0cc5e97d0cc1',
                    'owners' => 'user_photo',
                ],
                [
                    'object' => '5cb705ea-6c8c-4dae-a620-248545acab14',
                    'field' => '7596f220-bc61-46e4-b6aa-83eb08d5f804',
                    'owners' => 'coin'
                ],
                [
                    'object' => '655d85fa-2199-40fe-9836-295bf8a8a316',
                    'field' => '3a9f9243-f55f-4f91-bc6c-8a36ec5573ab',
                    'owners' => 'news'
                ]
            ]
        ],
        'pincode' => [
            'class' => 'common\modules\pinCode\Module'
        ]
	]
];
