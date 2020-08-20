const grpLabels = {
    status: 'Dataset Status',
    fundingCycle: 'Grant Awards',
    researchGroup:  'Projects',
    fundingOrg: 'Funding Organizations',
};
const grpFlags = {
    showFundingCycles: false,
    showProjectDirector: true
};
const griidcLabels = {
    status: 'Dataset Status',
    fundingCycle: 'Funding Cycles',
    researchGroup:  'Research Groups',
    fundingOrg: 'Funding Organizations',
};
const griidcFlags = {
    showFundingCycles: true,
    showProjectDirector: false
};

export default {
    getLabel: (name) => {
        if (PELAGOS_TEMPLATE_PROPS === undefined) {
            return undefined;
        } else if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName === undefined) {
            return undefined;
        } else if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName === 'GRP') {
            return grpLabels[name];
        } else if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName = 'GRIIDC') {
            return griidcLabels[name];
        } else {
            return undefined;
        }
    },
    getFlag: (name) => {
        if (PELAGOS_TEMPLATE_PROPS === undefined) {
            return undefined;
        } else if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName === undefined) {
            return undefined;
        } else if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName === 'GRP') {
            return grpFlags[name];
        } else if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName = 'GRIIDC') {
            return griidcFlags[name];
        } else {
            return undefined;
        }
    },
    isBaseTemplate: (name) => {
        if (PELAGOS_TEMPLATE_PROPS === undefined) {
            return false;
        } else if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName === undefined) {
            return false;
        } else if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName === name) {
            return true
        } else {
            return false;
        }
    }
}
