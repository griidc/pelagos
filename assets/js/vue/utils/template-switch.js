const templateVariables = {
  default: 'GRIIDC',
  GRIIDC: {
    name: 'GRIIDC',
    status: 'Dataset Status',
    fundingCycle: 'Funding Cycles',
    researchGroup: 'Research Groups',
    fundingOrg: 'Funding Organizations',
    showFundingCycles: false,
    showProjectDirector: false,
    showFundingOrgFacet: true,
    researchAwardOption: 'Research Award',
    digitalTypeDesc: 'Resource Type',
    productTypeDesc: 'Product Type',
  },
  GRP: {
    name: 'GRP',
    status: 'Dataset Status',
    fundingCycle: 'Grant Awards',
    researchGroup: 'Projects',
    fundingOrg: 'Funding Organizations',
    showFundingCycles: true,
    showProjectDirector: true,
    showFundingOrgFacet: false,
    researchAwardOption: 'Grant Award',
    cardHeadingText: 'View Project Overview By',
    digitalTypeDesc: 'Resource Type',
    productTypeDesc: 'Product Type',
  },
  HRI: {
    name: 'HRI',
    status: 'Dataset Status',
    fundingCycle: 'Funding Cycles',
    researchGroup: 'Research Groups',
    fundingOrg: 'Funding Organizations',
    showFundingCycles: false,
    showProjectDirector: false,
    showFundingOrgFacet: false,
    researchAwardOption: 'Research Award',
    digitalTypeDesc: 'Resource Type',
    productTypeDesc: 'Product Type',
  },
};

export default {
  getProperty: (name) => {
    if (
    // eslint-disable-next-line no-undef
      PELAGOS_TEMPLATE_PROPS !== undefined
      // eslint-disable-next-line no-undef
            && PELAGOS_TEMPLATE_PROPS.BaseTemplateName !== undefined
      // eslint-disable-next-line no-undef
            && templateVariables[PELAGOS_TEMPLATE_PROPS.BaseTemplateName] !== undefined
    ) {
      // eslint-disable-next-line no-undef
      return templateVariables[PELAGOS_TEMPLATE_PROPS.BaseTemplateName][name];
    }
    return templateVariables[templateVariables.default][name];
  },
};
