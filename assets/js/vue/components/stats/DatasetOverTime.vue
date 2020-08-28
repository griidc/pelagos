<template>
    <DxChart
      id="chart"
      data-source="/stats/data/total-records-over-time"
    >
        <DxLoadingIndicator
            :enabled="true"
        />
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
            :color="$parent.getThemeColor('primary')"
        />
        <DxSeries
            key="available"
            value-field="available"
            name="Available"
            :color="$parent.getThemeColor('secondary')"
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
            precision="1"
            format="decimal"
        >
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
import {
  DxChart,
  DxSeries,
  DxArgumentAxis,
  DxCommonSeriesSettings,
  DxExport,
  DxGrid,
  DxMargin,
  DxLegend,
  DxTitle,
  DxSubtitle,
  DxTooltip,
  DxPoint,
  DxTick,
  DxTickInterval,
  DxMinorTickInterval,
  DxMinorTick,
  DxLabel,
  DxLoadingIndicator,
  DxZoomAndPan,
  DxAggregationInterval,
  DxFormat,
  DxValueAxis,
} from 'devextreme-vue/chart';

export default {
  components: {
    DxChart,
    DxSeries,
    DxArgumentAxis,
    DxCommonSeriesSettings,
    DxExport,
    DxGrid,
    DxMargin,
    DxLegend,
    DxTitle,
    DxSubtitle,
    DxTooltip,
    DxPoint,
    DxTick,
    DxTickInterval,
    DxMinorTickInterval,
    DxMinorTick,
    DxLabel,
    DxLoadingIndicator,
    DxZoomAndPan,
    DxAggregationInterval,
    DxFormat,
    DxValueAxis,
  }
};
</script>

<style>

</style>
