<?php
// @codingStandardsIgnoreFile

class MD_TopicCategoryCode
{
	private $htmlString;

	public function __construct($mMD, $instanceType, $instanceName, $Legend='Keywords')
	{
		//$instanceType .= '-gmd:MD_TopicCategoryCode';
		
		$myIni = $mMD->loadINI('MD_TopicCategoryCode.ini');
		
		uasort($myIni, array(&$this, "cmp"));
		
		$topicKeywords = array();
		
		foreach ($myIni as $key => $value)
		{
			$topicKeywords[$key] = $value["title"];
		}
		
		$topicTooltips = array();
		
		foreach ($myIni as $key => $value)
		{
			$topicTooltips[$key] = $value["tooltip"];
		}
		
		$topicOrder = array();
		
		foreach ($myIni as $key => $value)
		{
			$topicOrder[$key] = $value["order"];
		}
		
		//echo '<pre>';
		//var_dump($myIni);
		//echo '</pre>';
		
		$xmlArray = $mMD->returnPath($instanceType);
		
		$selectedTopicKeyword = null;
		$sTopiclist = null;
					
		if ($xmlArray AND $xmlArray[0] != null)
		{
			asort($xmlArray);
			
			$sTopiclist = implode(";", $xmlArray);
							
			foreach ($xmlArray as $topicKeyword)
			{
				$selectedTopicKeyword[$topicKeyword] = $topicKeywords[$topicKeyword];
				unset($topicKeywords[$topicKeyword]);
			}
		}
		
		$mMD->jqUIs .= $mMD->twig->render('js/MD_TopicCategoryCode_UI.js', array('instanceName' => $instanceName, 'topicTooltips' => $topicTooltips));
			
		$twigArr = array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'sTopiclist' => $sTopiclist,'selectedTopicKeyword' => $selectedTopicKeyword,  'topicKeywords' => $topicKeywords, 'Legend' => $Legend);
		
		$this->htmlString = $mMD->twig->render('html/MD_TopicCategoryCode.html', $twigArr);
		
		$mMD->jsString .= $mMD->twig->render('js/MD_TopicCategoryCode.js', array('instanceName' => $instanceName, 'topicOrder' => $topicOrder));
		
		return true;
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
	
	static function cmp($a, $b)
	{
		if ($a['order'] == $b['order']) {
			return 0;
		}
		return ($a['order'] < $b['order']) ? -1 : 1;
	}
}