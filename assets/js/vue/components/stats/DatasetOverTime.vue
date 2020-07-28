<template>
  <div id="chart-demo">
    <DxChart
      id="chart"
      :data-source="dataSource"
      palette=myPalette
    >
      <DxCommonSeriesSettings
        :type="type"
        argument-field="country"
      />
      <DxSeries
        v-for="energy in energySources"
        :key="energy.value"
        :value-field="energy.value"
        :name="energy.name"
      />
      <DxMargin :bottom="20"/>
      <DxArgumentAxis
        :value-margins-enabled="false"
        discrete-axis-division-mode="crossLabels"
      >
        <DxGrid :visible="true"/>
      </DxArgumentAxis>
      <DxLegend
        vertical-alignment="bottom"
        horizontal-alignment="center"
        item-text-position="bottom"
      />
      <DxExport :enabled="true"/>
      <DxTitle text="Energy Consumption in 2004">
        <DxSubtitle text="(Millions of Tons, Oil Equivalent)"/>
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
  DxTooltip
} from 'devextreme-vue/chart';
import DxSelectBox from 'devextreme-vue/select-box';

import service from './data.js';

var myPalette = {
    // Applies in the BarGauge, Chart, Funnel, PieChart, PolarChart, Sankey, and TreeMap with a discrete colorizer
    simpleSet: ['#60a69f', '#78b6d9', '#6682bb', '#a37182', '#eeba69'], 
    // Applies in the CircularGauge and LinearGauge
    indicatingSet: ['#90ba58', '#eeba69', '#a37182'], 
    // Applies in the VectorMap and TreeMap with a gradient or range colorizer 
    gradientSet: ['#78b6d9', '#eeba69'] 
};

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
    DxTooltip
  },

  data() {
    return {
      dataSource: service.getCountriesInfo(),
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
