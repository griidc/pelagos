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
        if (PELAGOS_TEMPLATE_PROPS.BaseTemplateName === 'GRP') {
            return grp[name];
        } else {
            return griidc[name];
        }
    },
    isGrpTemplate: () => {
        return PELAGOS_TEMPLATE_PROPS.BaseTemplateName === 'GRP';
    }
}
