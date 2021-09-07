<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Subsite Generator',
    'description' => '',
    'category' => 'module',
    'author' => 'Benjamin Franzke',
    'author_email' => 'bfr@qbus.de',
    'state' => 'stable',
    'version' => '2.3.3',
    'constraints' => array(
        'depends' => array(
            'typo3' => '9.5.0-10.4.99',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
    'autoload' => array(
        'psr-4' => array(
            'Qbus\\SubsiteGenerator\\' => 'Classes',
        ),
    ),
);
