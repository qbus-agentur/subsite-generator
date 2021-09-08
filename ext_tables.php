<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'SubsiteGenerator',
    'web',
    'subsitegenerator',
    'after:list',
    array(
        \Qbus\SubsiteGenerator\Controller\SubsiteGeneratorController::class => 'new,create',
    ),
    array(
        'access' => 'admin',
        'icon'   => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/module/module-reports.svg',
        'labels' => 'LLL:EXT:subsite_generator/Resources/Private/Language/locallang.xlf',
    )
);
