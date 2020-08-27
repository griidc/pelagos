<template>
    <div id="program-resources" style="height:100%; width:100%; display:table;padding:100px;">
        <div style="display:table-row;">
            <div class="caption" style="display:table-cell;">Repository Summary</div>
        </div>
        <div style="display:table-row;">
            <div style="display:table-cell;">
                <table>
                    <tr>
                        <td>
                            <img src="~images/icon-datasets.png">
                            <div class="count">{{datasets}}</div>
                            <div class="label">Datasets</div>
                        </td>
                        <td>
                            <img src="~images/icon-filesize.png">
                            <div class="count">{{totalsize}}</div>
                            <div class="label">TB Of Data</div>
                        </td>
                        <td>
                            <img src="~images/icon-downloads.png">
                            <div class="count">{{totalDownloads}}</div>
                            <div class="label">Total Downloads</div>
                        </td>
                        <td>
                            <img src="~images/icon-projects.png">
                            <div class="count">{{researchGroups}}</div>
                            <div class="label">Research Groups</div>
                        </td>
                        <td>
                            <img src="~images/icon-researchers.png">
                            <div class="count">{{people}}</div>
                            <div class="label">People</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "RepositorySummary",
        data() {
            return {
                datasets: '-',
                totalsize: '-',
                totalDownloads: '-',
                researchGroups: '-',
                people: '-',
            }
        },
        created () {
            const axiosInstance = axios.create({});
            axiosInstance
                .get(Routing.generate('pelagos_app_ui_stats_getstatisticsjson'))
                .then(response => {
                    this.datasets = response.data.totalDatasets;
                    this.totalsize = response.data.totalSize;
                    this.people = response.data.peopleCount;
                    this.researchGroups = response.data.researchGroupCount;
                    this.totalDownloads = response.data.totalDownloadCount;
                }).catch(error => {
                    console.log(error);
            });
        }
    }
</script>

<style scoped>

</style>
