const grp = {
    status: 'Dataset Status',
    fundingCycle: 'Grant Awards',
    researchGroup:  'Projects',
    fundingOrg: 'Funding Organizations'
};

const griidc = {
    status: 'Dataset Status',
    fundingCycle: 'Funding Cycles',
    researchGroup:  'Research Groups',
    fundingOrg: 'Funding Organizations'
}

export default {
    getLabel: (name) => {
        if (PELAGOS_TEMPLATE_PROPS === undefined || PELAGOS_TEMPLATE_PROPS.BaseTemplateName === undefined || PELAGOS_TEMPLATE_PROPS.BaseTemplateName === 'GRIIDC') {
            return griidc[name];
        } else {
            return grp[name];
        }
    },
    isGrpTemplate: () => {
        return PELAGOS_TEMPLATE_PROPS.BaseTemplateName === 'GRP';
    }
}
