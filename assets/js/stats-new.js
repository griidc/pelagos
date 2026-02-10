import 'bootstrap';
import '../css/stats.css';
import axios from 'axios';
import { Chart, registerables } from 'chart.js';
import 'chartjs-adapter-date-fns';

// Register all Chart.js components
Chart.register(...registerables);

// Template switch utility
const templateVariables = {
  default: {
    researchGroup: 'Research Groups',
  },
  GRP: {
    researchGroup: 'Projects',
  },
  HRI: {
    researchGroup: 'Research Groups',
  },
  GOMRI: {
    researchGroup: 'Research Groups',
  },
};

function getTemplateProperty(name) {
  const baseTemplateName = global.baseTemplateName || 'default';
  if (
    baseTemplateName !== undefined
    && templateVariables[baseTemplateName] !== undefined
    && name in templateVariables[baseTemplateName]
  ) {
    return templateVariables[baseTemplateName][name];
  }
  return templateVariables.default[name];
}

// Get CSS variable value
function getThemeProperty(prop) {
  return getComputedStyle(document.body).getPropertyValue(`--${prop}`);
}

// Initialize the stats page
document.addEventListener('DOMContentLoaded', () => {
  const statsContainer = document.getElementById('stats');
  if (!statsContainer) return;

  // Create the layout
  statsContainer.innerHTML = `
    <div class="row w-75 mx-auto">
      <div class="col-xl-12 pb-5 pt-3">
        <div id="repository-summary" class="text-center">
          <div class="summaryLabel text-center">Repository Summary</div>
          <div class="row" id="summary-stats">
            <div class="col-sm stats-icons">
              <img src="${require('../images/icon-datasets.png')}" class="inline-block">
              <div class="count" id="datasets-count">-</div>
              <div class="label">Datasets</div>
            </div>
            <div class="col-sm stats-icons">
              <img src="${require('../images/icon-filesize.png')}" class="inline-block">
              <div class="count" id="totalsize-count">-</div>
              <div class="label"><span id="size-unit">TB</span> Of Data</div>
            </div>
            <div class="col-sm stats-icons">
              <img src="${require('../images/icon-downloads.png')}" class="inline-block">
              <div class="count" id="downloads-count">-</div>
              <div class="label">Total Downloads</div>
            </div>
            <div class="col-sm stats-icons">
              <img src="${require('../images/icon-projects.png')}" class="inline-block">
              <div class="count" id="researchgroups-count">-</div>
              <div class="label" id="researchgroups-label">${getTemplateProperty('researchGroup')}</div>
            </div>
            <div class="col-sm stats-icons">
              <img src="${require('../images/icon-researchers.png')}" class="inline-block">
              <div class="count" id="people-count">-</div>
              <div class="label">People</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-12 pb-5">
        <div id="dataset-over-time-container">
          <canvas id="dataset-over-time-chart"></canvas>
        </div>
      </div>
      <div class="col-xl-12 pb-5">
        <div id="dataset-size-ranges-container">
          <canvas id="dataset-size-ranges-chart"></canvas>
        </div>
      </div>
    </div>
  `;

  // Load repository summary data
  loadRepositorySummary();

  // Load dataset over time chart
  loadDatasetOverTimeChart();

  // Load dataset size ranges chart
  loadDatasetSizeRangesChart();
});

// Load repository summary statistics
function loadRepositorySummary() {
  // eslint-disable-next-line no-undef
  const url = Routing.generate('pelagos_app_ui_stats_getstatisticsjson');

  axios.get(url)
    .then((response) => {
      const data = response.data;

      // Update datasets count
      document.getElementById('datasets-count').textContent = data.totalDatasets || '-';

      // Update total size
      const datasetSize = data.totalSize?.split(' ');
      if (datasetSize && datasetSize.length > 0) {
        document.getElementById('totalsize-count').textContent = datasetSize[0];
        document.getElementById('size-unit').textContent = datasetSize[1];
      }

      // Update people count
      document.getElementById('people-count').textContent = data.peopleCount || '-';

      // Update research groups count
      document.getElementById('researchgroups-count').textContent = data.researchGroupCount || '-';

      // Update downloads count
      document.getElementById('downloads-count').textContent = data.totalDownloadCount || '-';
    })
    .catch((error) => {
      console.error('Error loading repository summary:', error);
    });
}

