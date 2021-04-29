<template>
    <div id="program-resources" class="text-center">
        <div class="summaryLabel text-center">Repository Summary</div>
        <div class="row">
            <div class="col-sm">
                <img src="~images/icon-datasets.png">
                <div class="count">{{ (datasets == 0) ? '-' : datasets }}</div>
                <div class="label">Datasets</div>
            </div>
            <div class="col-sm">
                <img src="~images/icon-filesize.png">
                <div class="count">{{ (totalsize == 0) ? '-' : totalsize }}</div>
                <div class="label">TB Of Data</div>
            </div>
            <div class="col-sm">
                <img src="~images/icon-downloads.png">
                <div class="count">{{ (totalDownloads == 0) ? '-' : totalDownloads }}</div>
                <div class="label">Total Downloads</div>
            </div>
            <div class="col-sm">
                <img src="~images/icon-projects.png">
                <div class="count">{{ (researchGroups == 0) ? '-' : researchGroups }}</div>
                <div class="label">{{ getRGLabel() }}</div>
            </div>
            <div class="col-sm">
                <img src="~images/icon-researchers.png">
                <div class="count">{{ (people  == 0) ? '-' : people }}</div>
                <div class="label">People</div>
            </div>
        </div>
    </div>
</template>

<script>
    import templateSwitch from "@/vue/utils/template-switch.js";
    import { getApi } from "@/vue/utils/axios";
    export default {
        name: "RepositorySummary",
        data() {
            return {
                datasets: 0,
                totalsize: 0,
                totalDownloads: 0,
                researchGroups: 0,
                people: 0,
            }
        },
        created () {
            try {
                getApi(Routing.generate('pelagos_app_ui_stats_getstatisticsjson')).then(response => {
                    this.datasets = response.totalDatasets;
                    this.totalsize = response.totalSize;
                    this.people = response.peopleCount;
                    this.researchGroups = response.researchGroupCount;
                    this.totalDownloads = response.totalDownloadCount;
                })
            } catch (error) {
                console.log(error);
            }
        },
        methods: {
            getRGLabel: function () { return templateSwitch.getProperty('researchGroup') }
        }
    }
</script>

<style scoped>
#program-resources {
    padding-bottom:20px;
    font-family:var(--main-fonts);
}

.summaryLabel {
    font-size:28px;
    font-weight:200;
    cursor:default;
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
</style>
