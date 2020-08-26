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
            :color="$parent.getPrimaryColor()"
        >
            <DxLabel 
                :visible="true"
                :backgroundColor="$parent.getSecondaryColor()"
            >
                <DxConnector 
                :visible="true"
                :color="$parent.getSecondaryColor()"
                />
            </DxLabel>
        </DxSeries>

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
            <DxSubtitle text="(number of datasets)"/>
        </DxTitle>
        <DxLegend
            :visible="false"
        />
    </DxChart>
</template>
<script>
import {
    DxChart,
    DxSeries,
    DxLabel,
    DxConnector,
    DxValueAxis,
    DxTitle,
    DxSubtitle,
    DxTick,
    DxLegend
} from 'devextreme-vue/chart';

export default {
  components: {
    DxChart,
    DxSeries,
    DxLabel,
    DxConnector,
    DxValueAxis,
    DxTitle,
    DxSubtitle,
    DxTick,
    DxLegend
  }
};
</script>
<style>
#chart {
    height: 440px;
}
</style>