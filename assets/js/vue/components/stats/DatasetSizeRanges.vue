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
            :color="$parent.getThemeColor('primary')"
        >
            <DxLabel 
                :visible="true"
                :backgroundColor="$parent.getThemeColor('secondary')"
            >
                <DxConnector 
                :visible="true"
                :color="$parent.getThemeColor('secondary')"
                />
            </DxLabel>
        </DxSeries>
        <DxMargin :bottom="100"/>
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
  }
};
</script>
<style>
#chart {
    height: 440px;
}
</style>