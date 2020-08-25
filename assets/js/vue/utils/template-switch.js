const templateVariables = {
    default: "GRIIDC",
    "GRIIDC" : {
        status: "Dataset Status",
        fundingCycle: "Funding Cycles",
        researchGroup: "Research Groups",
        fundingOrg: "Funding Organizations",
        showFundingCycles: false,
        showProjectDirector: false,
        displayTextBlock: false,
    },
    "GRP" : {
        status: "Dataset Status",
        fundingCycle: "Grant Awards",
        researchGroup: "Projects",
        fundingOrg: "Funding Organizations",
        showFundingCycles: true,
        showProjectDirector: true,
        displayTextBlock: true,
    }
};

export default {
    getProperty: (name) => {
        if (
            PELAGOS_TEMPLATE_PROPS !== undefined &&
            PELAGOS_TEMPLATE_PROPS.BaseTemplateName !== undefined &&
            templateVariables[PELAGOS_TEMPLATE_PROPS.BaseTemplateName] !== undefined
        ) {
            return templateVariables[PELAGOS_TEMPLATE_PROPS.BaseTemplateName][name];
        } else {
            return templateVariables[templateVariables.default][name];
        }
    }
}
