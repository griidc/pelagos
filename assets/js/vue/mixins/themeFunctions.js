const themeFunctions = {
  methods: {
    getThemeProperty(prop) {
      return getComputedStyle(document.body).getPropertyValue(`--${prop}`);
    },
  },
};

export default themeFunctions;
