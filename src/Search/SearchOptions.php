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
    private $queryString = '*';

    /**
     * Only search for published Information Products.
     *
     * @var boolean
     */
    private $onlyPublishedInformationProducts = false;

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
    private $maxPerPage = 10;

    /**
     * Research Group Filter.
     *
     * @var array|null
     */
    private $researchGroupFilter;

    /**
     * Funding Organization Filter.
     *
     * @var array|null
     */
    private $fundingOrganizationFilter;

    /**
     * Product type descriptor Filter.
     *
     * @var array|null
     */
    private $productTypeDescFilter;

    /**
     * Digital resource type descriptor Filter.
     *
     * @var array|null
     */
    private $digitalTypeDescFilter;

    /**
     * List of facets.
     *
     * @var array|null
     */
    private $facets;

    /**
     * The datatype.
     *
     * @var array|null
     */
    private $dataType;

    /**
     * Dataset availability status.
     *
     * @var array|null
     */
    private $status;

    /**
     * Dataset tags filter.
     *
     * @var array|null
     */
    private $tags;

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
    public function onlyPublishedInformationProducts(): self
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
    public function setMaxPerPage(?int $maxPerPage): self
    {
        if (empty($maxPerPage) or $maxPerPage <= 0) {
            return $this;
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

    /**
     * Set the data type.
     *
     * @param string|null $dataType
     *
     * @return self
     */
    public function setDataType(?string $dataType): self
    {
        if (!empty($dataType)) {
            $this->dataType = explode(',', $dataType);
        }

        return $this;
    }

    /**
     * Get the data type.
     *
     * @return array
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set the dataset status.
     *
     * @param string|null $status
     *
     * @return self
     */
    public function setDatasetStatus(?string $status): self
    {
        if (!empty($status)) {
            $this->status = explode(',', $status);
        }

        return $this;
    }

    /**
     * Get the dataset status.
     *
     * @return array
     */
    public function getDatasetStatus()
    {
        return $this->status;
    }

    /**
     * Sets the research groups to be filtered.
     *
     * @param string|null $researchGroups The comma delimited list of Research Groups.
     *
     * @return self
     */
    public function setResearchGroupFilter(?string $researchGroups): self
    {
        if (!empty($researchGroups)) {
            $this->researchGroupFilter = explode(',', $researchGroups);
        }

        return $this;
    }

    /**
     * Return the list of research groups to be filtered on.
     *
     * @return array
     */
    public function getResearchGroupFilter(): array
    {
        return $this->researchGroupFilter;
    }

    /**
     * Is the Research Group filter set.
     *
     * @return boolean
     */
    public function isResearchGroupFilterSet(): bool
    {
        return isset($this->researchGroupFilter);
    }

    /**
     * Sets the Funding Organizations to be filtered.
     *
     * @param string|null $fundingOrganizations The comma delimited list of Funding Organizations.
     *
     * @return self
     */
    public function setFundingOrgFilter(?string $fundingOrganizations): self
    {
        if (!empty($fundingOrganizations)) {
            $this->fundingOrganizationFilter = explode(',', $fundingOrganizations);
        }

        return $this;
    }

    /**
     * Return the list of Funding Organizations to be filtered on.
     *
     * @return array
     */
    public function getFundingOrgFilter(): array
    {
        return $this->fundingOrganizationFilter;
    }

    /**
     * Is the Funding Organization filter set.
     *
     * @return boolean
     */
    public function isFundingOrgFilterSet(): bool
    {
        return isset($this->fundingOrganizationFilter);
    }

    /**
     * Get the product type descriptor filter.
     *
     * @return array
     */
    public function getProductTypeDescFilter(): array
    {
        return $this->productTypeDescFilter;
    }

    /**
     * Set the product type descriptor filter.
     *
     * @param string|null $productTypeDescriptors A list of comma delimited product type descriptors.
     *
     * @return self
     */
    public function setProductTypeDescFilter(?string $productTypeDescriptors): self
    {
        if (!empty($productTypeDescriptors)) {
            $this->productTypeDescFilter = explode(',', $productTypeDescriptors);
        }

        return $this;
    }

    /**
     * Is the Product Type Filter set.
     *
     * @return bool
     */
    public function isProductTypeDescFilterSet(): bool
    {
        return isset($this->productTypeDescFilter);
    }

    /**
     * Gets the digital resource type filter.
     *
     * @return array
     */
    public function getDigitalTypeDescFilter(): array
    {
        return $this->digitalTypeDescFilter;
    }

    /**
     * Sets the digital resource type filter.
     *
     * @param string|null $digitalResourceTypes A comma delimited list of Digital Resource Type Descriptors.
     *
     * @return void
     */
    public function setDigitalTypeDescFilter(?string $digitalResourceTypes): void
    {
        if (!empty($digitalResourceTypes)) {
            $this->digitalTypeDescFilter = explode(',', $digitalResourceTypes);
        }
    }

    /**
     * If the digital resource type filter is set.
     *
     * @return bool
     */
    public function isDigitalTypeDescFilterSet(): bool
    {
        return isset($this->digitalTypeDescFilter);
    }

    /**
     * Set a list of facets to use.
     *
     * @param array $facets
     *
     * @return self
     */
    public function setFacets(array $facets): self
    {
        $this->facets = $facets;

        return $this;
    }

    /**
     * Get the list of facets.
     *
     * @return array|null
     */
    public function getFacets(): ?array
    {
        return $this->facets;
    }

    /**
     * Get the tags.
     *
     * @return array|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * Set the tag filter.
     *
     * @param string|null $tags
     *
     * @return void
     */
    public function setTags(?string $tags): void
    {
        if (!empty($tags)) {
            $this->digitalTypeDescFilter = explode(',', $tags);
        } else {
            $this->tags = null;
        }
    }
}
