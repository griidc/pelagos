<template>
    <div id="chart-demo">
        <DxChart
          id="chart"
          data-source="/stats/data/total-records-over-time"
            palette="Ocean"
        >
            <DxLoadingIndicator
                :enabled="true"
            />
            <DxCommonSeriesSettings
                type="line"
                argument-field="date"
            >
                <DxPoint :visible="true"/>
                <DxLabel
                    :visible="true"
                    :customize-text="Number"
                />
            </DxCommonSeriesSettings>
            <DxSeries
                key="registered"
                value-field="registered"
                name="Registered"
            />
            <DxSeries
                key="available"
                value-field="available"
                name="Available"
            />

            <DxMargin :bottom="20"/>
            <DxArgumentAxis
            >
                <DxTickInterval :years="1"/>
                <DxMinorTickInterval :months="6"/>
                <DxTick :visible="false"/>
                <DxMinorTick :visible="false"/>
                <DxGrid :visible="false"/>
                <DxLabel
                    :staggering-spacing="10"
                    display-mode="stagger"
                    format="monthAndDay"
                />
            </DxArgumentAxis>
            <DxLegend
                vertical-alignment="bottom"
                horizontal-alignment="center"
                item-text-position="bottom"
            />
            <DxExport :enabled="true"/>
            <DxTitle text="Total Datasets Over Time">
                <DxSubtitle text="(Test)"/>
            </DxTitle>
            <DxTooltip :enabled="true"/>
        </DxChart>
    </div>
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
  DxAggregationInterval
} from 'devextreme-vue/chart';
import DxSelectBox from 'devextreme-vue/select-box';

export default {

  components: {
    DxSelectBox,
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
    DxAggregationInterval
  },

  data() {
    return {
      dataSource: service.getData(),
      sources: service.getDataType(),
      energySources: service.getEnergySources(),
      types: ['line', 'stackedline', 'fullstackedline'],
      type: 'line'
    };
  }
};
</script>

<style>
.options {
    padding: 20px;
    background-color: rgba(191, 191, 191, 0.15);
    margin-top: 20px;
}

.option {
    margin-top: 10px;
}

.caption {
    font-size: 18px;
    font-weight: 500;
}

.option > span {
    margin-right: 10px;
}

.option > .dx-widget {
    display: inline-block;
    vertical-align: middle;
}
</style>
