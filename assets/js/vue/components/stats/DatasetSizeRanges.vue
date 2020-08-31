<template>
    <DxChart
        id="dsr-chart"
        :data-source="datasetUrl"
    >
        <DxLoadingIndicator
            :enabled="true"
        />
        <DxSeries
            argument-field="label"
            value-field="count"
            name="Data Sizes"
            type="bar"
            :color="this.getThemeColor('chart-secondary')"
        >
            <DxLabel
                :visible="true"
                :backgroundColor="this.getThemeColor('chart-alternate')"
            >
                <DxConnector
                :visible="true"
                :color="this.getThemeColor('chart-alternate')"
                />
            </DxLabel>
        </DxSeries>
        <DxMargin :bottom="20"/>
        <DxValueAxis
            value-type="numeric"
            type="continuous"
            title="Number of Datasets"
        >
            <DxLabel>
                <DxFormat
                    type="decimal"
                />
            </DxLabel>
            <DxTick
                :visible="true"
            />
        </DxValueAxis>
        <DxTitle text="Dataset Size Ranges">
        </DxTitle>
        <DxLegend
            :visible="false"
        />
    </DxChart>
</template>
<script>
import {themeFunctions} from '@/vue/mixins/themeFunctions';

import {
    DxChart,
    DxSeries,
    DxLabel,
    DxConnector,
    DxValueAxis,
    DxTitle,
    DxTick,
    DxLegend,
    DxMargin,
    DxLoadingIndicator,
    DxFormat
} from 'devextreme-vue/chart';

export default {
    name: "DatasetSizeRanges",
    components: {
        DxChart,
        DxSeries,
        DxLabel,
        DxConnector,
        DxValueAxis,
        DxTitle,
        DxTick,
        DxLegend,
        DxMargin,
        DxLoadingIndicator,
        DxFormat
    },
    mixins: [themeFunctions],
    data() {
        return {
            datasetUrl: Routing.generate("pelagos_app_ui_stats_getdatasetsizeranges"),
        };
    },
};
</script>

<style>

</style>
