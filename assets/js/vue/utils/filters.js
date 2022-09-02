export default {
  truncate(text, length) {
    const regex = new RegExp(`^.{${length}}\\S*`);
    const split = text.match(regex);
    return (split ? `${split[0]}...` : text);
  },
  sort(valuePath, array) {
    const getValue = (obj) => obj.valuePath;
    return array.sort((a, b) => {
      const nameA = getValue(a)?.toUpperCase();
      const nameB = getValue(b)?.toUpperCase();
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
