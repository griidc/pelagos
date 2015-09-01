<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('Funding Organization List');

$comp->addJS(
    array(
        '//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js',
        'static/js/fundingOrganizationList.js'
    )
);

$comp->addCSS(
    array(
        '//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css'
    )
);

$comp->setJSGlobals();

$twig = new Twig_Environment(new Twig_Loader_Filesystem('./templates'));

// get all Funding Organizations and put them in the Twig data array
$fundingOrganizations = $comp
               ->getEntityManager()
               ->getRepository('Pelagos\Entity\FundingOrganization')
               ->findAll();

$dataSet=array();
foreach ($fundingOrganizations as $fo) {
    $dataSet[] = array(
        $fo->getId(),
        $fo->getName(),
        "image goes here",
        $fo->getCreationTimeStampAsISO(true),
        $fo->getCreator(),
        $fo->getModificationTimeStampAsISO(true),
        $fo->getModifier()
    );
}


$comp->addJS("var dataSet = " . json_encode($dataSet), 'inline');
echo $twig->render('html/index.html');

$comp->finalize();
