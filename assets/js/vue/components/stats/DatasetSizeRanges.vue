<template>
    <DxChart
        id="chart"
        data-source="/stats/data/dataset-size-ranges"
    >
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
            precision="1"
            format="decimal"
        >
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
    DxSubtitle,
    DxTick,
    DxLegend,
    DxMargin
} from 'devextreme-vue/chart';

export default {
    name: "GivemeName",
    components: {
        DxChart,
        DxSeries,
        DxLabel,
        DxConnector,
        DxValueAxis,
        DxTitle,
        DxSubtitle,
        DxTick,
        DxLegend,
        DxMargin
    },
    mixins: [themeFunctions]
};
</script>
<style>
#chart {
    height: 440px;
}
</style>