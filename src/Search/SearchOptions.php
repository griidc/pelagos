<?php

namespace App\Search;

/**
 * Search Options Class.
 */
class SearchOptions
{
    /**
     * The query string to be searched.
     *
     * @var string $queryString
     */
    private $queryString ='*';

    /**
     * Only search for published Information Products.
     *
     * @var boolean
     */
    private $onlyPublishedInformationProducts = true;

    /**
     * The current page of results.
     *
     * @var integer $currentPage
     */
    private $currentPage = 1;

    /**
     * The maximum results per page.
     *
     * @var integer $maxPerPage
     */
    private $maxPerPage = 1000;

    /**
     * Class Contructor.
     *
     * @param string|null $queryString
     */
    public function __construct(?string $queryString)
    {
        $this->setQueryString($queryString);
    }

    /**
     * Set the query string.
     *
     * @param string|null $queryString
     *
     * @return self
     */
    public function setQueryString(?string $queryString): self
    {
        if (empty($queryString)) {
            $queryString = '*';
        }

        $this->queryString = $queryString;

        return $this;
    }

    /**
     * Get the query string.
     *
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->queryString;
    }

    /**
     * If only published Information Products should be searched for.
     *
     * @return self
     */
    public function OnlyPublishedInformationProducts(): self
    {
        $this->onlyPublishedInformationProducts = true;

        return $this;
    }

    /**
     * Should I filter only by published Information Products.
     *
     * @return boolean
     */
    public function shouldFilterOnlyPublishedInformationProducts(): bool
    {
        return $this->onlyPublishedInformationProducts;
    }

    /**
     * Set the curent page.
     *
     * @param integer|null $currentPage
     *
     * @return self
     */
    public function setCurrentPage(?int $currentPage): self
    {
        if (!empty($currentPage)) {
            $this->currentPage = $currentPage;
        }

        return $this;
    }

    /**
     * Get the current page.
     *
     * @return integer
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Sets the max results per page.
     *
     * @param integer $maxPerPage
     *
     * @throws \OutOfRangeException When max page number is 0;
     *
     * @return self
     */
    public function setMaxPerPage(int $maxPerPage): self
    {
        if ($maxPerPage <= 0) {
            throw new \OutOfRangeException("Max per Page has to be a number greater than 0");
        }

        $this->maxPerPage = $maxPerPage;

        return $this;
    }

    /**
     * Get the max number of results per page.
     *
     * @return integer
     */
    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

}
