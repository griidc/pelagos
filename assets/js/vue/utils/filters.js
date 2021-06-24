export default {
  truncate(text, length) {
    const regex = new RegExp(`^.{${length}}\\S*`);
    const split = text.match(regex);
    return (split ? `${split[0]}...` : text);
  },
  sort(valuePath, array) {
    const path = valuePath.split('.');

    const getValue = (obj, path) => {
      path.forEach((path) => obj = obj[path]);
      return obj;
    };
    return array.sort((a, b) => {
      const nameA = getValue(a, path).toUpperCase();
      const nameB = getValue(b, path).toUpperCase();
      if (nameA < nameB) {
        return -1;
      }
      if (nameA > nameB) {
        return 1;
      }
      return 0;
    });
  },
};
