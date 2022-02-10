// A function to remove the '<>&' characters from a string.
function htmlEntities(str) {
    str = notNull(str);
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// This funciton prevents a null or undefined function from displaying the strings 'null' and 'undefined'.
function notNull(str) {
    if((typeof(str) === 'undefined') || (str === null)) return '';
    else return str;
}

