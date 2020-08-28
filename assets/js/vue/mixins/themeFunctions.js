export const themeFunctions = {
  methods: {
    getThemeColor: function (color) {
      return getComputedStyle(document.body).getPropertyValue("--" + color);
    }
  }
}
