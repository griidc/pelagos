<template>
    <DxChart
      id="dot-chart"
      data-source="/stats/data/total-records-over-time"
    >
        <DxCommonSeriesSettings
            type="line"
            argument-field="date"
        >
            <DxPoint :visible="false"/>
        </DxCommonSeriesSettings>
        <DxSeries
            key="registered"
            value-field="registered"
            name="Registered"
            :color="this.getThemeColor('chart-primary')"
        />
        <DxSeries
            key="available"
            value-field="available"
            name="Available"
            :color="this.getThemeColor('chart-secondary')"
        />
        <DxMargin :bottom="20"/>
        <DxArgumentAxis
            argument-type="datetime"
        >
            <DxGrid :visible="true"/>
            <DxTick :visible="true"/>
            <DxMinorTick :visible="true"/>
            <DxTickInterval :months="6"/>
            <DxMinorTickInterval :months="1"/>
            <DxLabel>
                <DxFormat
                    type="monthAndYear"
                />
            </DxLabel>
        </DxArgumentAxis>
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
        <DxTitle text="Total Datasets Over Time">
        </DxTitle>
        <DxLegend
            position="inside"
            horizontal-alignment="left"
            vertical-alignment="top"
        />
        <DxTooltip :enabled="true"/>
    </DxChart>
</template>

<script>
import {themeFunctions} from '@/vue/mixins/themeFunctions';

import {
    DxChart,
    DxSeries,
    DxArgumentAxis,
    DxCommonSeriesSettings,
    DxGrid,
    DxMargin,
    DxLegend,
    DxTitle,
    DxTooltip,
    DxPoint,
    DxTick,
    DxTickInterval,
    DxMinorTickInterval,
    DxMinorTick,
    DxLabel,
    DxLoadingIndicator,
    DxFormat,
    DxValueAxis,
} from 'devextreme-vue/chart';

export default {
    name: "DatasetOverTime",
    components: {
        DxChart,
        DxSeries,
        DxArgumentAxis,
        DxCommonSeriesSettings,
        DxGrid,
        DxMargin,
        DxLegend,
        DxTitle,
        DxTooltip,
        DxPoint,
        DxTick,
        DxTickInterval,
        DxMinorTickInterval,
        DxMinorTick,
        DxLabel,
        DxLoadingIndicator,
        DxFormat,
        DxValueAxis,
    },
    mixins: [themeFunctions]
};
</script>

<style>

</style>
