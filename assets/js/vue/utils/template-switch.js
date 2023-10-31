global.baseTemplateName = 'default';

const templateVariables = {
  default: {
    status: 'Dataset Status',
    fundingCycle: 'Funding Cycles',
    researchGroup: 'Research Groups',
    funder: 'Funders',
    showFundingCycles: false,
    showProjectDirector: false,
    showFunderFacet: true,
    researchAwardOption: 'Research Award',
    digitalTypeDesc: 'Resource Type',
    productTypeDesc: 'Product Type',
    tags: 'Tags',
    researchGroupExpanded: true,
    fundingOrgExpanded: true,
    typeExpanded: true,
    statusExpanded: true,
    tagsExpanded: true,
  },
  GRP: {
    fundingCycle: 'Grant Awards',
    researchGroup: 'Projects',
    showFundingCycles: true,
    showProjectDirector: true,
    showFunderFacet: false,
    researchAwardOption: 'Grant Award',
    cardHeadingText: 'View Project Overview By',
  },
  HRI: {
    showFundingCycles: false,
    showProjectDirector: false,
    fundingOrgExpanded: false,
    showFunderFacet: false,
    researchAwardOption: 'Research Award',
    digitalTypeDesc: 'Resource Type',
    productTypeDesc: 'Product Type',
  },
};

export default {
  getProperty: (name) => {
    if (
      global.baseTemplateName !== undefined
            && templateVariables[global.baseTemplateName] !== undefined
            && name in templateVariables[global.baseTemplateName]
    ) {
      return templateVariables[global.baseTemplateName][name];
    }
    return templateVariables.default[name];
  },
  setTemplate: (template) => {
    global.baseTemplateName = template;
  },
};
