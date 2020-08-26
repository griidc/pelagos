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
            :color="$parent.getPrimaryColor()"
        />
        <DxSeries
            key="available"
            value-field="available"
            name="Available"
            :color="$parent.getSecondaryColor()"
        />
        <DxMargin :bottom="20"/>
        <DxArgumentAxis
            format="date"
        >
            <DxTick :visible="false"/>
            <DxMinorTick :visible="false"/>
            <DxGrid :visible="false"/>
            <DxLabel
                :staggering-spacing="10"
                display-mode="stagger"
            >
                <DxFormat
                    type="monthAndYear"
                />
            </DxLabel>
        </DxArgumentAxis>
        <DxTitle text="Total Datasets Over Time">
        </DxTitle>
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
  DxFormat
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
    DxFormat
  }
};
</script>

<style>

</style>
