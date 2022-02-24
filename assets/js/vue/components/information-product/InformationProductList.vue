<template>
    <div class="m-2">
        <h4 class="text-center">Information Products</h4>
        <DxDataGrid
            :data-source="data"
            key-expr="id"
            :show-borders="true"
        >
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

            <DxColumn
                data-field="id"
                data-type="number"
            />
            <DxColumn data-field="title"/>
            <DxColumn data-field="researchGroups">
                <DxLookup
                    :data-source="researchGroups"
                    value-expr="id"
                    display-expr="shortName"
                />
            </DxColumn>
            <DxColumn data-field="creators"/>
            <DxColumn data-field="publisher"/>
            <DxColumn data-field="externalDoi"/>
            <DxColumn data-field="published"/>
            <DxColumn data-field="remoteResource"/>
            <DxColumn
                data-field="file.filePathName"
                caption="File Name"
            />
            <DxColumn data-field="remoteUri"/>
        </DxDataGrid>
    </div>
</template>

<script>
import {
  DxDataGrid,
  DxColumn,
  DxLookup,
  DxSearchPanel,
  DxPaging,
  DxPager,
} from 'devextreme-vue/data-grid';
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';

export default {
  name: 'InformationProductList',
  components: {
    DxDataGrid,
    DxColumn,
    DxLookup,
    DxSearchPanel,
    DxPaging,
    DxPager,
  },
  data() {
    return {
      // eslint-disable-next-line no-undef
      data: `${Routing.generate('pelagos_api_get_all_information_product')}`,
      researchGroups: window.researchGroups,
    };
  },
};
</script>

<style scoped lang="scss">

</style>
