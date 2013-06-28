<?php

class MD_Keywords
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $type='default')
	{
		$myIni = $mMD->loadINI('MD_Keywords.ini');
		
		$instanceVars = $myIni[$type];
		
		$xmlArray = $mMD->returnPath($instanceType);
		
		$keyWordList = null;
		$skwlist = null;
				
		if ($xmlArray)
		{
			foreach ($xmlArray as $sub)
			{
				$keywordTypeCode = $sub["gmd:type"]["gmd:MD_KeywordTypeCode"]["@content"];
				if ($keywordTypeCode == $type)
				{
					foreach ($sub["gmd:keyword"] as $keyWords)
					{
						$keyWordList[] = $keyWords["gco:CharacterString"];
					}
					$skwlist = implode(";", $keyWordList);
				}
			}
		}
		
		$instanceType .= '-gmd:MD_Keywords';
		
		$this->htmlString .= $mMD->twig->render('html/MD_Keywords.html', array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'type' => $type,'skwlist' => $skwlist, 'keyWordList' => $keyWordList, 'instanceVars' => $instanceVars));
		
		$mMD->jsString .= $mMD->twig->render('js/MD_Keywords.js', array('instanceName' => $instanceName));
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}

?>