<template>
    <div class="m-2">
        <h4 class="text-center">Information Products</h4>
        <DxDataGrid
            :data-source="dataStore"
            :show-borders="true"
            :allow-column-resizing="true"
            :allow-column-reordering="true"
            column-resizing-mode="widget"
            :row-alternation-enabled="true"
            :column-auto-width="true"
            :focused-row-enabled="true"
            :auto-navigate-to-focused-row="true"
            @exporting="onExporting"
        >
            <DxLoadPanel :enabled="true"/>

            <DxPaging :page-size="10"/>
            <DxPager
                :visible="true"
                :allowed-page-sizes="[10, 20, 100, 'all']"
                :show-page-size-selector="true"
                :show-info="true"
                :show-navigation-buttons="true"
            />

            <DxSearchPanel
                :visible="true"
                :width="240"
                placeholder="Search..."
            />
            <DxHeaderFilter
              :visible="true"
              :allow-search="true"
            />

            <DxColumnChooser :enabled="true"/>
            <DxColumnFixing :enabled="true"/>

            <DxGroupPanel :visible="true"/>

            <DxExport
              :enabled="true"
              :allow-export-selected-data="true"
            />

            <DxColumn
                type="buttons"
                :width="60"
            >
                <DxButton
                    hint="View/Edit"
                    icon="link"
                    :visible="true"
                    :disabled="false"
                    :onClick="viewClick"
                  />
            </DxColumn>

            <DxColumn
                data-field="id"
                data-type="number"
                :visible="false"
            />
            <DxColumn data-field="title"/>
            <DxColumn
                data-field="researchGroups"
                row-type="group"
                :cell-template="cellTemplate"
                :calculate-filter-expression="calculateFilterExpression"
            >
                <DxLookup
                    :data-source="researchGroups"
                    value-expr="id"
                    display-expr="shortName"
                />
            </DxColumn>
            <DxColumn data-field="creators"/>
            <DxColumn data-field="publisher"/>
            <DxColumn data-field="externalDoi"/>
            <DxColumn data-field="published" :width="60">
                <DxHeaderFilter :allow-search="false"/>
            </DxColumn>
            <DxColumn data-field="remoteResource" :width="60">
                <DxHeaderFilter :allow-search="false"/>
            </DxColumn>
            <DxColumn
                data-field="file.filePathName"
                caption="File Name"
            />
            <DxColumn data-field="remoteUri"/>

            <DxSorting mode="multiple"/>
        </DxDataGrid>
    </div>
</template>

<script>
import CustomStore from 'devextreme/data/custom_store';

import {
  DxDataGrid,
  DxColumn,
  DxLookup,
  DxSearchPanel,
  DxPaging,
  DxPager,
  DxButton,
  DxHeaderFilter,
  DxLoadPanel,
  DxSorting,
  DxColumnChooser,
  DxColumnFixing,
  DxGroupPanel,
  DxExport,
} from 'devextreme-vue/data-grid';
import { getApi } from '@/vue/utils/axiosService';
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import { Workbook } from 'exceljs';
import { saveAs } from 'file-saver';
import { exportDataGrid } from 'devextreme/excel_exporter';

const store = new CustomStore({
  key: 'id',
  // eslint-disable-next-line no-undef
  load: () => getApi(`${Routing.generate('pelagos_api_get_all_information_product')}`),
});

export default {
  name: 'InformationProductList',
  components: {
    DxDataGrid,
    DxColumn,
    DxLookup,
    DxSearchPanel,
    DxPaging,
    DxPager,
    DxHeaderFilter,
    DxButton,
    DxLoadPanel,
    DxSorting,
    DxColumnChooser,
    DxColumnFixing,
    DxGroupPanel,
    DxExport,
  },
  data() {
    return {
      dataStore: store,
      researchGroups: window.researchGroups,

    };
  },
  methods: {
    calculateFilterExpression(filterValue, selectedFilterOperation, target) {
      if (target === 'search' && typeof (filterValue) === 'string') {
        return [this.dataField, 'contains', filterValue];
      }
      return function filterData(data) {
        return (data.researchGroups || []).indexOf(filterValue) !== -1;
      };
    },
    cellTemplate(container, options) {
      const noBreakSpace = '\u00A0';
      const text = (options.value || []).map((element) => options.column.lookup.calculateCellValue(element)).join(', ');
      // eslint-disable-next-line no-param-reassign
      container.textContent = text || noBreakSpace;
      // eslint-disable-next-line no-param-reassign
      container.title = text;
    },
    viewClick(e) {
      const { id } = e.row.data;
      // eslint-disable-next-line no-undef
      window.open(`${Routing.generate('pelagos_app_ui_information_product')}/${id}`, '_blank');
    },
    onExporting(e) {
      const workbook = new Workbook();
      const worksheet = workbook.addWorksheet('IPs');

      exportDataGrid({
        component: e.component,
        worksheet,
        autoFilterEnabled: true,
      }).then(() => {
        workbook.xlsx.writeBuffer().then((buffer) => {
          saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'DataGrid.xlsx');
        });
      });
      e.cancel = true;
    },
  },
};
</script>

<style scoped lang="scss">

</style>
