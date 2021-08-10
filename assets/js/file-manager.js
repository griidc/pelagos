import Vue from 'vue';
import FileManager from './vue/FileManager.vue';
import '../css/file-manager.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

const fileManagerElement = document.getElementById('file-manager-app');

if (fileManagerElement) {
  if (fileManagerElement.dataset) {
    const datasetSubmissionId = Number(fileManagerElement.dataset.id);
    const fileManagerMode = Boolean(fileManagerElement.dataset.writeMode);
    // eslint-disable-next-line no-new
    new Vue({
      el: '#file-manager-app',
      data() {
        return {
          datasetSubmissionId,
          fileManagerMode,
        };
      },
      components: { FileManager },
      template: `
              <FileManager :datasetSubId="datasetSubmissionId" :writeMode="fileManagerMode"/>`,
    });
  }
}
