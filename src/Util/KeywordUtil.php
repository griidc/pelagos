<?php

namespace App\Util;

use App\Entity\Keyword;
use Doctrine\Common\Collections\ArrayCollection;

class KeywordUtil
{
    /**
     * Get keywords by their level.
     *
     * @param array<array-key, Keyword> $keywords
     * @param integer                   $level
     */
    public function getKeywordsByLevel(array $keywords, int $level): array
    {
        $keywords = new ArrayCollection($keywords);

        $keywordsByLevel = $keywords->filter(
            function (Keyword $keyword) use ($level) {
                return $keyword->getLevel() === $level;
            });

        return $keywordsByLevel->toArray();
    }
}