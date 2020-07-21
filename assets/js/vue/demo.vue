<template>
  <div>
    <DxFileManager
      :file-system-provider="remoteProvider"
      :on-selected-file-opened="displayImagePopup"
      current-path="Widescreen"
    >
      <DxPermissions
        :create="false"
        :copy="true"
        :move="true"
        :delete="true"
        :rename="true"
        :upload="false"
        :download="true"
      />
      
      <DxItemView
      :show-parent-folder="false"
        >
          <DxDetails>
            <DxColumn data-field="thumbnail"/>
            <DxColumn data-field="name"/>
            <DxColumn
              data-field="category"
              caption="Category"
              :width="95"
            />
            <DxColumn caption="Progress" data-type="number" data-field="data-dz-uploadprogress" />
            <DxColumn data-field="dateModified"/>
            <DxColumn data-field="size" />
            
          </DxDetails>
        </DxItemView>
    </DxFileManager>

    <DxPopup
      :close-on-outside-click="true"
      :visible.sync="popupVisible"
      :title.sync="imageItemToDisplay.name"
      max-height="600"
      class="photo-popup-content"
    >
      <img
        :src="imageItemToDisplay.url"
        class="photo-popup-image"
      >
    </DxPopup>
  </div>
</template>

<script>
import { DxFileManager, DxPermissions, DxToolbar, DxContextMenu, DxItem,
  DxFileSelectionItem, DxItemView, DxDetails, DxColumn } from 'devextreme-vue/file-manager';
import { DxPopup } from 'devextreme-vue/popup';
import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import RemoteFileSystemProvider from 'devextreme/file_management/remote_provider';

const remoteProvider = new RemoteFileSystemProvider({
  endpointUrl: 'https://js.devexpress.com/Demos/Mvc/api/file-manager-file-system-images'
});

export default {
  components: {
    DxFileManager,
    DxPermissions,
    DxToolbar,
    DxContextMenu,
    DxItem,
    DxFileSelectionItem,
    DxItemView,
    DxDetails,
    DxColumn,
    DxPopup
  },

  data() {
    return {
      remoteProvider,
      popupVisible: false,
      imageItemToDisplay: {}
    };
  },

  methods: {
    displayImagePopup(e) {
      this.imageItemToDisplay = {
        name: e.fileItem.name,
        url: e.fileItem.dataItem.url
      };
      this.popupVisible = true;
    }
  }
};
</script>

<style>
.photo-popup-content {
    text-align: center;
}
.photo-popup-content .photo-popup-image {
    height: 100%;
    max-width: 100%;
}
</style>