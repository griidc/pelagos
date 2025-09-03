<template>
    <div id="program-resources" class="text-center">
        <div class="summaryLabel text-center">Repository Summary</div>
        <div class="row">
            <div class="col-sm">
                <img src="~images/icon-datasets.png" class="inline-block">
                <div class="count">{{ (datasets == 0) ? '-' : datasets }}</div>
                <div class="label">Datasets</div>
            </div>
            <div class="col-sm">
                <img src="~images/icon-filesize.png" class="inline-block">
                <div class="count">{{ (totalsize == 0) ? '-' : totalsize }}</div>
                <div class="label"> {{sizeUnit}} Of Data</div>
            </div>
            <div class="col-sm">
                <img src="~images/icon-downloads.png" class="inline-block">
                <div class="count">{{ (totalDownloads == 0) ? '-' : totalDownloads }}</div>
                <div class="label">Total Downloads</div>
            </div>
            <div class="col-sm">
                <img src="~images/icon-projects.png" class="inline-block">
                <div class="count">{{ (researchGroups == 0) ? '-' : researchGroups }}</div>
                <div class="label">{{ getRGLabel() }}</div>
            </div>
            <div class="col-sm">
                <img src="~images/icon-researchers.png" class="inline-block">
                <div class="count">{{ (people == 0) ? '-' : people }}</div>
                <div class="label">People</div>
            </div>
        </div>
    </div>
</template>

<script>
import { getApi } from '@/vue/utils/axiosService';
import templateSwitch from '../../utils/template-switch';

export default {
  name: 'RepositorySummary',
  data() {
    return {
      datasets: 0,
      totalsize: 0,
      totalDownloads: 0,
      researchGroups: 0,
      people: 0,
      sizeUnit: 'TB',
    };
  },
  created() {
    getApi(
      // eslint-disable-next-line no-undef
      Routing.generate('pelagos_app_ui_stats_getstatisticsjson'),
    ).then((response) => {
      const datasetSize = response.data.totalSize?.split(' ');
      if (datasetSize.length > 0) {
        [this.totalsize, this.sizeUnit] = datasetSize;
      }
      this.datasets = response.data.totalDatasets;
      this.people = response.data.peopleCount;
      this.researchGroups = response.data.researchGroupCount;
      this.totalDownloads = response.data.totalDownloadCount;
      // eslint-disable-next-line no-console
    }).catch((error) => console.log(error));
  },
  methods: {
    getRGLabel() {
      return templateSwitch.getProperty('researchGroup');
    },
  },
};
</script>

<style scoped>
#program-resources {
    padding-bottom: 20px;
    font-family: var(--main-fonts);
}

.summaryLabel {
    font-size: 28px;
    cursor: default;
    padding: 20px;
}

#program-resources .count {
    font-size: 30px;
    font-weight: 600;
    line-height: 1.33em;
}

#program-resources .label {
    font-size: 18px;
    text-transform: uppercase;
}

/* Center icons above counts/labels */
#program-resources .col-sm {
    display: flex;
    flex-direction: column;
    align-items: center;
}

#program-resources .col-sm img.inline-block {
    margin-bottom: 6px;
}
</style>
