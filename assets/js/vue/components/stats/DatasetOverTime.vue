<template>
    <DxChart
      id="dot-chart"
      :data-source="datasetUrl"
    >
        <DxLoadingIndicator
            :enabled="true"
        />
        <DxCommonSeriesSettings
            type="line"
            argument-field="date"
        >
            <DxPoint :visible="false"/>
            <DxLabel
            >
                <DxFont
                    :family="this.getThemeProperty('baseFontFamily')"
                />
            </DxLabel>
        </DxCommonSeriesSettings>
        <DxSeries
            key="registered"
            value-field="registered"
            name="Registered"
            :color="this.getThemeProperty('chart-primary')"
        />
        <DxSeries
            key="available"
            value-field="available"
            name="Available"
            :color="this.getThemeProperty('chart-secondary')"
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
                <DxFont
                    :family="this.getThemeProperty('baseFontFamily')"
                />
            </DxLabel>
        </DxArgumentAxis>
        <DxValueAxis
            value-type="numeric"
            type="continuous"
        >
            <DxTitle text="Total Datasets Over Time">
                <DxFont
                    :family="this.getThemeProperty('baseFontFamily')"
                />
            </DxTitle>
            <DxLabel>
                <DxFormat
                    type="decimal"
                />
                <DxFont
                    :family="this.getThemeProperty('baseFontFamily')"
                />
            </DxLabel>
            <DxTick
                :visible="true"
            />
        </DxValueAxis>
        <DxTitle text="Total Datasets Over Time">
            <DxFont
                :family="this.getThemeProperty('baseFontFamily')"
            />
        </DxTitle>
        <DxLegend
            position="inside"
            horizontal-alignment="left"
            vertical-alignment="top"
        >
            <DxFont
                :family="this.getThemeProperty('baseFontFamily')"
            />
        </DxLegend>
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
    DxFont,
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
        DxFont,
    },
    mixins: [themeFunctions],
    data() {
        return {
            datasetUrl: Routing.generate("pelagos_app_ui_stats_getdatasetovertime"),
        };
    },
};
</script>

<style>

</style>
