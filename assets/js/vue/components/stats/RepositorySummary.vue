<template>
    <div id="program-resources">
        <div style="display:table-row;">
            <div class="summaryLabel" style="display:table-cell;">Repository Summary</div>
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
.summaryLabel {
    font-size:28px;
    font-family:'Segoe UI Light', 'Helvetica Neue Light', 'Segoe UI', 'Helvetica Neue', 'Trebuchet MS', Verdana, sans-serif;
    font-weight:200;
    cursor:default;
    text-align: center;
    line-height: 100%;
    padding: 20px;
}

.display-table {
    display: table;
    width: 100%;
    height: 100vh;
}

.display-table-row {
    display: table-row;
}

.display-table-cell {
    display: table-cell;
    margin: 100px;
}

#program-resources {
    height:100%;
    width:100%;
    display:table;
    padding:20px;
}

#program-resources table {
    margin-left: auto;
    margin-right: auto;
    font-weight: normal;
    border-collapse: collapse;
    border-spacing: 0px;
}

#program-resources table td {
    text-align: center;
    border: none;
    padding-left: 20px;
    padding-right: 20px;
}

#program-resources .count {
    font-size: 30px;
    font-weight: 600;
    line-height: 1.33em;
    font-family: "Lato", Arial, sans-serif;
}

#program-resources .label {
    font-size: 14px;
    text-transform: uppercase;
}

</style>
