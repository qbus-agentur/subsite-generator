<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Qbus.' . $_EXTKEY,
    'web',
    'subsitegenerator',
    'after:list',
    array(
        'SubsiteGenerator' => 'new,create',
    ),
    array(
        'access' => 'admin',
        'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
        'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf',
    )
);
