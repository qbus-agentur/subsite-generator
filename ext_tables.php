<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Qbus.SubsiteGenerator',
    'web',
    'subsitegenerator',
    'after:list',
    array(
        'SubsiteGenerator' => 'new,create',
    ),
    array(
        'access' => 'admin',
        'icon'   => 'EXT:reports/Resources/Public/Icons/module-reports.svg',
        'labels' => 'LLL:EXT:subsite_generator/Resources/Private/Language/locallang.xlf',
    )
);
