<template>
  <article class="card-group-item">
    <header class="card-header">
      <strong> {{ facetName.label }}</strong>
      <b-button
        @click="toggleCollapses([`accordion-${facetName.label}`])"
        variant="light"
      >
        <span class="when-opened" v-if="expandFacet">
          <i class="fas fa-chevron-down"></i>
        </span>
        <span class="when-closed" v-else>
          <i class="fas fa-chevron-up"></i>
        </span>
      </b-button>
    </header>
    <b-collapse
      :id="`accordion-${facetName.label}`"
      :visible="expandFacet"
    >
      <div class="filter-content">
        <div class="card-body">
          <div
            class="input-group pb-3"
            v-show="
              ['researchGroup', 'projectDirector'].includes(
                facetName.queryParam
              )
            "
          >
            <input
              class="form-control"
              placeholder="Search"
              type="text"
              v-model="facetSearch"
            />
            <div class="input-group-append">
              <button class="btn btn-alternate" type="button">
                <i class="fa fa-search"></i>
              </button>
            </div>
          </div>
          <form>
            <div v-bind:class="facetScrollable">
              <label
                class="form-check"
                v-for="facet in filteredFacets"
                v-bind:key="facet.id"
              >
                <input
                  class="form-check-input facet-aggregation"
                  :value="facet.id"
                  type="checkbox"
                  :id="facetName.queryParam + '_' + facet.id"
                  v-model="listOfCheckedFacets"
                  @change="facetChange"
                />
                <span
                  class="form-check-label"
                  v-if="facetName.queryParam === 'status'"
                  v-tooltip="{
                    content: statusTooltip(facet.name),
                    placement: 'top',
                  }"
                >
                  <span class="float-right badge badge-light round">{{
                    facet.count
                  }}</span>
                  {{ facet.name }}
                </span>
                <span
                  class="form-check-label"
                  v-else-if="facetName.queryParam === 'tags'"
                  v-tooltip="{
                    content: tagsTooltip(facet.name),
                    placement: 'top',
                  }"
                >
                  <span class="float-right badge badge-light round">{{
                    facet.count
                  }}</span>
                  {{ facet.name }}
                </span>
                <span
                  class="form-check-label"
                  v-tooltip="{
                    content: facet.name,
                    placement: 'top',
                  }"
                  v-else
                >
                  <span class="float-right badge badge-light round">{{
                    facet.count
                  }}</span>
                  {{ facetName.queryParam === 'researchGroup' ? facet.name : facet.shortName ? facet.shortName : facet.name }}
                </span>
              </label>
            </div>
          </form>
        </div>
      </div>
    </b-collapse>
  </article>
</template>

<script>
const maxFacetsToDisplay = 10;
export default {
  name: 'FacetGroups',
  props: {
    facetInfo: {
      type: Array,
    },
    facetName: {
      type: Object,
    },
    formValues: {
      type: Object,
    },
  },
  data() {
    return {
      facetSearch: '',
      listOfCheckedFacets: [],
      expandFacet: true,
    };
  },
  methods: {
    facetChange() {
      this.$emit(
        'facetClicked',
        `${this.facetName.queryParam}=${this.listOfCheckedFacets.join(',')}`,
      );
    },
    facetCheckBox() {
      if (this.facetName.queryParam in this.formValues) {
        if (this.formValues[this.facetName.queryParam]) {
          const splitFacets = this.formValues[this.facetName.queryParam].split(',');
          this.listOfCheckedFacets = [];
          splitFacets.forEach((value) => {
            this.listOfCheckedFacets.push(value);
          });
        } else {
          this.listOfCheckedFacets = [];
        }
      }
    },
    statusTooltip(datasetStatus) {
      let datasetStatusTooltip = '';
      switch (true) {
        case datasetStatus === 'Available':
          datasetStatusTooltip = 'This dataset is available for download.';
          break;
        case datasetStatus === 'Restricted':
          datasetStatusTooltip = 'This dataset is restricted for download.';
          break;
        case datasetStatus === 'Submitted':
          datasetStatusTooltip = 'This dataset has been submitted and is not available for download.';
          break;
        case datasetStatus === 'Identified':
          datasetStatusTooltip = 'This dataset has not been submitted and is not available for download.';
          break;
        default:
          break;
      }
      return datasetStatusTooltip;
    },
    tagsTooltip(tagName) {
      let tagTooltip = '';
      switch (true) {
        case tagName === 'ERDDAP':
          tagTooltip = 'This data is also available via the GRIIDC ERDDAP Server';
          break;
        case tagName === 'NCEI':
          tagTooltip = 'This data has also been Archived via NCEI';
          break;
        case tagName === 'Cold Storage':
          tagTooltip = 'This data has been put in Cold Storage and must be requested via Email';
          break;
        case tagName === 'Remotely Hosted':
          tagTooltip = 'This data is hosted on a external non-GRIIDC host';
          break;
        default:
          break;
      }
      return tagTooltip;
    },
    toggleCollapses(id) {
      this.$root.$emit('bv::toggle::collapse', id);
      this.expandFacet = !this.expandFacet;
    },
  },
  computed: {
    filteredFacets() {
      if (this.facetName.queryParam === 'researchGroup') {
        return this.facetInfo.filter((facetItem) => {
          const facetItemName = facetItem.shortName + facetItem.name;
          return (
            facetItemName
              .toLowerCase()
              .indexOf(this.facetSearch.toLowerCase()) > -1
          );
        });
      }
      if (this.facetName.queryParam === 'projectDirector') {
        return this.facetInfo.filter(
          (facetItem) => facetItem.name
            .toLowerCase()
            .indexOf(this.facetSearch.toLowerCase()) > -1,
        );
      }
      return this.facetInfo;
    },
    facetScrollable() {
      const scrollableClass = 'scrollable-facet';
      if (this.facetInfo.length > maxFacetsToDisplay) {
        return scrollableClass;
      }
      return null;
    },
  },
  created() {
    this.facetCheckBox();
    if (this.facetName.expanded !== undefined) {
      this.expandFacet = this.facetName.expanded;
    }
  },
  watch: {
    formValues() {
      this.facetCheckBox();
    },
  },
};
</script>

<style scoped lang="scss">
.scrollable-facet {
  height: auto;
  max-height: 20rem;
  overflow-y: auto;
}
.card-body {
  padding: 0.625rem !important;
}

article > .collapse {
  visibility:visible !important;
}
</style>
