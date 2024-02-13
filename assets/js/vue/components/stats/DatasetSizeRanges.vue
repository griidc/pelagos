<template>
    <DxChart
        id="dsr-chart"
        :data-source="datasetUrl"
    >
        <DxLoadingIndicator
            :enabled="true"
        />
        <DxSeries
            argument-field="label"
            value-field="count"
            name="Data Sizes"
            type="bar"
            :color="this.getThemeProperty('chart-secondary')"
        >
            <DxLabel
                :visible="true"
                :backgroundColor="this.getThemeProperty('chart-alternate')"
            >
                <DxConnector
                :visible="true"
                :color="this.getThemeProperty('chart-alternate')"
                />
                <DxFont
                    :family="this.getThemeProperty('main-fonts')"
                />
            </DxLabel>
        </DxSeries>
        <DxMargin :bottom="20"/>
        <DxArgumentAxis>
            <DxLabel>
                <DxFont
                    :family="this.getThemeProperty('main-fonts')"
                />
            </DxLabel>
        </DxArgumentAxis>
        <DxValueAxis
            value-type="numeric"
            type="continuous"
        >
            <DxTitle text="Number of Datasets">
                <DxFont
                    :family="this.getThemeProperty('main-fonts')"
                />
            </DxTitle>
            <DxLabel>
                <DxFormat
                    type="decimal"
                />
                <DxFont
                    :family="this.getThemeProperty('main-fonts')"
                />
            </DxLabel>
            <DxTick
                :visible="true"
            />
        </DxValueAxis>
        <DxTitle text="Dataset Size Ranges">
            <DxFont
                :family="this.getThemeProperty('main-fonts')"
                :weight="this.getThemeProperty('weightRegular')"
            />
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
  DxTick,
  DxLegend,
  DxMargin,
  DxLoadingIndicator,
  DxFormat,
  DxFont,
  DxArgumentAxis,
} from 'devextreme-vue/chart';
import themeFunctions from '../../mixins/themeFunctions';

export default {
  name: 'DatasetSizeRanges',
  components: {
    DxChart,
    DxSeries,
    DxLabel,
    DxConnector,
    DxValueAxis,
    DxTitle,
    DxTick,
    DxLegend,
    DxMargin,
    DxLoadingIndicator,
    DxFormat,
    DxFont,
    DxArgumentAxis,
  },
  mixins: [themeFunctions],
  data() {
    return {
      // eslint-disable-next-line no-undef
      datasetUrl: Routing.generate('pelagos_app_ui_stats_getdatasetsizeranges'),
    };
  },
};
</script>

<style>

</style>
