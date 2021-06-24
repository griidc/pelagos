export const themeFunctions = {
  methods: {
    getThemeProperty(prop) {
      return getComputedStyle(document.body).getPropertyValue(`--${prop}`);
    },
  },
};
