export default {
    truncate: function (text, length) {
        let regex = new RegExp('^.{' + length + '}\\S*');
        let split = text.match(regex);
        return (split ? split[0] + '...' : text);
    }
};