// Load dataset over time chart
function loadDatasetOverTimeChart() {
  // eslint-disable-next-line no-undef
  const url = Routing.generate('pelagos_app_ui_stats_getdatasetovertime');

  axios.get(url)
    .then((response) => {
      const data = response.data;

      // Process data for Chart.js
      const dates = [];
      const registeredData = [];
      const availableData = [];

      // Separate and sort data
      data.forEach((item) => {
        if (item.registered !== undefined) {
          dates.push(item.date);
          registeredData.push({ x: item.date, y: item.registered });
        }
        if (item.available !== undefined) {
          availableData.push({ x: item.date, y: item.available });
        }
      });

      // Get chart colors from CSS variables
      const primaryColor = getThemeProperty('chart-primary').trim() || '#0066cc';
      const secondaryColor = getThemeProperty('chart-secondary').trim() || '#003366';

      // Create chart
      const ctx = document.getElementById('dataset-over-time-chart');
      // eslint-disable-next-line no-new
      new Chart(ctx, {
        type: 'line',
        data: {
          datasets: [
            {
              label: 'Registered',
              data: registeredData,
              borderColor: primaryColor,
              backgroundColor: primaryColor,
              fill: false,
              tension: 0,
              pointRadius: 0,
            },
            {
              label: 'Available',
              data: availableData,
              borderColor: secondaryColor,
              backgroundColor: secondaryColor,
              fill: false,
              tension: 0,
              pointRadius: 0,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            title: {
              display: true,
              text: 'Total Datasets Over Time',
              font: {
                family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
                size: 16,
              },
            },
            legend: {
              display: true,
              position: 'top',
              align: 'start',
              font: {
                family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
              },
            },
            tooltip: {
              enabled: true,
            },
          },
          scales: {
            x: {
              type: 'time',
              time: {
                unit: 'month',
                displayFormats: {
                  month: 'MMM yyyy',
                },
              },
              title: {
                display: false,
              },
              ticks: {
                font: {
                  family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
                },
              },
              grid: {
                display: true,
              },
            },
            y: {
              type: 'linear',
              title: {
                display: true,
                text: 'Total Datasets Over Time',
                font: {
                  family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
                },
              },
              ticks: {
                font: {
                  family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
                },
              },
            },
          },
        },
      });
    })
    .catch((error) => {
      console.error('Error loading dataset over time chart:', error);
    });
}

// Load dataset size ranges chart
function loadDatasetSizeRangesChart() {
  // eslint-disable-next-line no-undef
  const url = Routing.generate('pelagos_app_ui_stats_getdatasetsizeranges');

  axios.get(url)
    .then((response) => {
      const data = response.data;

      // Extract labels and counts
      const labels = data.map((item) => item.label);
      const counts = data.map((item) => item.count);

      // Get chart colors from CSS variables
      const secondaryColor = getThemeProperty('chart-secondary').trim() || '#003366';
      const alternateColor = getThemeProperty('chart-alternate').trim() || '#6699cc';

      // Create chart
      const ctx = document.getElementById('dataset-size-ranges-chart');
      // eslint-disable-next-line no-new
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Data Sizes',
              data: counts,
              backgroundColor: secondaryColor,
              borderColor: secondaryColor,
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            title: {
              display: true,
              text: 'Dataset Size Ranges',
              font: {
                family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
                size: 16,
              },
            },
            legend: {
              display: false,
            },
            tooltip: {
              enabled: true,
            },
            datalabels: {
              display: false,
            },
          },
          scales: {
            x: {
              ticks: {
                font: {
                  family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
                },
              },
            },
            y: {
              title: {
                display: true,
                text: 'Number of Datasets',
                font: {
                  family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
                },
              },
              ticks: {
                font: {
                  family: getComputedStyle(document.body).getPropertyValue('--main-fonts') || 'Arial',
                },
                precision: 0,
              },
            },
          },
        },
      });
    })
    .catch((error) => {
      console.error('Error loading dataset size ranges chart:', error);
    });
}
