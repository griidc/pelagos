const truncate = (text, length) => {
  if (text) {
    const regex = new RegExp(`^.{${length}}\\S*`);
    const split = text.match(regex);
    return (split ? `${split[0]}...` : text);
  }
  return text;
};

const sort = (valuePath, array) => {
  const getValue = (obj) => {
    if (!obj || !valuePath) return undefined;
    if (valuePath in obj) return obj[valuePath];
    return valuePath.split('.').reduce((acc, part) => (acc ? acc[part] : undefined), obj);
  };
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
};

export { truncate, sort };
export default {
  truncate,
  sort,
};
