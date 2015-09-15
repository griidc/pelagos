<?php
// @codingStandardsIgnoreFile

class MD_Keywords
{
	private $htmlString;
	
	public function __construct($mMD, $instanceType, $instanceName, $type='default',$required=true)
	{
		$myIni = $mMD->loadINI('MD_Keywords.ini');
		
		$instanceVars = $myIni[$type];
		
		$xmlArray = $mMD->returnPath($instanceType.'-gmd:descriptiveKeywords');
						
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
						if (is_array($keyWords))
						{
							if (isset($keyWords["gco:CharacterString"]))
							{
								$keyWordList[] = $keyWords["gco:CharacterString"];
							}
						}
						else
						{
							$keyWordList[] = $keyWords;
						}
					}
					$skwlist = implode(";", $keyWordList);
				}
			}
		}
		
		//$instanceType .= '-gmd:MD_Keywords';
		
		$this->htmlString .= $mMD->twig->render('html/MD_Keywords.html', array('instanceName' => $instanceName, 'instanceType' => $instanceType, 'type' => $type,'skwlist' => $skwlist, 'keyWordList' => $keyWordList, 'instanceVars' => $instanceVars, 'required' => $required));
		
		$mMD->jsString .= $mMD->twig->render('js/MD_Keywords.js', array('instanceName' => $instanceName));
	}
	
	public function getHTML()
	{
		return $this->htmlString;
	}
}

?>