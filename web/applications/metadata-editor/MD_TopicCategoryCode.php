<?php

/*
 
 
	<option value="biota">Biota</option>
	<option value="boundaries">Boundaries</option>
	<option value="economy">Economy</option>
	<option value="elevation">Elevation</option>
	<option value="environment">Environment</option>
	<option value="farming">Farming</option>
	<option value="geoscientificInformation">Geoscientific Information</option>
	<option value="health">Health</option>
	<option value="imageryBaseMapsEarthCover">Imagery/Base Maps/Earth Cover</option>
	<option value="inlandWaters">Inland Waters</option>
	<option value="location">Location</option>
	<option value="intelligenceMilitary">Military Intelligence</option>
	<option value="oceans">Oceans</option>
	<option value="planningCadastre">Planning/Cadastre</option>
	<option value="society">Society</option>
	<option value="structure">Structure</option>
	<option value="transportation">Transportation</option>
	<option value="utilitiesCommunication">Utilities/Communication</option>
 
*/

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
		
		asort($xmlArray);
						
		foreach ($xmlArray as $topicKeyword)
		{
			$selectedTopicKeyword[$topicKeyword] = $this->topicKeywords[$topicKeyword];
			unset($this->topicKeywords[$topicKeyword]);
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