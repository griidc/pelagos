export default {
  truncate(text, length) {
    const regex = new RegExp(`^.{${length}}\\S*`);
    const split = text.match(regex);
    return (split ? `${split[0]}...` : text);
  },
  sort(valuePath, array) {
    const path = valuePath.split('.');

    const getValue = (obj) => {
      path.forEach(() => {
        // eslint-disable-next-line no-param-reassign
        obj = obj[path];
      });
      return obj;
    };
    return array.sort((a, b) => {
      const nameA = getValue(a).toUpperCase();
      const nameB = getValue(b).toUpperCase();
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
