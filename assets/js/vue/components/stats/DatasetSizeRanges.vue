<template>
    <DxChart
        id="chart"
        data-source="/stats/data/dataset-size-ranges"
        palette="Ocean"
    >
        <DxSeries
            argument-field="label"
            value-field="count"
            name="Data Sizes"
            type="bar"
        >
            <DxLabel :visible="true">
                <DxConnector :visible="true"/>
            </DxLabel>
        </DxSeries>

        <DxValueAxis
            value-type="numeric"
            type="continuous"
        />
        <DxTitle text="Dataset Size Ranges">
            <DxSubtitle text="(number of datasets)"/>
        </DxTitle>
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
    DxSubtitle
} from 'devextreme-vue/chart';

export default {
  components: {
    DxChart,
    DxSeries,
    DxLabel,
    DxConnector,
    DxValueAxis,
    DxTitle,
    DxSubtitle
  }
};
</script>
<style>
#chart {
    height: 440px;
}
</style>