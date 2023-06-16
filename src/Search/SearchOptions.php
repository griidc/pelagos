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
     * @var string
     */
    private $queryString = '*';

    /**
     * Only search for published Information Products.
     *
     * @var bool
     */
    private $onlyPublishedInformationProducts = false;

    /**
     * The current page of results.
     *
     * @var int
     */
    private $currentPage = 1;

    /**
     * Sort order of results.
     *
     * @var string
     */
    private $sortOrder = '';

    /**
     * The maximum results per page.
     *
     * @var int
     */
    private $maxPerPage = 10;

    /**
     * Research Group Filter.
     *
     * @var array|null
     */
    private $researchGroupFilter;

    /**
     * Funder Filter.
     *
     * @var array|null
     */
    private $funderFilter;

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
     * @var array
     */
    private $tags = [];

    /**
     * Date type filter.
     *
     * @var string
     */
    private $dateType;

    /**
     * Start Date range filter.
     *
     * @var string
     */
    private $rangeStartDate;

    /**
     * End Date range filter.
     *
     * @var string
     */
    private $rangeEndDate;

    /**
     * Specific field to be searched upon.
     *
     * @var string
     */
    private $field;

    /**
     * Date type collection date.
     */
    public const DATE_TYPE_COLLECTION = 'collectionDate';

    /**
     * Date type published date.
     */
    public const DATE_TYPE_PUBLISHED = 'publishedDate';

    /**
     * Class Contructor.
     */
    public function __construct(?string $queryString)
    {
        $this->setQueryString($queryString);
    }

    /**
     * Set the query string.
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
     */
    public function getQueryString(): string
    {
        return $this->queryString;
    }

    /**
     * If only published Information Products should be searched for.
     */
    public function onlyPublishedInformationProducts(): self
    {
        $this->onlyPublishedInformationProducts = true;

        return $this;
    }

    /**
     * Should I filter only by published Information Products.
     */
    public function shouldFilterOnlyPublishedInformationProducts(): bool
    {
        return $this->onlyPublishedInformationProducts;
    }

    /**
     * Set the curent page.
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
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Sets the max results per page.
     *
     * @param int $maxPerPage
     *
     * @throws \OutOfRangeException When max page number is 0;
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
     */
    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    /**
     * Set the data type.
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
     * @param string|null $researchGroups the comma delimited list of Research Groups
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
     */
    public function getResearchGroupFilter(): array
    {
        return $this->researchGroupFilter;
    }

    /**
     * Is the Research Group filter set.
     */
    public function isResearchGroupFilterSet(): bool
    {
        return isset($this->researchGroupFilter);
    }

    /**
     * Sets the Funders to be filtered.
     *
     * @param string|null $funders A comma delimited list of Funders
     */
    public function setFunderFilter(?string $funders): self
    {
        if (!empty($funders)) {
            $this->funderFilter = explode(',', $funders);
        }

        return $this;
    }

    /**
     * Return the list of Funders to be filtered on.
     */
    public function getFunderFilter(): array
    {
        return $this->funderFilter;
    }

    /**
     * Is the Funder filter set.
     */
    public function isFunderFilterSet(): bool
    {
        return isset($this->funderFilter);
    }

    /**
     * Get the product type descriptor filter.
     */
    public function getProductTypeDescFilter(): array
    {
        return $this->productTypeDescFilter;
    }

    /**
     * Set the product type descriptor filter.
     *
     * @param string|null $productTypeDescriptors a list of comma delimited product type descriptors
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
     */
    public function isProductTypeDescFilterSet(): bool
    {
        return isset($this->productTypeDescFilter);
    }

    /**
     * Gets the digital resource type filter.
     */
    public function getDigitalTypeDescFilter(): array
    {
        return $this->digitalTypeDescFilter;
    }

    /**
     * Sets the digital resource type filter.
     *
     * @param string|null $digitalResourceTypes a comma delimited list of Digital Resource Type Descriptors
     */
    public function setDigitalTypeDescFilter(?string $digitalResourceTypes): void
    {
        if (!empty($digitalResourceTypes)) {
            $this->digitalTypeDescFilter = explode(',', $digitalResourceTypes);
        }
    }

    /**
     * If the digital resource type filter is set.
     */
    public function isDigitalTypeDescFilterSet(): bool
    {
        return isset($this->digitalTypeDescFilter);
    }

    /**
     * Set a list of facets to use.
     */
    public function setFacets(array $facets): self
    {
        $this->facets = $facets;

        return $this;
    }

    /**
     * Get the list of facets.
     */
    public function getFacets(): ?array
    {
        return $this->facets;
    }

    /**
     * Get the tags.
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Set the tag filter.
     */
    public function setTags(?string $tags): void
    {
        if (!empty($tags)) {
            $this->tags = explode(',', $tags);
        }
    }

    /**
     * Get the date type filter.
     */
    public function getDateType(): string
    {
        return $this->dateType;
    }

    /**
     * Set the date type filter.
     */
    public function setDateType(string $dateType): void
    {
        $this->dateType = $dateType;
    }

    /**
     * Get the start date range filter.
     */
    public function getRangeStartDate(): string
    {
        return $this->rangeStartDate;
    }

    /**
     * Set the start date range filter.
     */
    public function setRangeStartDate(string $rangeStartDate): void
    {
        $this->rangeStartDate = $rangeStartDate;
    }

    /**
     * Get the end date range filter.
     */
    public function getRangeEndDate(): string
    {
        return $this->rangeEndDate;
    }

    /**
     * Set the end date range filter.
     */
    public function setRangeEndDate(string $rangeEndDate): void
    {
        $this->rangeEndDate = $rangeEndDate;
    }

    /**
     * Get the sort order filter.
     */
    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }

    /**
     * Set the sort order filter.
     */
    public function setSortOrder(string $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Get specific field to be searched upon.
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * Set specific field to be searched upon.
     */
    public function setField(string $field): void
    {
        $this->field = $field;
    }
}
