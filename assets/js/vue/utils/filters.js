export default {
    truncate: function (text, length) {
        let regex = new RegExp('^.{' + length + '}\\S*');
        let split = text.match(regex);
        return (split ? split[0] + '...' : text);
    },
    sort: function (valuePath, array) {
        let path = valuePath.split('.')

        const getValue = (obj, path) => {
            path.forEach(path => obj = obj[path])
            return obj;
        }
        return array.sort((a, b) => {
            var nameA = getValue(a, path).toUpperCase();
            var nameB = getValue(b, path).toUpperCase();
            if (nameA < nameB) {
                return -1;
            }
            if (nameA > nameB) {
                return 1;
            }
            return 0;
        });
    }
};
