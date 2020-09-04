export const themeFunctions = {
  methods: {
    getThemeProperty: function (prop) {
      return getComputedStyle(document.body).getPropertyValue("--" + prop);
    }
  }
}
