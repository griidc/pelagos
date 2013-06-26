<?php

class MD_TopicCategoryCode
{
	private $htmlString;
	
	private $topicKeywords = 
		array(	"biota" => "Biota",
				"boundaries" => "Boundaries",
				"economy" => "Economy",
				"elevation" => "Elevation",
				"environment" => "Environment",
				"farming" => "Farming",
				"geoscientificInformation" => "Geoscientific Information",
				"health" => "Health",
				"imageryBaseMapsEarthCover" => "Imagery/Base Maps/Earth Cover",
				"inlandWaters" => "Inland Waters",
				"location" => "Location",
				"intelligenceMilitary" => "Military Intelligence",
				"oceans" => "Oceans",
				"planningCadastre" => "Planning/Cadastre",
				"society" => "Society",
				"structure" => "Structure",
				"transportation" => "Transportation",
				"utilitiesCommunication" => "Utilities/Communication"
				);
	
	public function __construct($mMD, $instanceType, $instanceName, $Legend='Keywords')
	{
		//$instanceType .= '-gmd:MD_TopicCategoryCode';
		
		$xmlArray = $mMD->returnPath($instanceType);
		
		$selectedTopicKeyword = null;
		
		if ($xmlArray)
		{
			asort($xmlArray);
							
			foreach ($xmlArray as $topicKeyword)
			{
				$selectedTopicKeyword[$topicKeyword] = $this->topicKeywords[$topicKeyword];
				unset($this->topicKeywords[$topicKeyword]);
			}
		}
			
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'selectedTopicKeyword' => $selectedTopicKeyword,  'topicKeywords' => $this->topicKeywords, 'Legend' => $Legend);
		
		$this->htmlString = $mMD->twig->render('html/MD_TopicCategoryCode.html', $twigArr);
		
		$mMD->jsString .= $mMD->twig->render('js/MD_TopicCategoryCode.js', array('instanceName' => $instanceName));
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}